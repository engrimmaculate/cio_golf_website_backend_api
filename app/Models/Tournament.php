<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'venue',
        'host_country',
        'start_date',
        'end_date',
        'registration_deadline',
        'total_slots',
        'available_slots',
        'prize_pool',
        'registration_fee',
        'status',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'date',
            'prize_pool' => 'decimal:2',
            'registration_fee' => 'decimal:2',
            'total_slots' => 'integer',
            'available_slots' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->name);
            }
        });
    }

    public function fixtures()
    {
        return $this->hasMany(Fixture::class);
    }
}
