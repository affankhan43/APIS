<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coin_address extends Model
{
    //
    protected $fillable = ['broker_id', 'broker_username', 'coin', 'coin_id', 'address', 'message', 'userid','username',
];
}
