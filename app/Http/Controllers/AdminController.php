<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Tournament;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_players' => User::where('role', 'player')->count(),
            'total_committee' => User::where('role', 'committee')->count(),
            'total_tournaments' => Tournament::count(),
            'upcoming_tournaments' => Tournament::where('start_date', '>=', now())->count(),
            'total_registrations' => Registration::count(),
            'pending_registrations' => Registration::where('status', 'pending')->count(),
            'approved_registrations' => Registration::where('status', 'approved')->count(),
            'rejected_registrations' => Registration::where('status', 'rejected')->count(),
        ];

        return response()->json($stats);
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(15);

        return response()->json($users);
    }

    public function updateRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:player,committee,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user,
        ]);
    }

    public function analytics()
    {
        $registrationsByCountry = Registration::selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->get();

        $registrationsByStatus = Registration::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        $playersByHandicap = User::where('role', 'player')
            ->selectRaw('
                CASE
                    WHEN handicap <= 5 THEN "0-5"
                    WHEN handicap <= 10 THEN "6-10"
                    WHEN handicap <= 15 THEN "11-15"
                    WHEN handicap <= 20 THEN "16-20"
                    ELSE "20+"
                END as range,
                count(*) as count
            ')
            ->groupBy('range')
            ->get();

        return response()->json([
            'registrations_by_country' => $registrationsByCountry,
            'registrations_by_status' => $registrationsByStatus,
            'players_by_handicap' => $playersByHandicap,
        ]);
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($logs);
    }
}
