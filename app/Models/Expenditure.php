<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenditure extends Model
{
    protected $fillable = [
        'category', 'description',
        'amount', 'date', 'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
