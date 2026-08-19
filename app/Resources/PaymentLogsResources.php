<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class PaymentLogsResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount ?? 0.0,
            'balance' => $this->balance ?? 0.0,
            'action' => $this->action ?? "",
            'description' => $this->description ?? "",
            'direction' => $this->direction ?? "",
            'created_date' => $this->created_at->toDateString() ?? ""
        ];
    }
}
