<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    protected $table = 'WatchHistory';

    protected $fillable = [
        'user_id',
        'video_id',
        'subscription_id',
        'last_time_stamp',
        'is_watch',
        'is_finshed',
        'is_delete',
        'created_at',
        'updated_at',
    ];
    public $timestamps = false;
}
