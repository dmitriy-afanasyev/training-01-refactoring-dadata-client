<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\BankService;
use App\Geocoder\UI\Http\DTO\ApiResponse;
use App\Geocoder\UI\Http\Requests\BankByBicRequest;
use App\Geocoder\UI\Http\Transformers\BankTransformer;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных банка по БИК.
 */
final readonly class BankByBicController
{
    public function __invoke(BankByBicRequest $request, BankService $bankService): JsonResponse
    {
        $bank = $bankService->findByBic($request->getBic());

        return ApiResponse::success($bank, BankTransformer::class)->toResponse();
    }
}
