<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Http\DTO;

use Illuminate\Http\JsonResponse;

/**
 * DTO для унифицированного ответа API.
 */
final readonly class ApiResponse
{
    private function __construct(
        private bool $success,
        private mixed $data = null,
        private ?string $error = null,
        private ?string $message = null,
        private int $statusCode = 200,
    ) {}

    /**
     * Успешный ответ с данными.
     *
     * @param mixed $data Данные (сущность, DTO, коллекция)
     * @param class-string|null $transformer Класс трансформера для преобразования данных
     */
    public static function success(mixed $data, ?string $transformer = null): self
    {
        if ($transformer !== null) {
            $data = $transformer::transform($data);
        }

        return new self(
            success: true,
            data: $data,
        );
    }

    /**
     * Ответ с ошибкой (по умолчанию 400).
     */
    public static function error(string $error, ?string $message = null): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            statusCode: 400,
        );
    }

    /**
     * Ответ с ошибкой 404.
     */
    public static function notFound(string $error, ?string $message = null): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            statusCode: 404,
        );
    }

    /**
     * Ответ с ошибкой 502 (ошибка внешнего API).
     */
    public static function badGateway(string $error, ?string $message = null): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            statusCode: 502,
        );
    }

    /**
     * Ответ с ошибкой 500 (внутренняя ошибка).
     */
    public static function internalError(string $error, ?string $message = null): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            statusCode: 500,
        );
    }

    /**
     * Преобразовать в JSON-ответ.
     */
    public function toResponse(): JsonResponse
    {
        return response()->json(
            $this->toArray(),
            $this->statusCode,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Преобразовать в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'success' => $this->success,
        ];

        if ($this->success) {
            $data['data'] = $this->data;
        } else {
            $data['error'] = $this->error;
            if ($this->message !== null) {
                $data['message'] = $this->message;
            }
        }

        return $data;
    }
}
