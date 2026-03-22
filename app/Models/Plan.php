<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'type', 'speed_up', 'speed_down',
        'burst_up', 'burst_down', 'fup_limit',
        'fup_speed_up', 'fup_speed_down',
        'validity_days', 'price', 'router_id', 'is_active',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function accounts()
    {
        return $this->hasMany(ClientAccount::class);
    }
}
