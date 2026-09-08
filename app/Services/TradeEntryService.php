<?php

namespace App\Services;

use App\Helper\DatabaseErrorHelper;
use App\Models\PaymentLogs;
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
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
                        } else {
                            $actualBal = $wallet->amount + $oldAmount - $tradeAmt;
                        }
                    } else {
                        if ($tradeEdit->win_loss === 'WIN' && $tradeEntryEditModel->winLoss === 'LOSS') {
                            $actualBal = $wallet->amount - $oldAmount - $tradeAmt;
                        } else {
                            $actualBal = $wallet->amount + $oldAmount + $tradeAmt;
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
                    $wallet->lockForUpdate();
                    $wallet->update([
                        'amount' => $actualBal,
                    ]);
                    // $this->walletService->walleteAction(
                    //     userId: $userId,
                    //     action: $action,
                    //     amount: $walletReduce,
                    //     isLog: false,
                    //     date: $tradeEntryEditModel->date
                    // );
                    PaymentLogs::where('trade_id', $tradeEdit->id)
                        ->where('action', 'TRADE ENTRY')
                        ->delete();

                    $this->walletService->PaymentLogsActions(
                        amount: $tradeAmt,
                        balance: $actualBal,
                        walletId: $tradeEntryEditModel->walletId,
                        action: "TRADE ENTRY",
                        tradeId: $tradeEdit->id,
                        direction: $tradeEntryEditModel->winLoss == "WIN"
                            ? "Inward" : "Outward",
                        description: "Amount added of trade",
                        createdDate: $tradeEntryEditModel->date
                    );
                    return (object)[
                        'id' => $tradeEdit->id
                    ];
                }
            });
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
    /**
     * Trade Delete
     *
     * @param integer $id
     * @return void
     */
    public function deleteTrade(int $id)
    {
        try {
            DB::transaction(function () use ($id) {

                $trade = TradeEntry::where('is_delete', 0)
                    ->where('id', $id)
                    ->first();

                if (!$trade) {
                    throw new Exception('Trade not found');
                }

                $wallet = Wallet::where('is_delete', 0)
                    ->where('id', $trade->wallet_id)
                    ->first();

                if (!$wallet) {
                    throw new Exception('User wallet not found');
                }

                // Reverse trade amount
                if ($trade->win_loss === 'WIN') {

                    $wallet->amount = bcsub(
                        (string) $wallet->amount,
                        (string) ($trade->profit ?? 0),
                        2
                    );
                } else {

                    $wallet->amount = bcadd(
                        (string) $wallet->amount,
                        (string) ($trade->loss ?? 0),
                        2
                    );
                }

                $wallet->save();
                // Delete only payment log related to this trade
                PaymentLogs::where('trade_id', $trade->id)->delete();
                // Soft delete trade
                $trade->is_delete = 1;
                $trade->save();
            });
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
