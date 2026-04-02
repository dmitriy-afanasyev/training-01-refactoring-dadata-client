<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Presentation\Api\DTO\ApiResponse;
use App\Geocoder\Presentation\Api\Requests\AddressSearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для поиска адресов.
 */
final class AddressSearchController
{
    public function __construct(private AddressService $addressService) {}

    public function __invoke(AddressSearchRequest $request): JsonResponse
    {
        $addresses = $this->addressService->search(
            $request->getQuery(),
            $request->getLocations()
        );

        return ApiResponse::success($addresses)->toResponse();
    }
}
