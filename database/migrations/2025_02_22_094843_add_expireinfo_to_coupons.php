<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('usage_limit')->default(1); // عدد مرات الاستخدام المسموح بها
            $table->integer('used_count')->default(0); // عدد المرات المستخدمة
            $table->dateTime('expires_at')->nullable(); // تاريخ الانتهاء
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            //
        });
    }
};
