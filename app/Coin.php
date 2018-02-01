<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    //
    protected $fillable = ['broker_id', 'broker_username', 'coin', 'coin_name', 'first_api', 'second_api', 'withdraw_fees','min_withdraw',
];
}
