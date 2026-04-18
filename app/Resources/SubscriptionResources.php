<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "plan_name" => $this->plan_name ?? "",
            "amount" => $this->amount ?? "0",
            "validity" => $this->validity ?? "",
            "duration" => $this->duration ?? "",
            "status" => (bool)$this->is_delete ?? false
        ];
    }
}
