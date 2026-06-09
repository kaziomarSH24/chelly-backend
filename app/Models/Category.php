<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    protected function image(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                // Return null if there is no image in the database
                if (!$value) {
                    return null;
                }

                // If the image is an external URL, return it directly
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }

                // Otherwise, generate the full URL for local storage
                return url(Storage::url($value));
            }
        );
    }
}
