<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Food extends Model
{
    use HasFactory, AutoClearsCache;
    protected $guarded = ['id'];
    protected $table = 'foods';

     /**
     * Get the category that owns the food.
     */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
