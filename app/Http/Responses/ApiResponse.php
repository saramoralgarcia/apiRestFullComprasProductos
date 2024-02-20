<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success($mensaje = "Success", $statusCode = 200, $data=[])
    {
        return response()-> json
        ([
            "mensaje" => $mensaje,
            'statusCode' => $statusCode,
            'error' => false,
            'data' => $data
        ], $statusCode);
    }

    public static function error($mensaje = "Error", $statusCode, $data = [])
    {
        return response()-> json([
            'message' => $mensaje,
            'statusCode' => $statusCode,
            'error' => false,
            'data' => $data
        ],$statusCode);
    }
}