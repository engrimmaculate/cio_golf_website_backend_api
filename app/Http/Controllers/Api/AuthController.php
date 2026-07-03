<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use App\Models\Player;
use App\Models\SponsorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['club', 'player', 'sponsorProfile']);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string',
            'user_type' => 'required|string|in:club,sponsor',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'player',
            'user_type' => $request->user_type,
            'phone' => $request->phone,
        ]);

        if ($request->user_type === 'club') {
            Club::create([
                'user_id' => $user->id,
                'club_name' => $request->full_name,
                'contact_person' => $request->full_name,
                'contact_email' => $request->email,
                'contact_phone' => $request->phone,
            ]);
        } elseif ($request->user_type === 'sponsor') {
            SponsorProfile::create([
                'user_id' => $user->id,
                'company_name' => $request->full_name,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load(['club', 'sponsorProfile']);

        $this->sendVerificationEmail($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    protected function sendVerificationEmail(User $user): void
    {
        $verificationToken = sha1($user->email . $user->created_at->timestamp);
        $verificationUrl = config('app.frontend_url') . '/verify-email?token=' . $verificationToken . '&email=' . urlencode($user->email);

        try {
            Mail::send('emails.verify-email', [
                'name' => $user->name,
                'verificationUrl' => $verificationUrl,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Verify Your Email - CIO International Golf Championship');
            });
        } catch (\Exception $e) {
            // Log error but don't block registration
        }
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json(['message' => 'Verification email sent.']);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        $expectedToken = sha1($user->email . $user->created_at->timestamp);

        if ($request->token !== $expectedToken) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 400);
        }

        $user->update(['email_verified_at' => now()]);

        $player = Player::where('user_id', $user->id)->first();
        if ($player && $player->status === 'invited') {
            $player->update(['status' => 'verified', 'verified_at' => now()]);
        }

        return response()->json([
            'message' => 'Email verified successfully. You can now log in.',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['club', 'player', 'sponsorProfile']);
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent'])
            : response()->json(['message' => 'Unable to send reset link'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset'])
            : response()->json(['message' => 'Invalid token'], 400);
    }
}
