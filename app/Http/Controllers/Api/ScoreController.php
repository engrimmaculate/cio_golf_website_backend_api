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

        $players = \App\Models\Player::with(['user', 'club'])
            ->where('tournament_id', $tournamentId)
            ->whereNotIn('status', ['rejected'])
            ->get();

        $scores = Score::where('published', true)
            ->whereHas('fixture', function ($query) use ($tournamentId) {
                $query->where('tournament_id', $tournamentId);
            })
            ->with('fixture')
            ->get()
            ->sortBy(function ($score) {
                return optional($score->fixture)->date ?? now()->toDateString();
            })
            ->values();

        $hasScores = $scores->isNotEmpty();

        $roundMap = [];
        foreach ($scores as $score) {
            $roundMap[$score->player_id][] = $score->total_score;
        }

        $rows = $players->map(function ($player) use ($roundMap, $hasScores) {
            $rounds = $roundMap[$player->id] ?? [];
            $total = $hasScores && count($rounds) > 0 ? (int) array_sum($rounds) : 0;

            return [
                'player_id' => $player->id,
                'name' => $player->full_name ?: ($player->user->name ?? 'Golfer'),
                'country' => $player->country ?: ($player->user->country ?? ''),
                'club' => optional($player->club)->name,
                'handicap' => $player->handicap,
                'total' => $total,
                'rounds' => count($rounds),
                'round_scores' => array_slice($rounds, 0, 4),
                'profile_photo_url' => $player->profile_photo_url,
            ];
        });

        if ($hasScores) {
            $rows = $rows->sortBy(function ($row) {
                return [
                    $row['rounds'] > 0 ? 0 : 1,
                    $row['rounds'] > 0 ? $row['total'] : ($row['handicap'] ?? PHP_FLOAT_MAX),
                ];
            });
        } else {
            $rows = $rows->sortBy(function ($row) {
                return $row['handicap'] ?? PHP_FLOAT_MAX;
            });
        }

        $ranked = $rows->values()->map(function ($row, $index) {
            $row['rank'] = $index + 1;
            return $row;
        });

        return response()->json([
            'data' => $ranked,
            'registered_count' => $players->count(),
            'has_scores' => $hasScores,
            'tournament' => $tournament->only(['id', 'name', 'venue', 'start_date', 'end_date']),
        ]);
    }
}
