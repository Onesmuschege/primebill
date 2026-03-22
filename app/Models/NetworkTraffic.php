<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkTraffic extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'router_id', 'tx_bytes',
        'rx_bytes', 'interface', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
