<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\UI\Http\Requests\CountrySearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для поиска стран.
 */
final readonly class CountrySearchController
{
    public function __construct(
        private AddressService $addressService,
    ) {
    }

    public function __invoke(CountrySearchRequest $request): JsonResponse
    {
        try {
            $countries = $this->addressService->searchCountry($request->getQuery());

            return response()->json([
                'success' => true,
                'data' => $countries,
            ]);
        } catch (ExternalApiException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка внешнего API',
                'message' => $e->getMessage(),
            ], 502);
        } catch (GeocoderException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка модуля Geocoder',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
