<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMaster extends Model
{
    protected $table = 'TranscationMaster';
    protected $fillable = [
        'user_id',
        'tag',
        'receipt',
        'amount',
        'order_id',
        'status',
        'payment_order_id',
        'payment_id',
        'signature',
        'is_delete',
    ];
}
