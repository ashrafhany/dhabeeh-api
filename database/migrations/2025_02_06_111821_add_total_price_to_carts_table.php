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

    Schema::table('carts', function (Blueprint $table) {
        $table->decimal('total_price', 10, 2)->after('quantity')->default(0); // ✅ إضافة `total_price`
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('total_price'); // حذف `total_price` عند التراجع
        });
    }
};
