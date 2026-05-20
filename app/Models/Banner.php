<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use AutoClearsCache;
    protected $guarded = ['id'];
}
