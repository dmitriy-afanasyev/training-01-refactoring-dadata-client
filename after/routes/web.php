<?php

use App\Geocoder\UI\Http\Controllers\DadataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// DaData API Routes
Route::prefix('api/dadata')->group(function () {
    Route::get('/party/by-inn', [DadataController::class, 'getByInn']);
    Route::get('/bank/by-bic', [DadataController::class, 'getBankByBic']);
    Route::get('/address/search', [DadataController::class, 'searchAddress']);
    Route::get('/country/search', [DadataController::class, 'searchCountry']);
});
