<?php

namespace App\Http\Controllers\Wallet;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(
        WalletService $walletService
    ) {
        $this->walletService = $walletService;
    }
    /**
     * Get Wallet
     *
     * @param Request $request
     * @return  JsonResponse
     */
    public function getWallet(Request $request)
    {
        try {
            $userId = (int)$request->get('user_id');
            $wallet = $this->walletService->getWallet(userId: $userId);
            return ResponseHelper::successResponse(data: $wallet, message: "user wallet arrived successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Get Payment Logs
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function getPaymentLogs(int $id)
    {
        try {
            $walletId = (int)$id;
            $logs = $this->walletService->getPaymentLogs(walletId: $walletId);
            return ResponseHelper::successResponse(data: $logs->toArray(), message: "Payment history arrived successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Wallet Creation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function walleteCreation(Request $request)
    {
        try {
            $userId = (int)$request->get('user_id');
            $Validator = Validator::make($request->all(), [
                'date' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $date = $request->get('date');
            $amount = $request->get('amount', 0);
            $wallet = $this->walletService->walleteCreation(userId: $userId, date: $date, amount: $amount);
            return ResponseHelper::successResponse(data: $wallet, message: "user wallet created successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Wallet Creation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function walleteAction(Request $request)
    {
        try {
            $userId = (int)$request->get('user_id');
            $Validator = Validator::make($request->all(), [
                'date' => 'required|strict_string',
                'action' => 'required|strict_string',
                'amount' => 'required|strict_number',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $date = $request->get('date');
            $action = $request->get('action');
            $amount = (float)$request->get('amount', 0.0);
            $walletReturn = $this->walletService->walleteAction(userId: $userId, action: $action, amount: $amount,date:$date);
            return ResponseHelper::successResponse(data: $walletReturn, message: "user wallet created successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
}
