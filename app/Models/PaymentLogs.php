<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLogs extends Model
{
    protected $table = 'PaymentLogs';
    protected $fillable = [
        'wallet_id',
        'description',
        'amount',
        'action',
        'direction',
        'trade_id',
        'balance',
        'is_delete',
        'created_at'
    ];
}
