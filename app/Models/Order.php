<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    use AutoClearsCache;
    public function getRelatedCacheTags(): array
    {
        return ['order_items', 'order_deliveries'];
    }

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items() : HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OrderDelivery::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    // Morph Relation with Transactions table
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }
}
