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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
        $table->integer('quantity');
        $table->decimal('total_price', 10, 2)->default(0);
        $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null'); // 🔥 ربط الكوبون
        $table->decimal('discount_amount', 10, 2)->default(0); // 🔥 قيمة الخصم
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
        Schema::dropIfExists('carts');
    }
};
