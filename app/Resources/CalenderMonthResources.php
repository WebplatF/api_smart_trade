<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CalenderMonthResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this['date'] ?? null,
            'trade_count' => $this['trade_count'] ?? 0,
            'amount' => $this['amount'] ?? '0.00',
            'direction' => $this['direction'] ?? null,
        ];
    }
}
