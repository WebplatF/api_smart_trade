<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponMaster extends Model
{
    protected $table = 'CouponMaster';
    protected $fillable = [
        'code',
        'discount_type',
        'value',
        'is_delete',
    ];
}
