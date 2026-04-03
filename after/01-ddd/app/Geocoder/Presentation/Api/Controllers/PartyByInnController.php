<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use App\Geocoder\Presentation\Api\Requests\PartyByInnRequest;
use App\Geocoder\Presentation\Api\Transformers\PartyTransformer;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных организации по ИНН.
 */
final class PartyByInnController
{
    public function __construct(
        private PartyService $partyService,
        private PartyTransformer $transformer,
    ) {}

    public function __invoke(PartyByInnRequest $request): JsonResponse
    {
        $party = $this->partyService->findByInn($request->getInn());

        return ApiResponseFactory::success($party, $this->transformer)->toResponse();
    }
}
