<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class PairListResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pair' => $this->pair,
        ];
    }
}
