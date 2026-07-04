<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CouponListResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code ?? "",
            'discount_type' => $this->discount_type,
            'value' => $this->value ?? "",
            'status' => (bool)$this->is_delete ?? false,
        ];
    }
}
