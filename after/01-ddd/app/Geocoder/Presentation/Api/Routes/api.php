<?php

declare(strict_types=1);

use App\Geocoder\Presentation\Api\Controllers\AddressSearchController;
use App\Geocoder\Presentation\Api\Controllers\BankByBicController;
use App\Geocoder\Presentation\Api\Controllers\CountrySearchController;
use App\Geocoder\Presentation\Api\Controllers\PartyByInnController;
use Illuminate\Support\Facades\Route;

/**
 * Маршруты модуля Geocoder.
 *
 * Не используется REST-ресурсный стиль (apiResource), потому что модуль
 * реализует DDD CQRS-подход: это не CRUD-операции над ресурсами,
 * а query-команды (найти по ИНН, найти по БИК, поиск адреса).
 * Каждый маршрут соответствует конкретному Use Case, а не модели Eloquent.
 */
Route::prefix('api/geocoder')
    ->middleware(['api', 'throttle:geocoder'])
    ->group(function () {
        // POST вместо GET: ИНН — персональные данные, не должны попадать
        // в логи сервера, CDN, proxy и историю браузера (query string логируется)
        Route::post('/party/by-inn', PartyByInnController::class)->name('geocoder.party.by-inn');
        Route::post('/bank/by-bic', BankByBicController::class)->name('geocoder.bank.by-bic');
        Route::get('/address/search', AddressSearchController::class)->name('geocoder.address.search');
        Route::get('/country/search', CountrySearchController::class)->name('geocoder.country.search');
    });
