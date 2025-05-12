<?php
// سكريبت لإضافة منتج إلى سلة المستخدم

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// الحصول على مستخدم
$user = \App\Models\User::first();

if (!$user) {
    echo "لا يوجد مستخدمين في قاعدة البيانات\n";
    exit;
}

// الحصول على منتج
$variant = \App\Models\ProductVariant::first();

if (!$variant) {
    echo "لا توجد منتجات متاحة في قاعدة البيانات\n";
    exit;
}

// حذف العناصر السابقة من السلة للمستخدم (اختياري)
\App\Models\Cart::where('user_id', $user->id)->delete();

// إضافة منتج إلى سلة المستخدم
$cart = \App\Models\Cart::create([
    'user_id' => $user->id,
    'variant_id' => $variant->id,
    'quantity' => 1,
    'total_price' => $variant->price ?? 100,
]);

if ($cart) {
    echo "تم إضافة منتج إلى سلة المستخدم بنجاح!\n";
    echo "معرف المستخدم: " . $user->id . "\n";
    echo "معرف المنتج: " . $variant->id . "\n";
    echo "السعر: " . ($variant->price ?? 100) . "\n";
} else {
    echo "فشل في إضافة المنتج إلى سلة المستخدم\n";
}
