<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseVideoResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title ?? "",
            "image" => $this->image->media_url ?? "",
            "video" => $this->video->video_id ?? "",
            "durations" => $this->video->durations ?? 0,
            "is_watch" => $this->watchHistory == null ? false :
                (bool)$this->watchHistory->is_watch,
            "last_time_stamp" => $this->watchHistory == null ? 0 :
                $this->watchHistory->last_time_stamp,
            "is_finshed" => $this->watchHistory == null ? false :
                (bool)$this->watchHistory->is_finshed,

        ];
    }
}
