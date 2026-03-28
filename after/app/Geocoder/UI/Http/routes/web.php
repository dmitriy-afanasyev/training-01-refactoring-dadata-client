<?php

declare(strict_types=1);

use App\Geocoder\UI\Http\Controllers\AddressSearchController;
use App\Geocoder\UI\Http\Controllers\BankByBicController;
use App\Geocoder\UI\Http\Controllers\CountrySearchController;
use App\Geocoder\UI\Http\Controllers\PartyByInnController;
use Illuminate\Support\Facades\Route;

/**
 * Маршруты модуля Geocoder.
 */
Route::get('/party/by-inn', PartyByInnController::class);
Route::get('/bank/by-bic', BankByBicController::class);
Route::get('/address/search', AddressSearchController::class);
Route::get('/country/search', CountrySearchController::class);
