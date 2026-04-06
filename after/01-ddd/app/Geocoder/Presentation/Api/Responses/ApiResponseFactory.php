<?php

declare(strict_types=1);

namespace App\Geocoder\Presentation\Api\Responses;

use App\Geocoder\Presentation\Api\Transformers\Transformer;
use Illuminate\Http\JsonResponse;

final readonly class ApiResponseFactory
{
    private function __construct(
        private bool $success,
        private mixed $data = null,
        private ?string $error = null,
        private ?string $message = null,
        private array $context = [],
        private int $statusCode = 200,
    ) {}

    /**
     * @param mixed $data Данные (сущность, DTO, коллекция)
     * @param Transformer|null $transformer Трансформер для преобразования данных
     */
    public static function success(mixed $data, ?Transformer $transformer = null): self
    {
        if ($transformer !== null) {
            $data = $transformer->transform($data);
        }

        return new self(
            success: true,
            data: $data,
        );
    }

    public static function error(string $error, ?string $message = null, array $context = []): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            context: $context,
            statusCode: 400,
        );
    }

    public static function notFound(string $error, ?string $message = null, array $context = []): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            context: $context,
            statusCode: 404,
        );
    }

    /**
     * @param string $error Сообщение об ошибке
     * @param string|null $message Дополнительное сообщение
     * @param array<string, mixed> $context Контекст ошибки
     */
    public static function badGateway(string $error, ?string $message = null, array $context = []): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            context: $context,
            statusCode: 502,
        );
    }

    /**
     * @param string $error Сообщение об ошибке
     * @param string|null $message Дополнительное сообщение
     * @param array<string, mixed> $context Контекст ошибки
     */
    public static function internalError(string $error, ?string $message = null, array $context = []): self
    {
        return new self(
            success: false,
            error: $error,
            message: $message,
            context: $context,
            statusCode: 500,
        );
    }

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
            if (!empty($this->context)) {
                $data['context'] = $this->context;
            }
        }

        return $data;
    }
}
