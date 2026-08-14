<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    // ──────────────────────────────────────────────
    // Club Owner (role:player, user_type:club) routes
    // ──────────────────────────────────────────────

    public function profile(Request $request)
    {
        $club = Club::where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($club);
    }

    public function updateProfile(Request $request)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'club_name' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
        ]);

        $club->update($validated);

        return response()->json(['message' => 'Club profile updated', 'club' => $club]);
    }

    public function downloadCsvTemplate()
    {
        $headers = ['Fullname', 'Gender', 'Phone', 'Email', 'Category', 'Handicap (optional)'];
        $filename = 'cio-golf-player-upload-template.csv';

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);

        fputcsv($handle, ['John Doe', 'Male', '+2348012345678', 'john@example.com', 'Male Pro', '12.5']);
        fputcsv($handle, ['Jane Smith', 'Female', '+2348098765432', 'jane@example.com', 'Female Amateur', '']);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function uploadPlayers(Request $request)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $expected = ['Fullname', 'Gender', 'Phone', 'Email', 'Category', 'Handicap (optional)'];
        $normalized = array_map('trim', $header);
        $headerMap = [];
        foreach ($expected as $i => $col) {
            $found = false;
            foreach ($normalized as $j => $h) {
                if (strcasecmp($h, $col) === 0 || stripos($h, $col) !== false) {
                    $headerMap[$col] = $j;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                fclose($handle);
                return response()->json([
                    'message' => 'Invalid CSV format. Expected columns: ' . implode(', ', $expected),
                ], 422);
            }
        }

        $created = [];
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $fullName = trim($row[$headerMap['Fullname']] ?? '');
            $gender = trim($row[$headerMap['Gender']] ?? '');
            $phone = trim($row[$headerMap['Phone']] ?? '');
            $email = trim($row[$headerMap['Email']] ?? '');
            $category = trim($row[$headerMap['Category']] ?? '');
            $handicap = trim($row[$headerMap['Handicap (optional)']] ?? '');

            if (empty($fullName) || empty($email)) {
                $errors[] = "Row {$rowNum}: Fullname and Email are required.";
                continue;
            }

            if (Player::where('email', $email)->exists()) {
                $errors[] = "Row {$rowNum}: Email {$email} already registered.";
                continue;
            }

            $tempPassword = Str::random(12);

            $playerUser = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make($tempPassword),
                'role' => 'player',
                'user_type' => 'player',
                'phone' => $phone,
            ]);

            $player = Player::create([
                'club_id' => $club->id,
                'user_id' => $playerUser->id,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'gender' => strtolower($gender) === 'male' ? 'male' : (strtolower($gender) === 'female' ? 'female' : null),
                'category' => $category ?: null,
                'handicap' => $handicap !== '' && is_numeric($handicap) ? (float) $handicap : null,
                'status' => 'invited',
            ]);

            try {
                $playerUser->email_verified_at = null;
                $playerUser->save();

                $verificationToken = sha1($playerUser->email . $playerUser->created_at->timestamp);
                $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
                $verificationUrl = $frontendUrl . '/verify-email?token=' . $verificationToken . '&email=' . urlencode($email);

                Mail::send('emails.player-invite', [
                    'name' => $fullName,
                    'clubName' => $club->club_name,
                    'verificationUrl' => $verificationUrl,
                    'email' => $email,
                    'password' => $tempPassword,
                ], function ($message) use ($email, $fullName) {
                    $message->to($email, $fullName)
                        ->subject('You\'ve been registered for CIO International Golf Championship');
                });
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Player created but email failed to send to {$email}.";
            }

            $created[] = [
                'name' => $fullName,
                'email' => $email,
                'status' => 'invited',
            ];
        }

        fclose($handle);

        return response()->json([
            'message' => count($created) . ' player(s) uploaded successfully.',
            'created' => $created,
            'errors' => $errors,
        ], empty($errors) ? 201 : 201);
    }

    public function listPlayers(Request $request)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        $players = Player::where('club_id', $club->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($players);
    }

    // ──────────────────────────────────────────────
    // Admin & Committee CRUD for Clubs
    // ──────────────────────────────────────────────

    public function all(Request $request)
    {
        $query = Club::withCount('players');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('club_name', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $clubs = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json($clubs);
    }

    public function show($id)
    {
        $club = Club::withCount('players')->with('players')->findOrFail($id);
        return response()->json($club);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'club_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'm1_18' => 'nullable|integer|min:0',
            'm20_28' => 'nullable|integer|min:0',
            'm_snr' => 'nullable|integer|min:0',
            'l1_20' => 'nullable|integer|min:0',
            'l21_28' => 'nullable|integer|min:0',
            'l_snr' => 'nullable|integer|min:0',
            'edition' => 'nullable|string|max:50',
        ]);

        $validated['status'] = 'active';
        $validated['country'] = $validated['country'] ?? 'Nigeria';
        $validated['edition'] = $validated['edition'] ?? '7th';

        $club = Club::create($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_club',
            'model_type' => Club::class,
            'model_id' => $club->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Club created successfully.',
            'club' => $club,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $club = Club::findOrFail($id);

        $validated = $request->validate([
            'club_name' => 'sometimes|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'm1_18' => 'nullable|integer|min:0',
            'm20_28' => 'nullable|integer|min:0',
            'm_snr' => 'nullable|integer|min:0',
            'l1_20' => 'nullable|integer|min:0',
            'l21_28' => 'nullable|integer|min:0',
            'l_snr' => 'nullable|integer|min:0',
            'edition' => 'nullable|string|max:50',
        ]);

        $oldValues = $club->getOriginal();
        $club->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_club',
            'model_type' => Club::class,
            'model_id' => $club->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Club updated successfully.',
            'club' => $club->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $club = Club::findOrFail($id);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_club',
            'model_type' => Club::class,
            'model_id' => $club->id,
            'old_values' => $club->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $club->delete();

        return response()->json(['message' => 'Club deleted successfully.']);
    }

    // ──────────────────────────────────────────────
    // Admin & Committee CRUD for Players per Club
    // ──────────────────────────────────────────────

    public function clubPlayers(Request $request, $clubId)
    {
        $club = Club::findOrFail($clubId);

        $query = Player::where('club_id', $club->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $players = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'club' => $club,
            'players' => $players,
        ]);
    }

    public function showClubPlayer($clubId, $playerId)
    {
        $player = Player::where('club_id', $clubId)->findOrFail($playerId);
        return response()->json($player);
    }

    public function storeClubPlayer(Request $request, $clubId)
    {
        $club = Club::findOrFail($clubId);

        return $this->storePlayerForClub($request, $club);
    }

    public function updateClubPlayer(Request $request, $clubId, $playerId)
    {
        $club = Club::findOrFail($clubId);

        return $this->updatePlayerForClub($request, $club, $playerId);
    }

    public function destroyClubPlayer(Request $request, $clubId, $playerId)
    {
        $club = Club::findOrFail($clubId);

        return $this->destroyPlayerForClub($request, $club, $playerId);
    }

    // ──────────────────────────────────────────────
    // Club owner (self) player CRUD
    // ──────────────────────────────────────────────

    public function storeSelfPlayer(Request $request)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        return $this->storePlayerForClub($request, $club);
    }

    public function updateSelfPlayer(Request $request, $playerId)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        return $this->updatePlayerForClub($request, $club, $playerId);
    }

    public function destroySelfPlayer(Request $request, $playerId)
    {
        if ($request->user()->user_type !== 'club') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $club = Club::where('user_id', $request->user()->id)->firstOrFail();

        return $this->destroyPlayerForClub($request, $club, $playerId);
    }

    private function storePlayerForClub(Request $request, Club $club)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:players,email',
            'phone' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'category' => 'nullable|string|max:255',
            'handicap' => 'nullable|numeric|min:0|max:54',
            'shirt_size' => 'nullable|string|max:10',
            'ranking' => 'nullable|integer|min:0',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'experience' => 'nullable|string',
            'tournament_id' => 'nullable|exists:tournaments,id',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'create_user' => 'nullable|boolean',
        ]);

        unset($validated['profile_photo']);

        $photoPath = null;
        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            $photoPath = $request->file('profile_photo')->store('player-photos', 'local');
            $validated['profile_photo'] = $photoPath;
        }

        $createUser = $validated['create_user'] ?? false;
        unset($validated['create_user']);

        $playerUserId = null;
        if ($createUser) {
            $tempPassword = Str::random(12);
            $playerUser = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($tempPassword),
                'role' => 'player',
                'user_type' => 'player',
                'phone' => $validated['phone'] ?? null,
                'avatar' => $photoPath,
            ]);
            $playerUserId = $playerUser->id;

            try {
                $verificationToken = sha1($playerUser->email . $playerUser->created_at->timestamp);
                $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
                $verificationUrl = $frontendUrl . '/verify-email?token=' . $verificationToken . '&email=' . urlencode($validated['email']);

                Mail::send('emails.player-invite', [
                    'name' => $validated['full_name'],
                    'clubName' => $club->club_name,
                    'verificationUrl' => $verificationUrl,
                    'email' => $validated['email'],
                    'password' => $tempPassword,
                ], function ($message) use ($validated) {
                    $message->to($validated['email'], $validated['full_name'])
                        ->subject('You\'ve been registered for CIO International Golf Championship');
                });
            } catch (\Exception $e) {
                // Log but don't fail
            }
        }

        $validated['club_id'] = $club->id;
        $validated['user_id'] = $playerUserId;
        $validated['status'] = 'pending';

        $player = Player::create($validated);

        // Create payment record if tournament is assigned
        if (!empty($validated['tournament_id']) && $playerUserId) {
            $tournament = \App\Models\Tournament::find($validated['tournament_id']);
            $amount = $tournament->registration_fee ?? config('services.paystack.registration_fee', 50000);
            $paymentToken = \App\Models\Payment::generateToken();

            \App\Models\Payment::create([
                'player_id' => $player->id,
                'user_id' => $playerUserId,
                'reference' => \App\Models\Payment::generateReference(),
                'amount' => $amount,
                'currency' => 'NGN',
                'status' => 'pending',
                'token' => $paymentToken,
                'token_expires_at' => now()->addDays(7),
                'description' => ($tournament->name ?? 'CIO Golf Classic') . ' - Registration Fee',
            ]);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'create_player',
            'model_type' => Player::class,
            'model_id' => $player->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Player added to club successfully.',
            'player' => $player,
        ], 201);
    }

    private function updatePlayerForClub(Request $request, Club $club, $playerId)
    {
        $player = Player::where('club_id', $club->id)->findOrFail($playerId);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:players,email,' . $playerId,
            'phone' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'category' => 'nullable|string|max:255',
            'handicap' => 'nullable|numeric|min:0|max:54',
            'shirt_size' => 'nullable|string|max:10',
            'ranking' => 'nullable|integer|min:0',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'experience' => 'nullable|string',
            'tournament_id' => 'nullable|exists:tournaments,id',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'status' => 'nullable|in:pending,invited,verified,completed,approved,rejected',
        ]);

        unset($validated['profile_photo']);

        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            $newPath = $request->file('profile_photo')->store('player-photos', 'local');
            $validated['profile_photo'] = $newPath;

            $oldPhoto = $player->profile_photo;
            if ($oldPhoto && $oldPhoto !== $newPath && Storage::disk('local')->exists($oldPhoto)) {
                Storage::disk('local')->delete($oldPhoto);
            }

            if ($player->user) {
                $player->user->update(['avatar' => $newPath]);
            }
        }

        $oldValues = $player->getOriginal();
        $player->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_player',
            'model_type' => Player::class,
            'model_id' => $player->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Player updated successfully.',
            'player' => $player->fresh(),
        ]);
    }

    private function destroyPlayerForClub(Request $request, Club $club, $playerId)
    {
        $player = Player::where('club_id', $club->id)->findOrFail($playerId);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_player',
            'model_type' => Player::class,
            'model_id' => $player->id,
            'old_values' => $player->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($player->profile_photo && Storage::disk('local')->exists($player->profile_photo)) {
            Storage::disk('local')->delete($player->profile_photo);
        }

        $player->delete();

        return response()->json(['message' => 'Player deleted successfully.']);
    }
}
