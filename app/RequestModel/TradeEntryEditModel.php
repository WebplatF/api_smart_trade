<?php

namespace App\RequestModel;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TradeEntryEditModel
{
    public function __construct(
        public int $tradeId,
        public int $walletId,
        public string $date,
        public string $pair,
        public float $lotSize,
        public string $direction,
        public float $entryPrice,
        public float $stopLoss,
        public float $takeProfit,
        public float $exitPrice,
        public float $pointsCaptured,
        public float $winLoss,
        public string $riskReward,
        public string $reason,
        public float $profit,
        public float $loss,
        public string $remark
    ) {}
    public static function fromRequest(Request $request): self
    {
        $validate = Validator::make($request->all(), [
            'trade_id' => 'required|strict_int',
            'wallet_id' => 'required|strict_int',
            'date' => 'required|strict_string',
            'pair' => 'required|strict_string',
            'lot_size' => 'required|strict_number',
            'direction' => 'required|strict_string',
            'entry_price' => 'required|strict_number',
            'stop_loss' => 'required|strict_number',
            'take_profit' => 'required|strict_number',
            'exit_price' => 'required|strict_number',
            'points_captured' => 'required|strict_number',
            'win_loss' => 'required|strict_strict',
            'risk_reward' => 'required|strict_number',
        ]);
        if ($validate->fails()) {
            throw new Exception($validate->errors()->first());
        }
        return self::fromArray($validate->all());
    }
    public static function fromArray(array $data): self
    {
        return new self(
            tradeId: $data['trade_id'],
            walletId: $data['wallet_id'],
            date: $data['date'],
            pair: $data['pair'],
            lotSize: $data['lot_size'],
            direction: $data['direction'],
            entryPrice: $data['entry_price'],
            stopLoss: $data['stop_loss'],
            takeProfit: $data['take_profit'],
            exitPrice: $data['exit_price'],
            pointsCaptured: $data['points_captured'],
            winLoss: $data['win_loss'],
            riskReward: $data['risk_reward'],
            reason: $data['reason'],
            profit: $data['profit'],
            loss: $data['loss'],
            remark: $data['remark'],
        );
    }
}
