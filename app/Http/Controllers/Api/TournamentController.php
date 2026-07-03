<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Tournament::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->latest()->paginate($request->get('per_page', 12));

        return response()->json($tournaments);
    }

    public function show($id)
    {
        $tournament = Tournament::with(['fixtures.players'])->findOrFail($id);

        return response()->json($tournament);
    }

    public function upcoming()
    {
        $tournaments = Tournament::where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        return response()->json($tournaments);
    }

    public function published()
    {
        $tournaments = Tournament::where('status', 'published')
            ->orderBy('start_date')
            ->get();

        return response()->json($tournaments);
    }
}
