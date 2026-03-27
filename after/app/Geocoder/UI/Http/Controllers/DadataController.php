<?php

declare(strict_types=1);

namespace App\Geocoder\UI\Http\Controllers;

use App\Geocoder\Application\Services\AddressService;
use App\Geocoder\Application\Services\BankService;
use App\Geocoder\Application\Services\PartyService;
use App\Geocoder\Domain\Exceptions\ExternalApiException;
use App\Geocoder\Domain\Exceptions\GeocoderException;
use App\Geocoder\Domain\Exceptions\InvalidBicException;
use App\Geocoder\Domain\Exceptions\InvalidInnException;
use App\Geocoder\Domain\Exceptions\PartyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер для работы с DaData API.
 */
class DadataController
{
    public function __construct(
        private PartyService $partyService,
        private BankService $bankService,
        private AddressService $addressService,
    ) {
    }

    /**
     * Получить данные организации по ИНН.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getByInn(Request $request): JsonResponse
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

    /**
     * Получить данные банка по БИК.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBankByBic(Request $request): JsonResponse
    {
        $request->validate([
            'bic' => 'required|string|size:9',
        ]);

        $bic = $request->input('bic');

        try {
            $bank = $this->bankService->findByBic($bic);

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

    /**
     * Поиск адресов.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAddress(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'locations' => 'nullable|array',
        ]);

        $query = $request->input('query');
        $locations = $request->input('locations');

        try {
            $addresses = $this->addressService->search($query, $locations);

            return response()->json([
                'success' => true,
                'data' => $addresses,
            ]);
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

    /**
     * Поиск стран.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchCountry(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
        ]);

        $query = $request->input('query');

        try {
            $countries = $this->addressService->searchCountry($query);

            return response()->json([
                'success' => true,
                'data' => $countries,
            ]);
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
