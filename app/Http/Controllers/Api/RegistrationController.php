<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'nationality' => 'required|string',
            'country' => 'required|string',
            'handicap' => 'nullable|numeric',
            'golf_club' => 'nullable|string',
            'ranking' => 'nullable|integer',
            'tournament_experience' => 'nullable|string',
        ]);

        $registration = Registration::create([
            'user_id' => Auth::id(),
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Registration submitted successfully',
            'registration' => $registration,
        ], 201);
    }

    public function saveDraft(Request $request)
    {
        $draft = Registration::updateOrCreate(
            ['user_id' => Auth::id(), 'status' => 'draft'],
            $request->only([
                'full_name', 'email', 'phone', 'nationality', 'country',
                'handicap', 'golf_club', 'ranking', 'tournament_experience',
            ])
        );

        return response()->json([
            'message' => 'Draft saved',
            'draft_id' => $draft->id,
        ]);
    }

    public function status(Request $request)
    {
        $registration = Registration::where('user_id', Auth::id())
            ->latest()
            ->first();

        return response()->json($registration);
    }
}
