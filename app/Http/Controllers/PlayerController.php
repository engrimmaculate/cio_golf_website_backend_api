<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'player');

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('ranking')) {
            $query->orderBy('ranking');
        }

        $players = $query->paginate(15);

        return response()->json($players);
    }

    public function show($id)
    {
        $player = User::where('role', 'player')->findOrFail($id);

        $player->load(['fixtures', 'scores.fixture']);

        return response()->json($player);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'nationality' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'handicap' => 'sometimes|numeric|min:0',
            'golf_club' => 'sometimes|string|max:255',
            'ranking' => 'sometimes|integer|min:1',
            'tournament_experience' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only([
            'name', 'nationality', 'country', 'phone',
            'handicap', 'golf_club', 'ranking', 'tournament_experience'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:handicap_certificate,id_document,passport_photo,avatar',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $file = $request->file('file');
        $path = $file->store($request->document_type . 's');

        $user->update([
            $request->document_type => $path,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'path' => $path,
        ]);
    }
}
