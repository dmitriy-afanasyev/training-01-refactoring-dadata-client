<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\UI\Http\Requests\AddressSearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для поиска адресов.
 */
final readonly class AddressSearchController
{
    public function __invoke(AddressSearchRequest $request, AddressService $addressService): JsonResponse
    {
        $addresses = $addressService->search(
            $request->getQuery(),
            $request->getLocations()
        );

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }
}
