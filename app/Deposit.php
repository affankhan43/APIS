<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    //
    protected $fillable = ['broker_id', 'broker_username', 'coin', 'coin_id', 'userid', 'username', 'address','message','category','amount','confirmations','txid','comment',
];
    
}
