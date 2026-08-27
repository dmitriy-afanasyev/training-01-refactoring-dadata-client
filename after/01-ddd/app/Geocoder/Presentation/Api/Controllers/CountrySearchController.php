<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use App\Geocoder\Presentation\Api\Requests\CountrySearchRequest;
use App\Geocoder\Presentation\Api\Transformers\CountryTransformer;
use Illuminate\Http\JsonResponse;

final class CountrySearchController
{
    public function __construct(
        private AddressService $addressService,
        private CountryTransformer $transformer,
    ) {}

    public function __invoke(CountrySearchRequest $request): JsonResponse
    {
        $countries = $this->addressService->searchCountry($request->getQuery());

        return ApiResponseFactory::success($countries, $this->transformer)->toResponse();
    }
}
