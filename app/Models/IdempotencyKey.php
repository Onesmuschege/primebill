<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $fillable = [
        'scope',
        'idempotency_key',
        'status',
        'response_payload',
    ];

    protected $casts = [
        'response_payload' => 'array',
    ];
}
