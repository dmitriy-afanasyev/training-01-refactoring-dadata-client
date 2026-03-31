<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\BankService;
use App\Geocoder\UI\Http\Requests\BankByBicRequest;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных банка по БИК.
 */
final readonly class BankByBicController
{
    public function __invoke(BankByBicRequest $request, BankService $bankService): JsonResponse
    {
        $bank = $bankService->findByBic($request->getBic());

        //TODO: Унификация ответов
        return response()->json([
            'success' => true,
            'data' => $bank->toArray(),
        ]);
    }
}
