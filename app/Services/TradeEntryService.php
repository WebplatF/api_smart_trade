<?php

namespace App\Services;

use App\Models\TradeEntry;
use App\Models\Wallet;
use App\RequestModel\TradeEntryCreateModel;
use App\RequestModel\TradeEntryEditModel;
use App\Resources\TradeEntryResources;
use App\ResponseModel\CommonListResponseModel;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TradeEntryService
{
    protected  WalletService $walletService;
    public function __construct(
        WalletService $walletService
    ) {
        $this->walletService = $walletService;
    }
    /**
     * Trade Entry Creation
     *
     * @param TradeEntryCreateModel $tradeEntryCreateModel
     * @param int $userId
     * @return object
     */
    public function create(TradeEntryCreateModel $tradeEntryCreateModel, int $userId): object
    {
        try {
            return DB::transaction(function () use ($tradeEntryCreateModel, $userId) {
                $wallet = Wallet::where('is_delete', 0)->where('id', $tradeEntryCreateModel->walletId)->first();
                if (!$wallet) {
                    throw new Exception("User wallet not found");
                }
                $tradeAmt = $tradeEntryCreateModel->winLoss === 'WIN'
                    ? $tradeEntryCreateModel->profit
                    : $tradeEntryCreateModel->loss;
                $actualBal =   $tradeEntryCreateModel->winLoss === 'WIN'
                    ?  bcadd($wallet->amount, $tradeAmt, 2) : bcsub($wallet->amount, $tradeAmt, 2);
                if (bccomp($actualBal, '0.00', 2) < 0) {
                    throw new Exception("Insufficient wallet balance.");
                }
                $tradeCreate = TradeEntry::create([
                    'wallet_id' => $tradeEntryCreateModel->walletId,
                    'date' => $tradeEntryCreateModel->date,
                    'pair' => $tradeEntryCreateModel->pair,
                    'lot_size' => $tradeEntryCreateModel->lotSize,
                    'direction' => $tradeEntryCreateModel->direction,
                    'entry_price' => $tradeEntryCreateModel->entryPrice,
                    'stop_loss' => $tradeEntryCreateModel->stopLoss,
                    'take_profit' => $tradeEntryCreateModel->takeProfit,
                    'exit_price' => $tradeEntryCreateModel->exitPrice,
                    'points_captured' => $tradeEntryCreateModel->pointsCaptured,
                    'win_loss' => $tradeEntryCreateModel->winLoss,
                    'risk_reward' => $tradeEntryCreateModel->riskReward,
                    'reason' => $tradeEntryCreateModel->reason,
                    'profit' => $tradeEntryCreateModel->profit,
                    'loss' => $tradeEntryCreateModel->loss,
                    'remark' => $tradeEntryCreateModel->remark,
                ]);
                $this->walletService->walleteAction(userId: $userId, action: $tradeEntryCreateModel->winLoss === 'WIN'
                    ? "deposite" : "withdraw", amount: $tradeAmt, isLog: false, date: $tradeEntryCreateModel->date);
                $this->walletService->PaymentLogsActions(
                    amount: $tradeAmt,
                    balance: $actualBal,
                    walletId: $tradeEntryCreateModel->walletId,
                    action: "TRADE ENTRY",
                    tradeId: $tradeCreate->id,
                    direction: $tradeEntryCreateModel->winLoss === 'WIN'
                        ? "Inward" : "Outward",
                    description: "Amount added of trade",
                    createdDate: $tradeEntryCreateModel->date
                );
                return (object)[
                    'id' => $tradeCreate->id
                ];
            });
        } catch (QueryException $e) {
            throw new Exception('Trade entry creation Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry creation Failed :" . $e->getMessage());
        }
    }
    /**
     * Trade Entry Edit
     *
     * @param TradeEntryEditModel $tradeEntryEditModel
     * @param int $userId
     * @return object
     */
    public function edit(TradeEntryEditModel $tradeEntryEditModel, int $userId): object
    {
        try {
            return DB::transaction(function () use ($tradeEntryEditModel, $userId) {
                $wallet = Wallet::where('is_delete', 0)->where('id', $tradeEntryEditModel->walletId)->first();
                if (!$wallet) {
                    throw new Exception("User wallet not found");
                }
                $tradeAmt = $tradeEntryEditModel->winLoss === 'WIN'
                    ? $tradeEntryEditModel->profit
                    : $tradeEntryEditModel->loss;
                $tradeEdit = TradeEntry::where('is_delete', 0)->find($tradeEntryEditModel->tradeId);
                if ($tradeEdit) {
                    $action = '';
                    $oldDate = $tradeEdit->date;
                    $oldAmount = $tradeEdit->win_loss === 'WIN' ? $tradeEdit->profit : $tradeEdit->loss;
                    if ($tradeEdit->win_loss === $tradeEntryEditModel->winLoss) {
                        if ($tradeEdit->win_loss == "WIN") {
                            $actualBal = $wallet->amount - $oldAmount + $tradeAmt;
                            $action = 'deposite';
                        } else {
                            $actualBal = $wallet->amount + $oldAmount - $tradeAmt;
                            $action = 'withdraw';
                        }
                    } else {
                        if ($tradeEdit->win_loss === 'WIN' && $tradeEntryEditModel->winLoss === 'LOSS') {
                            $actualBal = $wallet->amount - $oldAmount - $tradeAmt;
                            $action = 'withdraw';
                        } else {
                            $actualBal = $wallet->amount + $oldAmount + $tradeAmt;
                            $action = 'deposite';
                        }
                    }
                    if (bccomp($actualBal, '0.00', 2) < 0) {
                        throw new Exception("Insufficient wallet balance.");
                    }
                    $tradeEdit->update([
                        'wallet_id' => $tradeEntryEditModel->walletId,
                        'date' => $tradeEntryEditModel->date,
                        'pair' => $tradeEntryEditModel->pair,
                        'lot_size' => $tradeEntryEditModel->lotSize,
                        'direction' => $tradeEntryEditModel->direction,
                        'entry_price' => $tradeEntryEditModel->entryPrice,
                        'stop_loss' => $tradeEntryEditModel->stopLoss,
                        'take_profit' => $tradeEntryEditModel->takeProfit,
                        'exit_price' => $tradeEntryEditModel->exitPrice,
                        'points_captured' => $tradeEntryEditModel->pointsCaptured,
                        'win_loss' => $tradeEntryEditModel->winLoss,
                        'risk_reward' => $tradeEntryEditModel->riskReward,
                        'reason' => $tradeEntryEditModel->reason,
                        'profit' => $tradeEntryEditModel->profit,
                        'loss' => $tradeEntryEditModel->loss,
                        'remark' => $tradeEntryEditModel->remark,
                    ]);
                    $this->walletService->walleteAction(
                        userId: $userId,
                        action: $action,
                        amount: $tradeAmt,
                        isLog: false,
                        date: $tradeEntryEditModel->date
                    );
                    $this->walletService->PaymentLogsActions(
                        amount: $tradeAmt,
                        balance: $actualBal,
                        walletId: $tradeEntryEditModel->walletId,
                        action: "TRADE ENTRY",
                        tradeId: $tradeEdit->id,
                        direction: $oldAmount < $tradeAmt
                            ? "Inward" : "Outward",
                        description: "Amount adjusted by trade of " . $oldDate,
                        createdDate: $tradeEntryEditModel->date
                    );
                    return (object)[
                        'id' => $tradeEdit->id
                    ];
                }
            });
        } catch (QueryException $e) {
            throw new Exception('Trade entry edit Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry edit Failed :" . $e->getMessage());
        }
    }
    /**
     * Trade Entry List
     *
     * @param integer $walletId
     * @return CommonListResponseModel
     */
    public function list(int $walletId): CommonListResponseModel
    {
        try {
            $tradeEntry = TradeEntry::where('wallet_id', $walletId)->where('is_delete', 0)->paginate(15);
            $tradeList = TradeEntryResources::collection($tradeEntry)->resolve();
            return new CommonListResponseModel(
                totalRecords: $tradeEntry->total(),
                currentPage: $tradeEntry->currentPage(),
                dataList: $tradeList
            );
        } catch (QueryException $e) {
            throw new Exception('Trade entry list Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry list Failed :" . $e->getMessage());
        }
    }
}
