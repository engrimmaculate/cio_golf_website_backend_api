<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\ScheduleEvent;
use App\Models\Sponsor;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LandingContentController extends Controller
{
    /**
     * Public: active awards for the landing page, ordered by position.
     */
    public function awards()
    {
        $awards = Award::where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $awards]);
    }

    /**
     * Public: active schedule events for the landing page, ordered by position.
     */
    public function schedule()
    {
        $events = ScheduleEvent::where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $events]);
    }

    /**
     * Public: active sponsors/partners for the landing page, ordered by sort order.
     */
    public function sponsors()
    {
        $sponsors = Sponsor::where('is_active', true)
            ->orderByRaw("CASE type WHEN 'sponsor' THEN 0 ELSE 1 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $sponsors]);
    }

    // ---- Awards ----

    public function storeAward(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'prize' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $award = Award::create($this->withAudit($request, $validated));

        return response()->json(['message' => 'Award created.', 'data' => $award], 201);
    }

    public function updateAward(Request $request, $id)
    {
        $award = Award::findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|string|max:255',
            'prize' => 'nullable|string|max:255',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $award->update($validated);

        return response()->json(['message' => 'Award updated.', 'data' => $award->fresh()]);
    }

    public function destroyAward(Request $request, $id)
    {
        $award = Award::findOrFail($id);
        $award->delete();

        return response()->json(['message' => 'Award deleted.']);
    }

    // ---- Schedule ----

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'date' => 'nullable|string|max:255',
            'time' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'players' => 'nullable|integer|min:0',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $event = ScheduleEvent::create($this->withAudit($request, $validated));

        return response()->json(['message' => 'Schedule event created.', 'data' => $event], 201);
    }

    public function updateSchedule(Request $request, $id)
    {
        $event = ScheduleEvent::findOrFail($id);

        $validated = $request->validate([
            'day' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'date' => 'nullable|string|max:255',
            'time' => 'nullable|string|max:255',
            'venue' => 'nullable|string|max:255',
            'players' => 'nullable|integer|min:0',
            'position' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $event->update($validated);

        return response()->json(['message' => 'Schedule event updated.', 'data' => $event->fresh()]);
    }

    public function destroySchedule(Request $request, $id)
    {
        $event = ScheduleEvent::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Schedule event deleted.']);
    }

    // ---- Sponsors ----

    public function storeSponsor(Request $request)
    {
        $validated = $request->validate($this->sponsorRules());

        $sponsor = Sponsor::create($this->withAudit($request, $validated));

        return response()->json(['message' => 'Sponsor created.', 'data' => $sponsor], 201);
    }

    public function updateSponsor(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $rules = $this->sponsorRules();
        $rules['name'] = 'sometimes|string|max:255';
        $validated = $request->validate($rules);

        $sponsor->update($validated);

        return response()->json(['message' => 'Sponsor updated.', 'data' => $sponsor->fresh()]);
    }

    public function destroySponsor(Request $request, $id)
    {
        $sponsor = Sponsor::findOrFail($id);
        $sponsor->delete();

        return response()->json(['message' => 'Sponsor deleted.']);
    }

    private function sponsorRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:sponsor,partner',
            'tier' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function withAudit(Request $request, array $data): array
    {
        $data['is_active'] = $data['is_active'] ?? true;

        $user = $request->user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'create_landing_content',
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'new_values' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $data;
    }
}
