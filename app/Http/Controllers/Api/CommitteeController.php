<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\Fixture;
use App\Models\Score;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommitteeController extends Controller
{
    public function createTournament(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string',
            'host_country' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_deadline' => 'required|date|before:start_date',
            'total_slots' => 'required|integer',
            'prize_pool' => 'nullable|numeric',
            'image' => 'nullable|string',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        $validated['available_slots'] = $validated['total_slots'];
        $validated['status'] = 'draft';

        $tournament = Tournament::create($validated);

        return response()->json(['message' => 'Tournament created', 'tournament' => $tournament], 201);
    }

    public function updateTournament(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'sometimes|string',
            'host_country' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'registration_deadline' => 'sometimes|date',
            'total_slots' => 'sometimes|integer',
            'prize_pool' => 'nullable|numeric',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $tournament->update($validated);

        return response()->json(['message' => 'Tournament updated', 'tournament' => $tournament]);
    }

    public function publishTournament($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->update(['status' => 'published']);

        return response()->json(['message' => 'Tournament published']);
    }

    public function createFixture(Request $request)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'date' => 'required|date',
            'tee_off_time' => 'required',
            'course' => 'required|string',
            'hole' => 'nullable|integer',
            'group_name' => 'required|string',
        ]);

        $fixture = Fixture::create($validated);

        return response()->json(['message' => 'Fixture created', 'fixture' => $fixture], 201);
    }

    public function assignPlayers(Request $request, $fixtureId)
    {
        $validated = $request->validate([
            'player_ids' => 'required|array',
            'player_ids.*' => 'exists:users,id',
        ]);

        $fixture = Fixture::findOrFail($fixtureId);
        $fixture->players()->sync($validated['player_ids']);

        return response()->json(['message' => 'Players assigned to fixture']);
    }

    public function submitScores(Request $request, $fixtureId)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.player_id' => 'required|exists:users,id',
            'scores.*.hole_scores' => 'required|array',
            'scores.*.total_score' => 'required|integer',
            'scores.*.par' => 'nullable|integer',
        ]);

        $fixture = Fixture::findOrFail($fixtureId);

        foreach ($validated['scores'] as $scoreData) {
            Score::updateOrCreate(
                ['fixture_id' => $fixtureId, 'player_id' => $scoreData['player_id']],
                [
                    'hole_scores' => $scoreData['hole_scores'],
                    'total_score' => $scoreData['total_score'],
                    'par' => $scoreData['par'] ?? 72,
                    'published' => false,
                ]
            );
        }

        return response()->json(['message' => 'Scores submitted']);
    }

    public function publishScores($fixtureId)
    {
        Score::where('fixture_id', $fixtureId)->update(['published' => true]);

        Fixture::findOrFail($fixtureId)->update(['status' => 'completed']);

        return response()->json(['message' => 'Scores published']);
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|in:email,sms,in_app,push',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        foreach ($validated['user_ids'] as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
            ]);
        }

        return response()->json(['message' => 'Notifications sent']);
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:email,sms,in_app,push',
            'title' => 'required|string',
            'message' => 'required|string',
            'role' => 'nullable|in:player,committee,admin',
        ]);

        $query = User::query();
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $userIds = $query->pluck('id');

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
            ]);
        }

        return response()->json(['message' => 'Broadcast sent to ' . $userIds->count() . ' users']);
    }

    public function getReports($type, Request $request)
    {
        switch ($type) {
            case 'participation':
                $data = [
                    'total_registrations' => \App\Models\Registration::count(),
                    'by_status' => \App\Models\Registration::selectRaw('status, COUNT(*) as count')->groupBy('status')->get(),
                    'by_country' => User::where('role', 'player')->selectRaw('country, COUNT(*) as count')->groupBy('country')->get(),
                ];
                break;

            case 'scores':
                $data = [
                    'average_score' => Score::avg('total_score'),
                    'best_score' => Score::min('total_score'),
                    'total_rounds' => Score::count(),
                ];
                break;

            case 'rankings':
                $data = User::where('role', 'player')
                    ->whereNotNull('ranking')
                    ->orderBy('ranking')
                    ->take(50)
                    ->get(['id', 'name', 'country', 'ranking', 'handicap']);
                break;

            default:
                return response()->json(['message' => 'Unknown report type'], 400);
        }

        return response()->json($data);
    }
}
