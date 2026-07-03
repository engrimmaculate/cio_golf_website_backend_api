<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function index(Request $request)
    {
        $query = Fixture::with(['tournament', 'players', 'scores']);

        if ($request->has('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $fixtures = $query->orderBy('date')->paginate(10);

        return response()->json($fixtures);
    }

    public function show($id)
    {
        $fixture = Fixture::with(['tournament', 'players', 'scores.player'])
            ->findOrFail($id);

        return response()->json($fixture);
    }

    public function myFixtures()
    {
        $user = auth()->user();

        $fixtures = Fixture::whereHas('players', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['tournament', 'scores' => function ($query) use ($user) {
            $query->where('player_id', $user->id);
        }])->orderBy('date')->paginate(10);

        return response()->json($fixtures);
    }
}
