<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Player extends Model
{
    use HasFactory;

    protected $appends = ['profile_photo_url'];

    protected $fillable = [
        'club_id',
        'user_id',
        'tournament_id',
        'full_name',
        'email',
        'phone',
        'gender',
        'category',
        'handicap',
        'shirt_size',
        'ranking',
        'city',
        'state',
        'country',
        'profile_photo',
        'experience',
        'status',
        'verified_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'handicap' => 'float',
            'ranking' => 'integer',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        $root = rtrim(request()->root(), '/');

        if (empty($this->profile_photo)) {
            return $root . '/logo/cio-logo.jpeg';
        }

        try {
            if (!Storage::disk('local')->exists($this->profile_photo)) {
                return $root . '/logo/cio-logo.jpeg';
            }

            $signed = URL::temporarySignedRoute(
                'player.photo',
                now()->addHours(1),
                ['player' => $this->id]
            );

            $appUrl = rtrim(config('app.url'), '/');
            if (str_starts_with($signed, $appUrl . '/')) {
                return $root . substr($signed, strlen($appUrl));
            }

            return $signed;
        } catch (\Throwable $e) {
            return $root . '/logo/cio-logo.jpeg';
        }
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
