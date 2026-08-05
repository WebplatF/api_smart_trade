<?php

namespace App\Services;

use App\Models\TradeEntry;
use App\RequestModel\TradeEntryCreateModel;
use App\RequestModel\TradeEntryEditModel;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TradeEntryService
{
    /**
     * Trade Entry Creation
     *
     * @param TradeEntryCreateModel $tradeEntryCreateModel
     * @return object
     */
    public function create(TradeEntryCreateModel $tradeEntryCreateModel): object
    {
        try {
            return DB::transaction(function () use ($tradeEntryCreateModel) {
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
     * @return object
     */
    public function edit(TradeEntryEditModel $tradeEntryEditModel): object
    {
        try {
            return DB::transaction(function () use ($tradeEntryEditModel) {
                $tradeEdit = TradeEntry::create([
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
                return (object)[
                    'id' => $tradeEdit->id
                ];
            });
        } catch (QueryException $e) {
            throw new Exception('Trade entry edit Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry edit Failed :" . $e->getMessage());
        }
    }
}
