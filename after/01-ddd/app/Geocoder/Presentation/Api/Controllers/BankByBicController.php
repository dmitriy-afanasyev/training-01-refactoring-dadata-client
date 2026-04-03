<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Controllers;

use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Presentation\Api\Responses\ApiResponseFactory;
use App\Geocoder\Presentation\Api\Requests\BankByBicRequest;
use App\Geocoder\Presentation\Api\Transformers\BankTransformer;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для получения данных банка по БИК.
 */
final class BankByBicController
{
    public function __construct(
        private BankService $bankService,
        private BankTransformer $transformer,
    ) {}

    public function __invoke(BankByBicRequest $request): JsonResponse
    {
        $bank = $this->bankService->findByBic($request->getBic());

        return ApiResponseFactory::success($bank, $this->transformer)->toResponse();
    }
}
