<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoListResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            "title" => $this->title ?? "",
            'video_path' => $this->video != null ?  $this->video->video_id ?? "" : "",
             "durations" =>  $this->video != null ?  $this->video->durations ?? 0 : 0,
            'thumbnail_url' => $this->image->media_url ?? "",
        ];
    }
}
