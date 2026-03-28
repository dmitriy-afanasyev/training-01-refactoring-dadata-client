<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Маршруты модуля Geocoder
Route::prefix('api/dadata')->group(function () {
    require base_path('app/Geocoder/UI/Http/routes/web.php');
});
