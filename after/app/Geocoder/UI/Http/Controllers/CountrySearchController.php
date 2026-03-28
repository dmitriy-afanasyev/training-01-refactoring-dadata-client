<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для поиска стран.
 */
final readonly class CountrySearchController
{
    public function __construct(
        private AddressService $addressService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
        ]);

        $query = $request->input('query');

        try {
            $countries = $this->addressService->searchCountry($query);

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
