<?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\CategoryController;
    use App\Http\Controllers\ProductController;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\UserController;
    use App\Http\Controllers\SettingsController;
    use App\Http\Controllers\TwilioController;
    use App\Http\Controllers\InfoController;
    use App\Http\Controllers\CartController;
    use App\Http\Controllers\SliderController;
    use App\Http\Controllers\NotificationController;
    use App\Http\Controllers\TestNotificationController;
    use App\Http\Controllers\TapTestController;
    use App\Http\Controllers\TapDirectTestController;
    use App\Http\Controllers\OrderTesterController;
    use App\Http\Controllers\TapCallbackTestController;
    use App\Http\Controllers\OrderFlowTestController;

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
    */
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    //Route::post('/login', [AuthController::class, 'verifyOtp'])->name('login');
    /*
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    */

    // TAP Payment Testing Routes
    Route::prefix('tap-test')->group(function () {
        Route::post('/charge', [TapTestController::class, 'testCharge']);
        Route::post('/direct', [TapDirectTestController::class, 'testDirectCharge']);
        Route::post('/order-flow', [OrderTesterController::class, 'testTapCallback']);
        Route::post('/callback', [TapCallbackTestController::class, 'testCallback']);
    });

    Route::get('/tap/callback', [OrderController::class, 'handleTapRedirect'])->name('tap.callback');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);  // جلب جميع التصنيفات
            Route::get('/{id}', [CategoryController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/{category_id}/products', [ProductController::class, 'getByCategory'])->where('category_id', '[0-9]+');
        });

        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/search', [ProductController::class, 'search']);
            Route::get('/{id}', [ProductController::class, 'show'])->where('id', '[0-9]+');
            Route::get('/top', [ProductController::class, 'topProducts']); // ✅ إضافة مسار المنتجات الأعلى مبيعًا
        });
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']); // عرض جميع الطلبات
            Route::post('/', [OrderController::class, 'store']); // إنشاء طلب جديد
            Route::get('/{id}', [OrderController::class, 'show']); // عرض تفاصيل طلب معين
            Route::put('/{id}', [OrderController::class, 'update']); // تحديث حالة الطلب
            Route::delete('/{id}', [OrderController::class, 'destroy']); // حذف الطلب
            Route::get('/status/{status}', [OrderController::class, 'getOrdersByStatus']);  // ✅ إضافة مسار الطلبات بحالة معينة
            Route::post('/check-coupon', [OrderController::class, 'checkCoupon']); // ✅ إضافة مسار فحص الكوبون
        });
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'user']); // عرض بيانات المستخدم
            Route::put('/update', [UserController::class, 'update']); // تحديث بيانات المستخدم
            Route::delete('/delete', [UserController::class, 'deleteAccount']);
        });
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'getLanguage']); // عرض الإعدادات
            Route::put('/', [SettingsController::class, 'getLanguage']); // تحديث الإعدادات
        });
        Route::prefix('info')->group(function () {
            Route::get('/', [InfoController::class, 'about']); // عن التطبيق
            Route::get('/terms', [InfoController::class, 'terms']); // الشروط والأحكام
            Route::get('/privacy', [InfoController::class, 'privacyPolicy']); // سياسة الخصوصية
            Route::get('/contact', [InfoController::class, 'contact']); // تواصل معنا
            Route::post('/rate-app', [InfoController::class, 'rateApp']); // تقييم التطبيق

        });
        Route::prefix('cart')->group(function(){
            Route::get('/', [CartController::class, 'index']); // جلب محتويات السلة
            Route::post('/add', [CartController::class, 'add']); // إضافة منتج للسلة
            Route::put('/update/{id}', [CartController::class, 'update']); // تحديث كمية المنتج
            Route::delete('/remove', [CartController::class, 'remove']); // إزالة منتج معين
            Route::delete('/clear', [CartController::class, 'clear']); // إفراغ السلة بالكامل
        });

        Route::prefix('sliders')->group(function () {
            Route::get('/', [SliderController::class, 'index']); // جلب جميع السلايدرز
        });

        // Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);

            // Test routes - remove in production
            Route::post('/test/order-status', [TestNotificationController::class, 'sendOrderStatusTest']);
            Route::post('/test/payment-status', [TestNotificationController::class, 'sendPaymentStatusTest']);
        });
    });

    // Route::get('/categories', [CategoryController::class, 'index']);
