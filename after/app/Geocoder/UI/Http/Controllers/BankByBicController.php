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
    public function __construct(
        private BankService $bankService,
    ) {
    }

    public function __invoke(BankByBicRequest $request): JsonResponse
    {
        $bank = $this->bankService->findByBic($request->getBic());

        return response()->json([
            'success' => true,
            'data' => $bank->toArray(),
        ]);
    }
}
