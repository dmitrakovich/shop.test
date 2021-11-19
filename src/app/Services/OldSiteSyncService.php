<?php

namespace App\Services;

class OldSiteSyncService
{
    const JSON_OPTIONS = JSON_UNESCAPED_UNICODE;

    /**
     * Make success response
     *
     * @param mixed $data
     * @param integer $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $code = 200)
    {
        return self::jsonResponse([
            'success' => true,
            'data' => $data
        ], $code);
    }

    /**
     * Make error json response
     *
     * @param mixed $errorMessages
     * @param integer $code
     * @return \Illuminate\Http\JsonResponse
     */
    static public function errorResponse($errorMessages, int $code = 422)
    {
        if (!is_array($errorMessages)) {
            $errorMessages = [$errorMessages];
        }
        return self::jsonResponse([
            'success' => false,
            'errors' => $errorMessages
        ], $code);
    }

    /**
     * Make json response
     *
     * @param array $data
     * @param integer $code
     * @return \Illuminate\Http\JsonResponse
     */
    static protected function jsonResponse(array $data, int $code = 200)
    {
        return response()->json($data, $code, [], self::JSON_OPTIONS);
    }
}
