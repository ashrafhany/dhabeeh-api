<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('infos')) {
            Schema::create('infos', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->longText('content');
                $table->timestamps();
            });

            // إضافة البيانات الافتراضية
            $this->seedDefaultInfo();
        }
    }

    /**
     * إضافة بيانات افتراضية للمعلومات
     */
    private function seedDefaultInfo()
    {
        DB::table('infos')->insert([
            [
                'title' => 'about',
                'content' => 'هذا التطبيق خاص ببيع الذبائح وتوصيلها.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'privacy_policy',
                'content' => 'سياسة الخصوصية هنا...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'terms',
                'content' => 'الشروط والأحكام هنا...',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'contact',
                'content' => json_encode(['phone' => '+9660507944402', 'email' => 'support@example.com']),
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
        Schema::dropIfExists('infos');
    }
};
