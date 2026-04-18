<?php

namespace App\Models;

use App\Models\SubscriptionMaster;
use App\Models\UserMaster;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $table = 'UserSubscription';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'start_date',
        'end_date',
        'renew_date',
        'image_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(UserMaster::class, 'user_id', 'id');
    }
    public function subscription()
    {
        return $this->belongsTo(SubscriptionMaster::class, 'subscription_id', 'id');
    }
    public function image()
    {
        return $this->belongsTo(ImageUpload::class, 'image_id', 'id');
    }
}
