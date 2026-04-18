<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyMeeting extends Model
{
    protected $table = 'WeeklyMeeting';
    protected $fillable = [
        'title',
        'source_id',
        'is_delete',
    ];
    public function video()
    {
        return $this->belongsTo(VideoUpload::class, 'source_id', 'id');
    }
}
