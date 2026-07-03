<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Fixture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function myScores()
    {
        $user = auth()->user();

        $scores = Score::where('player_id', $user->id)
            ->with('fixture.tournament')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($scores);
    }

    public function leaderboard(Request $request)
    {
        $query = Score::with('player');

        if ($request->has('fixture_id')) {
            $query->where('fixture_id', $request->fixture_id);
        }

        if ($request->has('tournament_id')) {
            $query->whereHas('fixture', function ($q) use ($request) {
                $q->where('tournament_id', $request->tournament_id);
            });
        }

        $scores = $query->orderBy('total_score')
            ->paginate(20);

        return response()->json($scores);
    }
}
