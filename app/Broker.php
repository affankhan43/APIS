<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    //
   protected $fillable = ['broker_id', 'broker_username', 'broker_email', 'no_coins', 'no_pairs', 'coins', 'pairs','country', 'domain',];
}
