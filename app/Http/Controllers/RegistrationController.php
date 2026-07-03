<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'handicap' => 'sometimes|numeric|min:0',
            'golf_club' => 'sometimes|string|max:255',
            'ranking' => 'sometimes|integer|min:1',
            'tournament_experience' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        $registration = Registration::create($data);

        return response()->json([
            'message' => 'Registration submitted successfully',
            'registration' => $registration,
        ], 201);
    }

    public function saveDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:255',
            'nationality' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'handicap' => 'sometimes|numeric|min:0',
            'golf_club' => 'sometimes|string|max:255',
            'ranking' => 'sometimes|integer|min:1',
            'tournament_experience' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required to save draft'], 401);
        }

        $registration = Registration::updateOrCreate(
            ['user_id' => auth()->id(), 'status' => 'pending'],
            $request->only([
                'full_name', 'email', 'phone', 'nationality', 'country',
                'handicap', 'golf_club', 'ranking', 'tournament_experience'
            ])
        );

        return response()->json([
            'message' => 'Draft saved successfully',
            'registration' => $registration,
        ]);
    }

    public function status()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $registration = Registration::where('user_id', auth()->id())
            ->latest()
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'No registration found']);
        }

        return response()->json([
            'status' => $registration->status,
            'registration' => $registration,
        ]);
    }
}
