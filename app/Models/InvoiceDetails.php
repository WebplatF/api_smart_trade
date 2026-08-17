<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    protected $table = 'InvoiceDetails';
    protected $fillable = [
        'invoice_id',
        'user_sub_id',
        'is_delete',
    ];
    public function userSubscription()
    {
        return $this->belongsTo(
            UserSubscription::class,
            'user_sub_id'
        );
    }
}
