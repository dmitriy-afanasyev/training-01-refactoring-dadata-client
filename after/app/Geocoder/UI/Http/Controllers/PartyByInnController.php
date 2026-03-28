<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для получения данных организации по ИНН.
 */
final readonly class PartyByInnController
{
    public function __construct(
        private PartyService $partyService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'inn' => 'required|string|size:10',
        ]);

        $inn = $request->input('inn');

        try {
            $party = $this->partyService->findByInn($inn);

            return response()->json([
                'success' => true,
                'data' => $party->toArray(),
            ]);
        } catch (InvalidInnException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Неверный формат ИНН',
                'message' => $e->getMessage(),
            ], 400);
        } catch (PartyNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Организация не найдена',
                'message' => $e->getMessage(),
            ], 404);
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
