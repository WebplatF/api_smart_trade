<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageMaster extends Model
{
    protected $table = 'HomePageMaster';

    protected $fillable = [
        'type',
        'source_id',
        'title'
    ];

    public function image()
    {
        return $this->belongsTo(ImageUpload::class, 'source_id', 'id');
    }

    public function video()
    {
        return $this->belongsTo(VideoUpload::class, 'source_id', 'id');
    }
}
