<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('coupons')){
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                $table->string('code', 8)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                $table->unsignedBigInteger('company_id');
                $table->tinyInteger('type')->default(1)->comment('0 - Fixed, 1 - Percentage');
                $table->double('price');
                $table->date('active_from');
                $table->date('active_to');
                $table->integer('limit_to_num_uses');
                $table->integer('used_count');
                $table->timestamps();
    
                $table->unique('code', 'coupons_code_unique');
                $table->index('company_id');
                $table->foreign('company_id')->references('id')->on('companies');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
