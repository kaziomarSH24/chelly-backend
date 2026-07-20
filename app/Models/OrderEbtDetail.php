<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEbtDetail extends Model
{
    protected $fillable = ['order_id', 'card_number', 'pin', 'meal_plan'];

    protected $casts = [
        'card_number' => 'encrypted',
        'pin' => 'encrypted',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
