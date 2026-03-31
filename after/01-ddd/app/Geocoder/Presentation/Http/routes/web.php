<?php

declare(strict_types=1);

use App\Geocoder\Presentation\Http\Controllers\AddressSearchController;
use App\Geocoder\Presentation\Http\Controllers\BankByBicController;
use App\Geocoder\Presentation\Http\Controllers\CountrySearchController;
use App\Geocoder\Presentation\Http\Controllers\PartyByInnController;
use Illuminate\Support\Facades\Route;

/**
 * Маршруты модуля Geocoder.
 */
Route::prefix('api/dadata')->group(function () {
    Route::get('/party/by-inn', PartyByInnController::class);
    Route::get('/bank/by-bic', BankByBicController::class);
    Route::get('/address/search', AddressSearchController::class);
    Route::get('/country/search', CountrySearchController::class);
});
