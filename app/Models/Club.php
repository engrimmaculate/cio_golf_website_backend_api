<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_name',
        'city',
        'state',
        'country',
        'contact_person',
        'contact_email',
        'contact_phone',
        'status',
        'm1_18',
        'm20_28',
        'm_snr',
        'l1_20',
        'l21_28',
        'l_snr',
        'edition',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }
}
