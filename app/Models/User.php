<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject, CanResetPasswordContract
{
    use HasApiTokens, CanResetPassword, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'user_type',
        'nationality',
        'country',
        'phone',
        'avatar',
        'handicap',
        'golf_club',
        'ranking',
        'tournament_experience',
        'handicap_certificate',
        'id_document',
        'passport_photo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'handicap' => 'float',
            'ranking' => 'integer',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
        ];
    }

    public function fixtures()
    {
        return $this->belongsToMany(Fixture::class, 'fixture_player')
            ->withPivot('score')
            ->withTimestamps();
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'player_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function club()
    {
        return $this->hasOne(Club::class);
    }

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function sponsorProfile()
    {
        return $this->hasOne(SponsorProfile::class);
    }
}
