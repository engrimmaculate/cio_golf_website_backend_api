<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\Sponsor;
use App\Models\Registration;
use App\Models\AuditLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function stats()
    {
        $totalUsers = User::count();
        $totalPlayers = Player::count();
        $totalTournaments = Tournament::count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        return response()->json([
            'data' => [
                'total_users' => $totalUsers,
                'total_players' => $totalPlayers,
                'total_tournaments' => $totalTournaments,
                'total_sponsors' => Sponsor::count(),
                'upcoming_fixtures' => DB::table('fixtures')->where('date', '>=', now())->count(),
                'registration_count' => Registration::count(),
                'revenue' => $totalRevenue,
                'revenue_growth' => 0,
                'users_growth' => 0,
                'tournaments_growth' => 0,
            ],
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
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        return response()->json([
            'data' => [
                'registrations' => Registration::where('created_at', '>=', now()->subDays($period))
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->get(),
                'total_revenue' => $totalRevenue,
                'player_growth' => User::where('role', 'player')
                    ->where('created_at', '>=', now()->subDays($period))
                    ->count(),
                'api_response_time' => '45ms',
                'api_health' => 'healthy',
                'db_load' => '12%',
                'db_health' => 'healthy',
                'storage_usage' => '2.4 GB',
                'storage_health' => 'healthy',
                'active_sessions' => DB::table('sessions')->count(),
                'growth_rate' => User::where('created_at', '>=', now()->subDays($period))->count() > 0
                    ? round((User::where('created_at', '>=', now()->subDays($period))->count() / max(User::count(), 1)) * 100, 1)
                    : 0,
                'growth_change' => 0,
            ],
        ]);
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->latest()->paginate($request->get('per_page', 20));

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

        $player->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($player->user) {
            try {
                Mail::send('emails.player-approved', [
                    'name' => $player->full_name,
                ], function ($message) use ($player) {
                    $message->to($player->email, $player->full_name)
                        ->subject('Registration Approved - CIO International Golf Championship');
                });
            } catch (\Exception $e) {
                Log::error('Approval email failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Player approved. Notification sent.',
            'player' => $player->fresh(),
        ]);
    }

    public function sendPaymentLink(Request $request, $id)
{
    $player = Player::with(['tournament', 'user'])->findOrFail($id);

    // Verify the user's email
    if ($player->user && !$player->user->email_verified_at) {
        $player->user->update(['email_verified_at' => now()]);
    }

    // Approve the player
    $player->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    // Calculate registration fee + dynamic support charges
    $tournament = $player->tournament;
    $registrationFee = $tournament->registration_fee ?? config('services.paystack.registration_fee', 50000);
    $supportCharges = $tournament->support_charges ?? 500;
    $amount = $registrationFee + $supportCharges;

    // Find or create payment record
    $payment = Payment::where('player_id', $player->id)
        ->where('status', 'pending')
        ->where('token_expires_at', '>', now())
        ->first();

    if ($payment) {
        // Update existing pending payment with fresh reference & updated total amount
        $payment->update([
            'amount' => $amount,
            'reference' => Payment::generateReference(),
        ]);
    } else {
        $payment = Payment::create([
            'player_id' => $player->id,
            'user_id' => $player->user_id,
            'reference' => Payment::generateReference(),
            'amount' => $amount,
            'currency' => 'NGN',
            'status' => 'pending',
            'token' => Payment::generateToken(),
            'token_expires_at' => now()->addDays(7),
            'description' => 'CIO International Golf Classic - Registration Fee',
        ]);
    }

    // Send payment link email
    $frontendUrl = rtrim(config('app.frontend_url', 'https://ciogolfclassic.com'), '/');
    $paymentUrl = $frontendUrl . '/payment/public/' . $payment->token;

    try {
        Mail::send('emails.player-payment-link', [
            'name' => $player->full_name,
            'paymentUrl' => $paymentUrl,
            'amount' => $amount,
            'expiresAt' => $payment->token_expires_at->format('F j, Y'),
        ], function ($message) use ($player) {
            $message->to($player->email, $player->full_name)
                ->subject('Complete Your Payment — CIO International Golf Classic');
        });
    } catch (\Exception $e) {
        Log::error('Payment link email failed: ' . $e->getMessage());
    }

    AuditLog::create([
        'user_id' => $request->user()->id,
        'action' => 'send_payment_link',
        'model_type' => Player::class,
        'model_id' => $player->id,
        'new_values' => ['status' => 'approved', 'email_verified_at' => now()->toDateTimeString()],
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return response()->json([
        'message' => 'Player approved and payment link sent.',
        'player' => $player->fresh(),
        'payment' => $payment,
    ]);
}

    public function regeneratePaymentLink(Request $request, $id)
    {
        $player = Player::with(['tournament', 'user'])->findOrFail($id);
    
        if (!$player->user) {
            return response()->json(['message' => 'Player has no associated user account.'], 400);
        }
    
        // Calculate registration fee + dynamic support charges
        $tournament = $player->tournament;
        $registrationFee = $tournament->registration_fee ?? config('services.paystack.registration_fee', 50000);
        $supportCharges = $tournament->support_charges ?? 500;
        $amount = $registrationFee + $supportCharges;
    
        // Look for an existing pending payment record
        $payment = Payment::where('player_id', $player->id)
            ->where('status', 'pending')
            ->first();
    
        if ($payment) {
            // Update existing pending record with fresh reference, token, and extended expiration
            $payment->update([
                'reference' => Payment::generateReference(),
                'amount' => $amount,
                'token' => Payment::generateToken(),
                'token_expires_at' => now()->addDays(7),
                'description' => 'CIO International Golf Classic - Registration Fee (Regenerated)',
            ]);
        } else {
            // Create new record only if no pending payment exists
            $payment = Payment::create([
                'player_id' => $player->id,
                'user_id' => $player->user_id,
                'reference' => Payment::generateReference(),
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'pending',
                'token' => Payment::generateToken(),
                'token_expires_at' => now()->addDays(7),
                'description' => 'CIO International Golf Classic - Registration Fee (Regenerated)',
            ]);
        }
    
        // Send payment link email
        $frontendUrl = rtrim(config('app.frontend_url', 'https://ciogolfclassic.com'), '/');
        $paymentUrl = $frontendUrl . '/payment/public/' . $payment->token;
    
        try {
            Mail::send('emails.player-payment-link', [
                'name' => $player->full_name,
                'paymentUrl' => $paymentUrl,
                'amount' => $amount,
                'expiresAt' => $payment->token_expires_at->format('F j, Y'),
            ], function ($message) use ($player) {
                $message->to($player->email, $player->full_name)
                    ->subject('New Payment Link — CIO International Golf Classic');
            });
        } catch (\Exception $e) {
            Log::error('Regenerate payment link email failed: ' . $e->getMessage());
        }
    
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'regenerate_payment_link',
            'model_type' => Player::class,
            'model_id' => $player->id,
            'new_values' => ['payment_id' => $payment->id, 'reference' => $payment->reference],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    
        return response()->json([
            'message' => 'Payment link updated and email sent.',
            'payment' => $payment,
            'payment_url' => $paymentUrl,
        ]);
    }

    public function playerPaymentLinks($id)
    {
        $player = Player::findOrFail($id);

        $payments = Payment::where('player_id', $player->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $frontendUrl = rtrim(env('FRONTEND_URL', 'https://ciogolfclassic.com'), '/');
        $payments->each(function ($payment) use ($frontendUrl) {
            $payment->payment_url = $payment->token
                ? $frontendUrl . '/payment/public/' . $payment->token
                : null;
        });

        return response()->json(['data' => $payments]);
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

    public function verifyUserEmail($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['email_verified_at' => now()]);

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'verify_user_email',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => ['email_verified_at' => now()->toDateTimeString()],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        return response()->json(['message' => 'Email verified.', 'user' => $user->fresh()]);
    }

    public function unverifyUserEmail($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['email_verified_at' => null]);

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'unverify_user_email',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => ['email_verified_at' => null],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'Email unverified.', 'user' => $user->fresh()]);
    }

    public function suspendUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['suspended_at' => now()]);

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'suspend_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => ['suspended_at' => now()->toDateTimeString()],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'User suspended.', 'user' => $user->fresh()]);
    }

    public function unsuspendUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['suspended_at' => null]);

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'unsuspend_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => ['suspended_at' => null],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'User unsuspended.', 'user' => $user->fresh()]);
    }

    public function resetUserPassword(Request $request, $userId)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['password' => Hash::make($request->password)]);

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'reset_user_password',
            'model_type' => User::class,
            'model_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|string',
            'user_type' => 'nullable|string',
            'phone' => 'nullable|string',
            'nationality' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'User created.', 'user' => $user], 201);
    }

    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'role' => 'sometimes|string',
            'user_type' => 'nullable|string',
            'phone' => 'nullable|string',
            'nationality' => 'nullable|string',
        ]);

        $user->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => $user->getOriginal(),
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'User updated.', 'user' => $user->fresh()]);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        AuditLog::create([
            'user_id' => request()->user()->id,
            'action' => 'delete_user',
            'model_type' => User::class,
            'model_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json(['message' => 'User deleted.']);
    }
}
