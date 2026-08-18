<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailsResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "subscription" => $this->user_subscription != null ? [
                "id" => $this->user_subscription->id,
                "subscription_id" =>  $this->subscription_id,
                "plan_name" =>  $this->plan_name ?? "",
                "amount" => $this->amount ?? "0",
                "validity" => $this->validity ??  "",
            ] : null
        ];
    }
}
