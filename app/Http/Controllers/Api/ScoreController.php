<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Models\Fixture;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function fixtureScores($fixtureId)
    {
        $scores = Score::where('fixture_id', $fixtureId)
            ->with('player')
            ->orderBy('total_score')
            ->get();

        return response()->json($scores);
    }

    public function myScores(Request $request)
    {
        $user = $request->user();

        $scores = Score::where('player_id', $user->id)
            ->with(['fixture.tournament'])
            ->where('published', true)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($scores);
    }

    public function leaderboard($tournamentId)
    {
        $tournament = \App\Models\Tournament::findOrFail($tournamentId);

        $leaderboard = Score::whereHas('fixture', function ($query) use ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        })
        ->with('player')
        ->selectRaw('player_id, MIN(total_score) as best_score, COUNT(*) as rounds')
        ->groupBy('player_id')
        ->orderBy('best_score')
        ->get();

        return response()->json($leaderboard);
    }
}
