<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FoodImage extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
    
    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (!$value) return null;
                
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }
                
                return url(Storage::url($value));
            }
        );
    }
}
