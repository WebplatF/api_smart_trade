<?php

namespace App\Http\Middleware;

use App\Helper\ResponseHelper;
use Closure;
use Illuminate\Http\Request;

class ApikeyAuth
{
    /**
     * @param Illuminate\Http\Request $request
     * @param \Closure $next
     * @return Illuminate\Http\Request|\Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $validKey = config('AppConfig.api_key');
        if (!$request->header('Apikey')) {
            return ResponseHelper::failureResponse(message: "Missing apikey", code: 400);
        }
        $apiKey = $request->header('Apikey');
        if ($apiKey !== $validKey) {
            return ResponseHelper::failureResponse(message: "Invalid apikey", code: 400);
        }
        return $next($request);
    }
}
