<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class InvoiceUserResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" =>  $this->name ?? "",
            "email" => $this->email ??  "",
            "mobile" =>  $this->email ?? ""
        ];
    }
}
