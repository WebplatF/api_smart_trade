<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageUpload extends Model
{
    protected $table = 'ImageUpload';

    protected $fillable = [
        'title',
        'media_url'
    ];
}
