<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\UI\Http\DTO\ApiResponse;
use App\Geocoder\UI\Http\Requests\AddressSearchRequest;
use Illuminate\Http\JsonResponse;

//TODO: Общую папку Http переименовать в Api?

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

        return ApiResponse::success($addresses)->toResponse();
    }
}
