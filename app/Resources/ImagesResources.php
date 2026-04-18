<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImagesResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title ?? "",
            "media_url" => $this->media_url ?? "",
            "updated_at" => $this->updated_at->toDateString() ?? "",
            "status" => (bool)$this->is_delete ?? false
        ];
    }
}
