<?php

namespace App\Http\Controllers\Trade;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\RequestModel\TradeEntryCreateModel;
use App\RequestModel\TradeEntryEditModel;
use App\Services\TradeEntryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class TradeController extends Controller
{
    protected TradeEntryService $tradeEntryService;

    public function __construct(TradeEntryService $tradeEntryService)
    {
        $this->tradeEntryService = $tradeEntryService;
    }
    /**
     * Trade Entry Creation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $userId = (int)$request->get('user_id');
            $tradeEntryCreate = TradeEntryCreateModel::fromRequest(request: $request);
            $create = $this->tradeEntryService->create(tradeEntryCreateModel: $tradeEntryCreate, userId: $userId);
            return ResponseHelper::successResponse(data: $create, message: "Trade entry created successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * Trade Entry Edit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request)
    {
        try {
            $userId = (int)$request->get('user_id');
            $tradeEntryEdit = TradeEntryEditModel::fromRequest(request: $request);
            $edit = $this->tradeEntryService->edit(tradeEntryEditModel: $tradeEntryEdit, userId: $userId);
            return ResponseHelper::successResponse(data: $edit, message: "Trade entry edited successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * Trade Entry Edit
     *
     * @param int $id
     * @return JsonResponse
     */
    public function list(int $id)
    {
        try {
            $walletId = (int)$id;
            $tradeEntry = $this->tradeEntryService->list(walletId: $walletId);
            return ResponseHelper::successResponse(data: $tradeEntry->toArray(), message: "Trade entry list arrived successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
