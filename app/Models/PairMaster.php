<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PairMaster extends Model
{
    protected $table = 'PairMaster';
    protected $fillable = [
        'user_id',
        'pair'
    ];
}
