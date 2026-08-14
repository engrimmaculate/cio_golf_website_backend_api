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
        'support_charges',
        'max_tournament_player_expected',
        'max_male_expected_per_club',
        'max_female_expected_per_club',
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
            'support_charges' => 'decimal:2',
            'total_slots' => 'integer',
            'available_slots' => 'integer',
            'max_tournament_player_expected' => 'integer',
            'max_male_expected_per_club' => 'integer',
            'max_female_expected_per_club' => 'integer',
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

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function reach()
    {
        return $this->hasOne(ChampionshipReach::class);
    }

    public function getEditionNumberAttribute(): int
    {
        preg_match('/(\d+)\s*(?:st|nd|rd|th)\s*edition/i', $this->name ?? '', $matches);

        return isset($matches[1]) ? (int) $matches[1] : 0;
    }


     /**
     * Calculate total payable fee (Registration Fee + Support Charges)
     */
    public function getTotalFeeAttribute(): float
    {
        $baseFee = $this->registration_fee ?? config('services.paystack.registration_fee', 50000);
        $supportCharges = $this->support_charges ?? 500;

        return (float) ($baseFee + $supportCharges);
    }
}
