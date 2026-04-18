<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseVideos extends Model
{
    protected $table = "CourseVideos";

    protected $fillable = [
        'detail_id',
        'title',
        'video_id',
        'thumbnail_id',
        'is_delete'
    ];

    public function image()
    {
        return $this->belongsTo(ImageUpload::class, 'thumbnail_id', 'id');
    }
    public function video()
    {
        return $this->belongsTo(VideoUpload::class, 'video_id', 'id');
    }
    public function courseDetails()
    {
        return $this->belongsTo(CourseDetails::class, 'detail_id', 'id');
    }
    public function watchHistory()
    {
        return $this->hasOne(WatchHistory::class, 'video_id');
    }
}
