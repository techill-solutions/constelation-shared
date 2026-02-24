<?php

declare(strict_types=1);

namespace Constelation\Shared\Support;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $status = 200
    ): array {
        $response = [
            'data' => $data,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        $response['status'] = $status;

        return $response;
    }

    public static function error(
        string $message,
        array $errors = [],
        int $status = 422
    ): array {
        $response = [
            'message' => $message,
            'status' => $status,
        ];

        if ($errors !== []) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}
