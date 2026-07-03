<?php

namespace App\Http\Middleware;

use App\Helper\ResponseHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;

class PaymentAuth
{
    /**
     * @param Illuminate\Http\Request $request
     * @param \Closure $next
     * @return Illuminate\Http\Request|\Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = $request->getContent();
            $razorpaySignature = $request->header('X-Razorpay-Signature');
            $secretkey = config('AppConfig.payment_secret');
            $generatedSignature = hash_hmac(
                'sha256',
                $payload,
                $secretkey
            );
            if (!hash_equals($generatedSignature, $razorpaySignature)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Webhook Signature'
                ], 401);
            }
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: "Unauthorized token", code: 401);
        }
        return $next($request);
    }
}
