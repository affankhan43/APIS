<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('broker_id');
            $table->string('broker_username');
            $table->string('coin');
            $table->string('coin_name');
            $table->string('first_api')->default('NULL');
            $table->string('second_api')->default('NULL');
            $table->string('third_api')->default('NULL');
            $table->string('withdraw_fees');
            $table->string('min_withdraw');
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
        Schema::dropIfExists('coins');
    }
}
