<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideosResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "video_id" => $this->video_id ?? "",
            "media_url" => $this->media_url ?? "",
            "duration" => (int)$this->durations ?? 0,
            "thumbnail_id" => $this->thumbnail_id,
            "thumbnail" => $this->thumbnail->media_url ?? "",
            "status" => (bool)$this->is_delete ?? false
        ];
    }
}
