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
