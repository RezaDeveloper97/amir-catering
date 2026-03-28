<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function localizedName(?User $user = null): string
    {
        $locale = $user->language ?? 'fa';
        $field = "name_{$locale}";
        return $this->{$field} ?? $this->name_fa ?? '';
    }
}
