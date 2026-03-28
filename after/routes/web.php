<?php

use App\Geocoder\UI\Http\Controllers\AddressSearchController;
use App\Geocoder\UI\Http\Controllers\BankByBicController;
use App\Geocoder\UI\Http\Controllers\CountrySearchController;
use App\Geocoder\UI\Http\Controllers\PartyByInnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// DaData API Routes
Route::prefix('api/dadata')->group(function () {
    Route::get('/party/by-inn', PartyByInnController::class);
    Route::get('/bank/by-bic', BankByBicController::class);
    Route::get('/address/search', AddressSearchController::class);
    Route::get('/country/search', CountrySearchController::class);
});
