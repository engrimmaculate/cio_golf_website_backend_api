<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'date',
        'tee_off_time',
        'course',
        'hole',
        'group_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tee_off_time' => 'datetime',
            'hole' => 'integer',
        ];
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'fixture_player')
            ->withPivot('score')
            ->withTimestamps();
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
