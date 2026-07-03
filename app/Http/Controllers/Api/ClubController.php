<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClubController extends Controller
{
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
                $verificationToken = sha1($playerUser->email . Str::random(16));
                $playerUser->email_verified_at = null;
                $playerUser->save();

                $verificationUrl = url('/api/v1/auth/verify-email?token=' . $verificationToken . '&email=' . urlencode($email));

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
}
