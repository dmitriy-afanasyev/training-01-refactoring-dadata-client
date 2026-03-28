<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\UI\Http\Requests\BankByBicRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных банка по БИК.
 */
final readonly class BankByBicController
{
    public function __construct(
        private BankService $bankService,
    ) {
    }

    public function __invoke(BankByBicRequest $request): JsonResponse
    {
        try {
            $bank = $this->bankService->findByBic($request->getBic());

            if ($bank === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Банк не найден',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $bank->toArray(),
            ]);
        } catch (InvalidBicException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Неверный формат БИК',
                'message' => $e->getMessage(),
            ], 400);
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
