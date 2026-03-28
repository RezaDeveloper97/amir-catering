<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function localizedItemName(?User $user = null): string
    {
        $locale = $user->language ?? 'fa';
        $field = "item_name_{$locale}";
        return $this->{$field} ?? $this->item_name_fa ?? '';
    }

    public function localizedCategory(?User $user = null): string
    {
        $locale = $user->language ?? 'fa';
        $field = "category_{$locale}";
        return $this->{$field} ?? $this->category_fa ?? '';
    }
}
