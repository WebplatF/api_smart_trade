<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpMaster extends Model
{
    protected $table = 'OtpMaster';

    protected $fillable = [
        'user_id',
        'trx_id',
        'otp',
        'is_delete',
    ];
}
