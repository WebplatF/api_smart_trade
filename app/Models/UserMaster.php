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
        'login_ip'
    ];

    protected $casts = [
        'login_ip' => 'array', // auto decode JSON
    ];
}
