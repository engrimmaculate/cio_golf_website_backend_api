<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\Sponsor;
use App\Models\Registration;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_players' => User::where('role', 'player')->count(),
            'total_tournaments' => Tournament::count(),
            'total_sponsors' => Sponsor::count(),
            'upcoming_fixtures' => DB::table('fixtures')->where('date', '>=', now())->count(),
            'registration_count' => Registration::where('status', 'pending')->count(),
            'revenue' => Registration::where('status', 'approved')->count() * 5000,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $users = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($users);
    }

    public function updateRole(Request $request, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|in:player,committee,admin',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['role' => $validated['role']]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_user_role',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => ['role' => $validated['role']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'User role updated', 'user' => $user]);
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', '30');

        $registrations = Registration::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();

        return response()->json([
            'registrations' => $registrations,
            'total_revenue' => Registration::where('status', 'approved')->count() * 5000,
            'player_growth' => User::where('role', 'player')
                ->where('created_at', '>=', now()->subDays($period))
                ->count(),
        ]);
    }

    public function auditLogs(Request $request)
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate($request->get('per_page', 50));

        return response()->json($logs);
    }

    public function listPlayers(Request $request)
    {
        $query = Player::with(['club', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        $players = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($players);
    }

    public function approvePlayer(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        if ($player->status !== 'completed') {
            return response()->json(['message' => 'Player must complete registration first.'], 400);
        }

        $paymentLink = $request->input('payment_link', url('/payment/' . $player->id));

        $player->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($player->user) {
            try {
                Mail::send('emails.player-approved', [
                    'name' => $player->full_name,
                    'paymentLink' => $paymentLink,
                ], function ($message) use ($player) {
                    $message->to($player->email, $player->full_name)
                        ->subject('Registration Approved - CIO International Golf Championship');
                });
            } catch (\Exception $e) {
                // Log error but don't fail
            }
        }

        return response()->json([
            'message' => 'Player approved. Notification sent.',
            'player' => $player->fresh(),
        ]);
    }

    public function rejectPlayer(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $request->validate(['reason' => 'nullable|string']);

        $player->update(['status' => 'rejected']);

        if ($player->user) {
            try {
                Mail::send('emails.player-rejected', [
                    'name' => $player->full_name,
                    'reason' => $request->reason ?? 'Your registration did not meet the required criteria.',
                ], function ($message) use ($player) {
                    $message->to($player->email, $player->full_name)
                        ->subject('Registration Update - CIO International Golf Championship');
                });
            } catch (\Exception $e) {
                // Log error
            }
        }

        return response()->json(['message' => 'Player rejected.', 'player' => $player->fresh()]);
    }

    public function createCommittee(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'committee',
            'user_type' => 'committee',
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'Committee account created successfully.',
            'user' => $user,
        ], 201);
    }
}
