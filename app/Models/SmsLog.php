<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'client_id', 'phone', 'message',
        'status', 'gateway_response', 'gateway',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
