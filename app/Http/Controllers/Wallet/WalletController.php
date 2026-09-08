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
            $walletReturn = $this->walletService->walleteAction(userId: $userId, action: $action, amount: $amount, date: $date);
            return ResponseHelper::successResponse(message: "user wallet created successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Wallet Summary 
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function getSummary(Request $request, string $id)
    {
        try {
            $returnData = $this->walletService->getWalletSummary(walletId: (int)$id);
            return ResponseHelper::successResponse(data: $returnData, message: "user wallet summary arrived successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Wallet Monthly Summary 
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMonthSummay(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'wallet_id' => 'required|strict_int',
                'month' => 'required|strict_string',
                'year' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $walletId = $request->get('wallet_id');
            $month = $request->get('month');
            $year = $request->get('year');
            $returnData = $this->walletService->getCalenderData(walletId: (int)$walletId, month: $month, year: $year);
            return ResponseHelper::successResponse(data: $returnData, message: "user calender summary arrived successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * Wallet Balance Summary 
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlanceSummay(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'wallet_id' => 'required|strict_int',
                'year' => 'required|strict_string',
                'tag' => 'required|strict_string',
                'month'     => 'required_if:tag,weekly|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $tag = $request->get('tag');
            $walletId = $request->get('wallet_id');
            $month = $request->get('month');
            $year = $request->get('year');
            $returnData = $this->walletService->getChartSummary(walletId: (int)$walletId, month: $month, year: $year, tag: $tag);
            return ResponseHelper::successResponse(data: $returnData, message: "user calender summary arrived successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
}
