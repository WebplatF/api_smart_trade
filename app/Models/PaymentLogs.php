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
        'balance',
        'is_delete',
    ];
}
