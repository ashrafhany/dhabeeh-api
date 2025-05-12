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
        Schema::create('fcm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('fcm_settings')->insert([
            [
                'key' => 'notification_enabled',
                'value' => 'true',
                'description' => 'Enable or disable all Firebase Cloud Messaging notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'order_notifications',
                'value' => 'true',
                'description' => 'Enable notifications for order status changes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payment_notifications',
                'value' => 'true',
                'description' => 'Enable notifications for payment status changes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'promotion_notifications',
                'value' => 'true',
                'description' => 'Enable notifications for promotions and offers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fcm_settings');
    }
};
