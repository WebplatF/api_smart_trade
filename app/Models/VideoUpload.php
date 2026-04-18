<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoUpload extends Model
{
    protected $table = 'VideoUpload';

    protected $fillable = [
        'video_id',
        'thumbnail_id',
        'media_url',
        'durations'
    ];

    public function thumbnail()
    {
        return $this->belongsTo(ImageUpload::class, 'thumbnail_id', 'id');
    }
}
