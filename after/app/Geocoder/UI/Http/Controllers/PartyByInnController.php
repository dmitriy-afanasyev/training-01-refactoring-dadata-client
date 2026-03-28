<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\UI\Http\Requests\PartyByInnRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных организации по ИНН.
 */
final readonly class PartyByInnController
{
    public function __construct(
        private PartyService $partyService,
    ) {
    }

    public function __invoke(PartyByInnRequest $request): JsonResponse
    {
        $party = $this->partyService->findByInn($request->getInn());

        return response()->json([
            'success' => true,
            'data' => $party->toArray(),
        ]);
    }
}
