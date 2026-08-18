<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceMasterResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "invoice_no" => $this->invoice_no ??  "",
            "order_id" => $this->order_id ??  "",
            "user_id" => $this->user_id,
            "discount" => $this->discount ?? "",
            "discount_type" => $this->discount_type ?? "",
            "sub_total" => $this->sub_total ?? "0",
            "tax" =>  $this->sub_total ?? "18",
            "grand_total" => $this->grand_total ?? "0",
            "is_delete" => (bool)$this->is_delete ?? false,
            "created_at" => $this->created_at->toDateString(),
        ];
    }
}
