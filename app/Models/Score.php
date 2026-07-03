<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'player_id',
        'hole_scores',
        'total_score',
        'par',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'hole_scores' => 'array',
            'total_score' => 'integer',
            'par' => 'integer',
            'published' => 'boolean',
        ];
    }

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }
}
