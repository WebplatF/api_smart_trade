<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'Wallet';
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'wallet_create_date',
        'is_delete',
    ];
}
