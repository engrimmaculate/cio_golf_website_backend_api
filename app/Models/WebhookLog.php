<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'reference',
        'payment_id',
        'payload',
        'status',
        'message',
        'ip_address',
        'user_agent',
        'is_sample',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_sample' => 'boolean',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
