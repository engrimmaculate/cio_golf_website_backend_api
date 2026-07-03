<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'player');

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('handicap_min')) {
            $query->where('handicap', '>=', $request->handicap_min);
        }

        if ($request->has('handicap_max')) {
            $query->where('handicap', '<=', $request->handicap_max);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $players = $query->orderBy('ranking')->paginate($request->get('per_page', 20));

        return response()->json($players);
    }

    public function show($id)
    {
        $player = User::where('role', 'player')->findOrFail($id);

        return response()->json($player);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string',
            'nationality' => 'nullable|string',
            'country' => 'nullable|string',
            'handicap' => 'nullable|numeric',
            'golf_club' => 'nullable|string',
            'ranking' => 'nullable|integer',
            'tournament_experience' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:passport_photo,id_document,handicap_certificate',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('file');
        $path = $file->store('documents/' . $user->id, 's3');

        $field = $request->document_type;
        $user->update([$field => $path]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'path' => $path,
        ]);
    }
}
