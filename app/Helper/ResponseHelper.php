<?php

namespace App\Helper;



class ResponseHelper
{
    /** 
     * @param  array $data
     * @param String $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponse($data = [], String $message = "Data arrived", int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /** 
     * @param String $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function failureResponse(String $message = "Data arrived", int $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}
