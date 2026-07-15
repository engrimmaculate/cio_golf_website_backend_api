<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'user_id',
        'reference',
        'paystack_reference',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_channel',
        'metadata',
        'paystack_response',
        'description',
        'token',
        'token_expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'paystack_response' => 'array',
            'token_expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateReference(): string
    {
        return 'CIO-' . strtoupper(Str::random(8)) . '-' . time();
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function isTokenValid(): bool
    {
        return $this->token && $this->token_expires_at && $this->token_expires_at->isFuture();
    }

    public function generateReceiptNumber(): string
    {
        return 'CIO-RCPT-' . str_pad($this->id, 6, '0', STR_PAD_LEFT) . '-' . date('Y');
    }
}
