<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class WalletResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount ?? 0,
            'create_date' => $this->wallet_create_date,
            'status' => (bool)$this->is_delete ?? false,
        ];
    }
}
