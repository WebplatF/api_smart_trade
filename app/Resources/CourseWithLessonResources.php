<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseWithLessonResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title ?? "",
            "expert" => $this->expert ?? "",
            "image" => $this->image->media_url ?? "",
            "lesson" => LessonResorces::collection($this->lesson ?? [])
        ];
    }
}
