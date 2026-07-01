<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loyalty_account_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', ['earn', 'redeem', 'welcome'])->default('earn');
            $table->integer('points'); // positive for earn/welcome, negative for redeem
            $table->string('coupon_code')->nullable(); // set when type = redeem
            $table->string('description')->nullable();
            $table->timestamps();

            // Prevents the same order from ever being credited with points twice
            $table->unique('order_id');

            $table->foreign('loyalty_account_id')->references('id')->on('loyalty_accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
