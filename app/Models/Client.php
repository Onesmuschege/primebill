<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'id_number', 'address', 'county', 'town',
        'gps_lat', 'gps_lng', 'status', 'created_by',
    ];

    public function accounts()
    {
        return $this->hasMany(ClientAccount::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}