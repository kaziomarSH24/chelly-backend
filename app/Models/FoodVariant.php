<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodVariant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    /**
     * Get the food that owns the variant.
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
