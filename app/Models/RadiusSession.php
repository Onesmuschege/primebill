<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusSession extends Model
{
    protected $fillable = [
        'username', 'client_account_id', 'ip_address',
        'bytes_in', 'bytes_out', 'session_start',
        'session_stop', 'status',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_stop'  => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ClientAccount::class, 'client_account_id');
    }
}
