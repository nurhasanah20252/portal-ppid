<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvalidStatusTransitionException extends Exception
{
    /**
     * Buat instance exception baru untuk transisi status yang tidak valid.
     */
    public function __construct(string $message = 'Transisi status tidak valid')
    {
        parent::__construct($message);
    }

    /**
     * Render exception sebagai HTTP response JSON.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
            'errors' => (object) [],
        ], 422);
    }
}
