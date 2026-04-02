<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Presentation\Api\DTO\ApiResponse;
use App\Geocoder\Presentation\Api\Requests\CountrySearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для поиска стран.
 */
final class CountrySearchController
{
    public function __construct(private AddressService $addressService) {}

    public function __invoke(CountrySearchRequest $request): JsonResponse
    {
        $countries = $this->addressService->searchCountry($request->getQuery());

        return ApiResponse::success($countries)->toResponse();
    }
}
