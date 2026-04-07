<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'client_id',
        'invoice_id',
        'payment_id',
        'entry_type',
        'amount',
        'currency',
        'description',
        'meta',
        'recorded_by',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
