<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function localizedName(?User $user = null): string
    {
        $locale = $user->language ?? 'fa';
        $field = "name_{$locale}";
        return $this->{$field} ?? $this->name_fa ?? '';
    }
}
