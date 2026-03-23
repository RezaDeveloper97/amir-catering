<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_registered' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
