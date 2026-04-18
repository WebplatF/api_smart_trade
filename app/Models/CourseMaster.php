<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaster extends Model
{
    protected $table = 'CourseMaster';

    protected $fillable = [
        'title',
        'expert',
        'thumbnail_id',
        'is_delete',
    ];

    public function image()
    {
        return $this->belongsTo(ImageUpload::class, 'thumbnail_id', 'id');
    }

    public function lesson()
    {
        return $this->hasMany(CourseDetails::class, 'course_id');
    }
}
