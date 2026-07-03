<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'nationality',
        'country',
        'handicap',
        'golf_club',
        'ranking',
        'tournament_experience',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'handicap' => 'float',
            'ranking' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
