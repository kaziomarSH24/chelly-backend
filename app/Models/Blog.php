<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use AutoClearsCache;
    protected $guarded = ['id'];

    // Category Relationship
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
