<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(int $statusCode, $data = null, string $message = 'Success'): JsonResponse
    {
        return response()->json(['statusCode' => $statusCode, 'message' => $message, 'data' => $data], $statusCode);
    }

    protected function errorResponse(int $statusCode, string $message = 'Error', $errors = null): JsonResponse
    {
        return response()->json(['statusCode' => $statusCode, 'message' => $message, 'errors' => $errors], $statusCode);
    }

    protected function badRequestResponse(int $statusCode = 400, string $message = 'Bad Request', $errors = null): JsonResponse
    {
        return $this->errorResponse($statusCode, $message, $errors);
    }

    protected function internalErrorResponse(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->errorResponse(500, $message, null);
    }

    protected function notFoundResponse(string $message = 'Not Found'): JsonResponse
    {
        return $this->errorResponse(404, $message, null);
    }
}