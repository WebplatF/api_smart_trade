<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMaster extends Model
{
    protected $table = 'UserMaster';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'last_login',
        'login_ip',
        'is_delete'
    ];

    protected $casts = [
        'login_ip' => 'array', // auto decode JSON
    ];
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'user_id', 'id');
    }
}
