<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionMaster extends Model
{
    protected $table = 'SubscriptionMaster';

    protected $fillable = [
        'plan_name',
        'amount',
        'validity',
        'duration',
        'is_delete'
    ];
}
