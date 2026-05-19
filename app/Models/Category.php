<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, AutoClearsCache;
    protected $guarded = ['id'];


    /**
     * Get the foods for the category.
     */
    public function foods(): HasMany
    {
        return $this->hasMany(Food::class);
    }
}
