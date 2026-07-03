<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    public function index(Request $request, $tournamentId = null)
    {
        $query = Fixture::with(['players.user', 'tournament']);

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('course')) {
            $query->where('course', $request->course);
        }

        $fixtures = $query->orderBy('date')->orderBy('tee_off_time')->paginate(20);

        return response()->json($fixtures);
    }

    public function show($id)
    {
        $fixture = Fixture::with(['players.user', 'tournament', 'scores'])->findOrFail($id);

        return response()->json($fixture);
    }

    public function myFixtures(Request $request)
    {
        $user = $request->user();

        $fixtures = Fixture::whereHas('players', function ($query) use ($user) {
            $query->where('player_id', $user->id);
        })->with(['tournament', 'players.user'])->orderBy('date')->get();

        return response()->json($fixtures);
    }

    public function destroy($id)
    {
        $fixture = Fixture::findOrFail($id);

        $fixture->players()->detach();
        $fixture->scores()->delete();
        $fixture->delete();

        return response()->json(['message' => 'Fixture deleted successfully']);
    }
}
