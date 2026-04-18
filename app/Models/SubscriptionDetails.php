<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionDetails extends Model
{
    protected $table = 'SubscriptionDetails';

    protected $fillable = [
        'subscription_id',
        'course_id'
    ];
}
