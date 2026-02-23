<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiResponse
{
    
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200
    ): JsonResponse {
        $payload = ['success' => true];
        if ($message !== null) {
            $payload['message'] = $message;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    public static function error(
        string $message = 'Error en la petición',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $payload['errors'] = $errors instanceof MessageBag
                ? $errors->toArray()
                : $errors;
        }

        return response()->json($payload, $status);
    }

    public static function validationError(
        MessageBag|array $errors,
        string $message = 'Error de validación.'
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors' => $errors instanceof MessageBag ? $errors->toArray() : $errors,
        ];

        return response()->json($payload, 422);
    }

    public static function created(mixed $data, ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? 'Recurso creado correctamente.', 201);
    }

    public static function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function serverError(string $message = 'Error interno del servidor.'): JsonResponse
    {
        return self::error($message, 500);
    }

    public static function handleException(Throwable $e, string $context): JsonResponse
    {
        if ($e instanceof ValidationException) {
            throw $e;
        }

        Log::error("{$context}: {$e->getMessage()}", [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $message = config('app.debug')
            ? $e->getMessage()
            : 'Error interno del servidor. Inténtelo de nuevo más tarde.';

        return self::serverError($message);
    }
}
