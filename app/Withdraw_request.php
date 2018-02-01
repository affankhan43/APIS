<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Withdraw_request extends Model
{
    //
    protected $fillable = ['id','broker_id', 'broker_username', 'coin', 'coin_id', 'withdraw_address', 'message', 'userid','username','status','auth_code',
];
}
