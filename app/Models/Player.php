<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
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

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
