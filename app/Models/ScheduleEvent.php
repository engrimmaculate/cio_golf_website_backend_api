<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'title',
        'date',
        'time',
        'venue',
        'players',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'players' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
