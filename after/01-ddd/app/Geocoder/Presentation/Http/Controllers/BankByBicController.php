<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Http\Controllers;

use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Presentation\Http\DTO\ApiResponse;
use App\Geocoder\Presentation\Http\Requests\BankByBicRequest;
use App\Geocoder\Presentation\Http\Transformers\BankTransformer;
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
