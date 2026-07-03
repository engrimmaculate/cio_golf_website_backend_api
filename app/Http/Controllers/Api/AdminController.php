<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Sponsor;
use App\Models\Registration;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
