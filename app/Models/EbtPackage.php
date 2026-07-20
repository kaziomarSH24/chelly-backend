<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbtPackage extends Model
{
    protected $fillable = [
        'title',
        'price',
        'product_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
