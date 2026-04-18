<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResorces extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "is_delete" => (bool)$this->is_delete ?? false,
            "videos" => CourseVideoResources::collection($this->videos ?? [])
        ];
    }
}
