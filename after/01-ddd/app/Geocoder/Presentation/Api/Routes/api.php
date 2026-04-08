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
Route::prefix('api/geocoder')
    ->middleware(['api', 'throttle:geocoder'])
    ->group(function () {
        // POST вместо GET: ИНН — персональные данные, не должны попадать
        // в логи сервера, CDN, proxy и историю браузера (query string логируется)
        Route::post('/party/by-inn', PartyByInnController::class);
        Route::post('/bank/by-bic', BankByBicController::class);
        Route::get('/address/search', AddressSearchController::class);
        Route::get('/country/search', CountrySearchController::class);
    });
