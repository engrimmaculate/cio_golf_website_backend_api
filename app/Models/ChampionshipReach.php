<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChampionshipReach extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'african_countries',
        'continents',
        'golf_clubs',
        'official_sponsors',
        'pro_purse',
    ];

    protected function casts(): array
    {
        return [
            'african_countries' => 'integer',
            'continents' => 'integer',
            'golf_clubs' => 'integer',
            'official_sponsors' => 'integer',
            'pro_purse' => 'decimal:2',
        ];
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
