<?php

use App\Http\Controllers\Api\V1\Payment\CallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


