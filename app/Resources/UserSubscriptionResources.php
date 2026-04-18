<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? "",
            'subscription_id' => $this->subscription_id,
            'plan_name' => $this->subscription->plan_name ?? "",
             'amount'  => $this->subscription->amount ?? "0",
            'validity' => $this->subscription->validity ?? "",
            'duration' => $this->subscription->duration ?? "",
            'requested_date' => $this->created_at->toDateString() ?? "",
            'start_date' => $this->start_date ?? "",
            'end_date' => $this->end_date ?? "",
            'renew_date' => $this->renew_date ?? "",
            'image_path' => $this->image->media_url ?? "",
            'status' => $this->status ?? "pending",
            'is_active' => (bool)$this->is_delete ?? false,
        ];
    }
}
