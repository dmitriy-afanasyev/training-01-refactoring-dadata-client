<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\UI\Http\DTO\ApiResponse;
use App\Geocoder\UI\Http\Requests\PartyByInnRequest;
use App\Geocoder\UI\Http\Transformers\PartyTransformer;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных организации по ИНН.
 */
final readonly class PartyByInnController
{
    public function __invoke(PartyByInnRequest $request, PartyService $partyService): JsonResponse
    {
        $party = $partyService->findByInn($request->getInn());

        return ApiResponse::success($party, PartyTransformer::class)->toResponse();
    }
}
