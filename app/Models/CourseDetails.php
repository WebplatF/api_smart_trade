<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseDetails extends Model
{
    protected $table = "CourseDetails";

    protected $fillable = [
        'course_id',
        'title',
        'is_delete'
    ];

    public function videos()
    {
        return $this->hasMany(CourseVideos::class, 'detail_id');
    }

    public function courseMaster()
    {
        return $this->belongsTo(CourseMaster::class, 'course_id', 'id');
    }
}
