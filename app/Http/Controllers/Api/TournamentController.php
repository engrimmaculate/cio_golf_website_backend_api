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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'host_country' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'total_slots' => 'nullable|integer|min:1',
            'prize_pool' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
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
            'description' => 'nullable|string',
            'venue' => 'sometimes|string|max:255',
            'host_country' => 'nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'total_slots' => 'nullable|integer|min:1',
            'available_slots' => 'nullable|integer|min:0',
            'prize_pool' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
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
