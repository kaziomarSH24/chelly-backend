<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use AutoClearsCache;
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
