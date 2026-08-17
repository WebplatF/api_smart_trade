<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceMaster extends Model
{
    protected $table = 'InvoiceMaster';
    protected $fillable = [
        'invoice_no',
        'order_id',
        'user_id',
        'discount',
        'discount_type',
        'sub_total',
        'tax',
        'grand_total',
        'is_delete',
    ];
}

