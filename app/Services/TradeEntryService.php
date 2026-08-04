<?php

namespace App\Services;

use App\Models\TradeEntry;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TradeEntryService
{

    public function create()
    {
        try {
            // DB::transaction()
            TradeEntry::create([
                'wallet_id',
                'date',
                'pair',
                'lot_size',
                'direction',
                'entry_price',
                'stop_loss',
                'take_profit',
                'exit_price',
                'points_captured',
                'win_loss',
                'risk_reward',
                'reason',
                'profit',
                'loss',
                'remark',
            ]);
        } catch (QueryException $e) {
            throw new Exception('Trade entry creation Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry creation Failed :" . $e->getMessage());
        }
    }
}
