<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeEntry extends Model
{
    protected $table = 'TradeEntry';
    protected $fillable = [
        'wallet_id',
        'date',
        'pair',
        'lot_size',
        'direction',
        'entry_price',
        'stop_loss',
        'take_profit',
        'exit_price',
        'points_captured',
        'win_loss',
        'risk_reward',
        'reason',
        'profit',
        'loss',
        'remark',
        'is_delete',
    ];
}
