<?php

declare(strict_types=1);

use App\Geocoder\Presentation\Api\Controllers\AddressSearchController;
use App\Geocoder\Presentation\Api\Controllers\BankByBicController;
use App\Geocoder\Presentation\Api\Controllers\CountrySearchController;
use App\Geocoder\Presentation\Api\Controllers\PartyByInnController;
use Illuminate\Support\Facades\Route;

/**
 * Маршруты модуля Geocoder.
 */
Route::prefix('api/geocoder')->middleware('throttle:100,1')->group(function () {
    Route::get('/party/by-inn', PartyByInnController::class);
    Route::get('/bank/by-bic', BankByBicController::class);
    Route::get('/address/search', AddressSearchController::class);
    Route::get('/country/search', CountrySearchController::class);
});
