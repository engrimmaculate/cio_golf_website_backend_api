<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Tournament::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->get('per_page', $request->get('limit', 12));
        $perPage = ($perPage > 0 && $perPage <= 500) ? $perPage : 12;

        $tournaments = $query->orderBy('id', 'desc')->paginate($perPage);

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

    public function overview()
    {
        $tournament = Tournament::where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->first();

        if (!$tournament) {
            $tournament = Tournament::where('status', 'published')
                ->orderBy('start_date', 'desc')
                ->first();
        }

        if (!$tournament) {
            return response()->json([
                'data' => [
                    'tournament' => null,
                    'registered_count' => 0,
                    'expected_limit' => 0,
                    'available_slots' => 0,
                    'african_countries' => 0,
                    'clubs_count' => 0,
                    'entry_fee' => 0,
                    'support_charges' => 0,
                    'reach' => [
                        'african_countries' => 0,
                        'continents' => 0,
                        'golf_clubs' => 0,
                        'official_sponsors' => 0,
                        'pro_purse' => 0,
                    ],
                ],
            ]);
        }

        $players = $tournament->players()
            ->with('club')
            ->whereNotIn('status', ['rejected'])
            ->get();

        $registered = $players->count();

        $expected = (int) ($tournament->max_tournament_player_expected
            ?: $tournament->total_slots
            ?: 0);

        $africanCountries = $players
            ->pluck('country')
            ->filter()
            ->unique()
            ->count();

        $clubsCount = $players
            ->pluck('club_id')
            ->filter()
            ->unique()
            ->count();

        $reach = $tournament->reach;

        return response()->json([
            'data' => [
                'tournament' => $tournament,
                'registered_count' => $registered,
                'expected_limit' => $expected,
                'available_slots' => max(0, (int) ($tournament->available_slots ?? $expected) - $registered),
                'african_countries' => $africanCountries,
                'clubs_count' => $clubsCount,
                'entry_fee' => (float) ($tournament->registration_fee ?? 0),
                'support_charges' => (float) ($tournament->support_charges ?? 0),
                'reach' => $reach ?? [
                    'african_countries' => $africanCountries ?: 0,
                    'continents' => 0,
                    'golf_clubs' => $clubsCount ?: 0,
                    'official_sponsors' => 0,
                    'pro_purse' => (float) ($tournament->prize_pool ?? 0),
                ],
            ],
        ]);
    }

    public function reach($id)
    {
        $tournament = Tournament::findOrFail($id);

        return response()->json([
            'data' => $tournament->reach ?? [
                'tournament_id' => $tournament->id,
                'african_countries' => 0,
                'continents' => 0,
                'golf_clubs' => 0,
                'official_sponsors' => 0,
                'pro_purse' => 0,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tournaments,slug',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'host_country' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'total_slots' => 'nullable|integer|min:1',
            'available_slots' => 'nullable|integer|min:0',
            'prize_pool' => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
            'support_charges' => 'nullable|numeric|min:0',
            'max_tournament_player_expected' => 'nullable|integer|min:1',
            'max_male_expected_per_club' => 'nullable|integer|min:0',
            'max_female_expected_per_club' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        if (!isset($validated['available_slots'])) {
            $validated['available_slots'] = $validated['total_slots'] ?? 0;
        }

        $tournament = Tournament::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_tournament',
            'model_type' => Tournament::class,
            'model_id' => $tournament->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Tournament created.', 'tournament' => $tournament], 201);
    }

    public function update(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tournaments,slug,' . $tournament->id,
            'description' => 'nullable|string',
            'venue' => 'sometimes|string|max:255',
            'host_country' => 'nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'total_slots' => 'nullable|integer|min:1',
            'available_slots' => 'nullable|integer|min:0',
            'prize_pool' => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
            'support_charges' => 'nullable|numeric|min:0',
            'max_tournament_player_expected' => 'nullable|integer|min:1',
            'max_male_expected_per_club' => 'nullable|integer|min:0',
            'max_female_expected_per_club' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        }

        $tournament->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_tournament',
            'model_type' => Tournament::class,
            'model_id' => $tournament->id,
            'old_values' => $tournament->getOriginal(),
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Tournament updated.', 'tournament' => $tournament->fresh()]);
    }

    public function destroy(Request $request, $id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_tournament',
            'model_type' => Tournament::class,
            'model_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Tournament deleted.']);
    }
}
