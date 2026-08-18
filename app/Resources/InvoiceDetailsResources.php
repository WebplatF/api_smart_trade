<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailsResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "subscription" => $this->userSubscription == null ? null : [
                "id" => $this->userSubscription->id,
                "subscription_id" =>  $this->userSubscription->subscription_id,
                "plan_name" =>  $this->userSubscription->plan_name ?? "",
                "amount" => $this->userSubscription->amount ?? "0",
                "validity" => $this->userSubscription->validity ??  "",
                "duration" => $this->userSubscription->duration ??  "",
            ]
        ];
    }
}
