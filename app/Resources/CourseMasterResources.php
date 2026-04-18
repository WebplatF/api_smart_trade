<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseMasterResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            'title' => $this->title ?? "",
            'expert' => $this->expert ?? "",
            'thumbnail_id' => $this->image->media_url ?? "",
            'is_delete' => (bool)$this->is_delete ?? false,
        ];
    }
}
