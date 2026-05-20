<?php

namespace App\Models;

use App\Traits\AutoClearsCache;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use AutoClearsCache;
    protected $guarded = ['id'];
}
