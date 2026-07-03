<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::with(['club', 'user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        $players = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($players);
    }

    public function show($id)
    {
        $player = Player::with(['club', 'user'])->findOrFail($id);
        return response()->json($player);
    }

    public function myProfile(Request $request)
    {
        $player = Player::where('user_id', $request->user()->id)->first();

        if (!$player) {
            return response()->json(['message' => 'Player profile not found. Contact your club.'], 404);
        }

        return response()->json($player);
    }

    public function completeRegistration(Request $request)
    {
        $player = Player::where('user_id', $request->user()->id)->firstOrFail();

        if ($player->status === 'completed') {
            return response()->json(['message' => 'Registration already completed.'], 400);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'handicap' => 'nullable|numeric',
            'shirt_size' => 'nullable|string|max:10',
            'ranking' => 'nullable|integer',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'experience' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('player-photos', 'public');
            $validated['profile_photo'] = $path;
        }

        $player->update([
            'full_name' => $validated['full_name'] ?? $player->full_name,
            'phone' => $validated['phone'] ?? $player->phone,
            'gender' => $validated['gender'] ?? $player->gender,
            'handicap' => $validated['handicap'] ?? $player->handicap,
            'shirt_size' => $validated['shirt_size'] ?? $player->shirt_size,
            'ranking' => $validated['ranking'] ?? $player->ranking,
            'city' => $validated['city'] ?? $player->city,
            'state' => $validated['state'] ?? $player->state,
            'country' => $validated['country'] ?? $player->country,
            'category' => $validated['category'] ?? $player->category,
            'experience' => $validated['experience'] ?? $player->experience,
            'profile_photo' => $validated['profile_photo'] ?? $player->profile_photo,
            'status' => 'completed',
        ]);

        $request->user()->update([
            'name' => $validated['full_name'] ?? $request->user()->name,
            'phone' => $validated['phone'] ?? $request->user()->phone,
        ]);

        return response()->json([
            'message' => 'Registration completed successfully. Awaiting admin approval.',
            'player' => $player->fresh(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $player = Player::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'phone' => 'nullable|string',
            'handicap' => 'nullable|numeric',
            'shirt_size' => 'nullable|string|max:10',
            'gender' => 'nullable|in:male,female,other',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'experience' => 'nullable|string',
        ]);

        $player->update($validated);

        return response()->json(['message' => 'Profile updated', 'player' => $player]);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:profile_photo,handicap_certificate,id_document',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('file');
        $path = $file->store('documents/' . $user->id, 'public');

        if ($request->document_type === 'profile_photo') {
            $player = Player::where('user_id', $user->id)->first();
            if ($player) {
                $player->update(['profile_photo' => $path]);
            }
        }

        $user->update([$request->document_type => $path]);

        return response()->json(['message' => 'Document uploaded', 'path' => $path]);
    }
}
