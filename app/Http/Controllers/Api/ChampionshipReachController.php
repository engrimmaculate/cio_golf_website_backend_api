<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChampionshipReach;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ChampionshipReachController extends Controller
{
    public function index(Request $request)
    {
        $query = ChampionshipReach::query()->with('tournament:id,name');

        if ($request->has('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        $perPage = (int) $request->get('per_page', $request->get('limit', 20));
        $perPage = ($perPage > 0 && $perPage <= 500) ? $perPage : 20;

        return response()->json($query->orderBy('id', 'desc')->paginate($perPage));
    }

    public function show($id)
    {
        $reach = ChampionshipReach::with('tournament:id,name')->findOrFail($id);

        return response()->json(['data' => $reach]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id|unique:championship_reaches,tournament_id',
            'african_countries' => 'required|integer|min:0',
            'continents' => 'required|integer|min:0',
            'golf_clubs' => 'required|integer|min:0',
            'official_sponsors' => 'required|integer|min:0',
            'pro_purse' => 'required|numeric|min:0',
        ]);

        $reach = ChampionshipReach::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_championship_reach',
            'model_type' => ChampionshipReach::class,
            'model_id' => $reach->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Championship reach created.',
            'data' => $reach->load('tournament:id,name'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $reach = ChampionshipReach::findOrFail($id);

        $validated = $request->validate([
            'tournament_id' => 'sometimes|exists:tournaments,id|unique:championship_reaches,tournament_id,' . $reach->id,
            'african_countries' => 'sometimes|integer|min:0',
            'continents' => 'sometimes|integer|min:0',
            'golf_clubs' => 'sometimes|integer|min:0',
            'official_sponsors' => 'sometimes|integer|min:0',
            'pro_purse' => 'sometimes|numeric|min:0',
        ]);

        $oldValues = $reach->getOriginal();

        $reach->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_championship_reach',
            'model_type' => ChampionshipReach::class,
            'model_id' => $reach->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Championship reach updated.',
            'data' => $reach->fresh()->load('tournament:id,name'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $reach = ChampionshipReach::findOrFail($id);
        $reach->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_championship_reach',
            'model_type' => ChampionshipReach::class,
            'model_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Championship reach deleted.']);
    }
}
