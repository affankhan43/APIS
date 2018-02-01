<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('broker_id');
            $table->string('broker_username');
            $table->string('coin');
            $table->integer('coin_id');
            $table->string('withdraw_address');
            $table->string('message')->nullable();
            $table->string('auth_code');
            $table->string('userid');
            $table->string('username');
            $table->string('status')->nullable();
            $table->longText('details')->nullable();
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
        Schema::dropIfExists('withdraw_requests');
    }
}
