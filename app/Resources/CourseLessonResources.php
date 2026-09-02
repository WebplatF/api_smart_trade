<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseLessonResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            'title' => $this->title ?? "",
            "video_count" => $this->videos_count ?? 0,
            'order_sort' => $this->order_sort ?? 0,
            'is_delete' => (bool)$this->is_delete ?? false,
        ];
    }
}
