<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Presentation\Http\DTO\ApiResponse;
use App\Geocoder\Presentation\Http\Requests\CountrySearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для поиска стран.
 */
final readonly class CountrySearchController
{
    public function __invoke(CountrySearchRequest $request, AddressService $addressService): JsonResponse
    {
        $countries = $addressService->searchCountry($request->getQuery());

        return ApiResponse::success($countries)->toResponse();
    }
}
