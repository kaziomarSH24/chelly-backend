<?php

use App\Http\Controllers\Api\V1\Payment\CallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/variant-demo', function () {
    return view('variant-demo');
});

Route::get('/admin-variant-form', function () {
    return view('admin-variant-form');
});


