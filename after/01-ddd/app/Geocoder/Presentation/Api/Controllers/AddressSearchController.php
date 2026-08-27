<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use App\Geocoder\Presentation\Api\Requests\AddressSearchRequest;
use App\Geocoder\Presentation\Api\Transformers\AddressTransformer;
use Illuminate\Http\JsonResponse;

final class AddressSearchController
{
    public function __construct(
        private AddressService $addressService,
        private AddressTransformer $transformer,
    ) {}

    public function __invoke(AddressSearchRequest $request): JsonResponse
    {
        $addresses = $this->addressService->searchAddress(
            $request->getQuery(),
            $request->getLocations()
        );

        return ApiResponseFactory::success($addresses, $this->transformer)->toResponse();
    }
}
