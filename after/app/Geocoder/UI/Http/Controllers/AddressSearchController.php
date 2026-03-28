<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для поиска адресов.
 */
final readonly class AddressSearchController
{
    public function __construct(
        private AddressService $addressService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'locations' => 'nullable|array',
        ]);

        $query = $request->input('query');
        $locations = $request->input('locations');

        try {
            $addresses = $this->addressService->search($query, $locations);

            return response()->json([
                'success' => true,
                'data' => $addresses,
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
