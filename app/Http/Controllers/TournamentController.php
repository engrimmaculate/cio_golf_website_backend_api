<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::withCount('fixtures')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return response()->json($tournaments);
    }

    public function show($slug)
    {
        $tournament = Tournament::where('slug', $slug)
            ->with('fixtures')
            ->firstOrFail();

        return response()->json($tournament);
    }

    public function upcoming()
    {
        $tournaments = Tournament::where('start_date', '>=', now())
            ->where('status', 'published')
            ->orderBy('start_date')
            ->paginate(10);

        return response()->json($tournaments);
    }

    public function published()
    {
        $tournaments = Tournament::where('status', 'published')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return response()->json($tournaments);
    }
}
