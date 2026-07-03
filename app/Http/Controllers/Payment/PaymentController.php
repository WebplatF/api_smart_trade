<?php

namespace App\Http\Controllers\Payment;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaymentController extends Controller{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function orderUnlock(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $Validator = Validator::make($request->all(), [
                'amount' => 'required|strict_string',
                'tag' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $amount = (int)$request->get('amount');
            $tag = $request->get('tag');
            $response = $this->paymentService->orderCreate(amount: $amount, userId: $userId, tag: $tag);
            return ResponseHelper::successResponse(data: $response->toArray(), message: "Order is created", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }

    public function paymentCapture(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $Validator = Validator::make($request->all(), [
                'amount' => 'required|strict_string',
                'tag' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $amount = $request->get('amount');
            $tag = $request->get('tag');
            $response = $this->paymentService->orderCreate(amount: $amount, userId: $userId, tag: $tag);
            return ResponseHelper::successResponse(data: $response->toArray(), message: "Order is created", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
}