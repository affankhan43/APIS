<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrokersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brokers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('broker_id')->unique();
            $table->string('broker_username')->unique();
            $table->string('broker_email')->unique();
            $table->integer('no_coins');
            $table->integer('no_pairs');
            $table->longText('coins');
            $table->longText('pairs');
            $table->string('country');
            $table->string('domain')->unique();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brokers');
    }
}
