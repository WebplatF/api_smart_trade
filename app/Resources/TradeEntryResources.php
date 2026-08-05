<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class TradeEntryResources extends JsonResource
{

    public function toArray($request)
    {
        return [
            'trade_id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'date' => $this->date ?? "",
            'pair' => $this->pair ?? "",
            'lot_size' => $this->lot_size ?? 0.0,
            'direction' => $this->direction ?? "",
            'entry_price' => $this->entry_price ??  0.0,
            'stop_loss' => $this->stop_loss ??  0.0,
            'take_profit' => $this->take_profit ??  0.0,
            'exit_price' => $this->exit_price ??  0.0,
            'points_captured' => $this->points_captured ?? 0.0,
            'win_loss' => $this->win_loss ??  "",
            'risk_reward' => $this->risk_reward ?? 0.0,
            'reason' => $this->reason ?? "",
            'profit' => $this->profit ?? 0.0,
            'loss' => $this->loss ?? 0.0,
            'remark' => $this->remark ?? "",
        ];
    }
}
