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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي قام بالطلب
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // المنتج المطلوب
            $table->integer('quantity')->default(1); // الكمية المطلوبة
            $table->decimal('total_price', 10, 2); // السعر الإجمالي للطلب
            $table->string('status')->default('pending'); // حالة الطلب (pending, completed, cancelled)
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
        Schema::dropIfExists('orders');
    }
};
