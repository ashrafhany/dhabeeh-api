# اختبار واجهة برمجة التطبيقات لبوابة الدفع TAP
# هذا الملف يحاكي اختبارات Postman

$baseUrl = "http://localhost:8000"

# ------------------------------------
# الدوال المساعدة
# ------------------------------------

# دالة لطباعة النتائج بتنسيق أفضل
function Format-Response {
    param (
        [Parameter(Mandatory=$true)]
        [object]$Response
    )

    Write-Host "Status Code: $($Response.StatusCode)" -ForegroundColor Green
    Write-Host "Headers:" -ForegroundColor Yellow
    $Response.Headers | Format-Table -AutoSize

    if ($Response.Content) {
        Write-Host "Content:" -ForegroundColor Cyan
        $content = $Response.Content | ConvertFrom-Json -Depth 10
        $content | ConvertTo-Json -Depth 10
    }
}

# ------------------------------------
# اختبارات TAP API المباشرة
# ------------------------------------

function Test-DirectTapPayment {
    Write-Host "اختبار TAP API مباشرة (بدون معاملات قاعدة البيانات)" -ForegroundColor Magenta

    $endpoint = "$baseUrl/api/tap-test/direct"
    $body = @{
        payment_method = "visa"
        amount = 150.00
        email = "test@example.com"
        phone = "966500000000"
        first_name = "اختبار"
        last_name = "المستخدم"
    } | ConvertTo-Json

    Write-Host "إرسال طلب إلى: $endpoint" -ForegroundColor Yellow
    Write-Host "البيانات المرسلة:" -ForegroundColor Yellow
    Write-Host $body

    try {
        $response = Invoke-WebRequest -Uri $endpoint -Method POST -ContentType "application/json" -Body $body
        $responseData = $response.Content | ConvertFrom-Json

        Write-Host "تم إنشاء معاملة دفع بنجاح!" -ForegroundColor Green
        Write-Host "معرف المعاملة: $($responseData.charge_id)" -ForegroundColor Green
        Write-Host "رابط الدفع: $($responseData.transaction_url)" -ForegroundColor Green

        # إرجاع بيانات المعاملة لاستخدامها في اختبارات أخرى
        return $responseData
    }
    catch {
        Write-Host "خطأ في إنشاء معاملة الدفع:" -ForegroundColor Red
        Write-Host $_.Exception.Message -ForegroundColor Red
    }
}

# ------------------------------------
# اختبار مُعالج استدعاء TAP
# ------------------------------------

function Test-TapCallback {
    param (
        [Parameter(Mandatory=$true)]
        [string]$TapId
    )

    Write-Host "اختبار التعامل مع استدعاء TAP" -ForegroundColor Magenta

    $endpoint = "$baseUrl/api/tap-test/callback"
    $body = @{
        tap_id = $TapId
    } | ConvertTo-Json

    Write-Host "إرسال طلب استدعاء إلى: $endpoint" -ForegroundColor Yellow
    Write-Host "معرف المعاملة: $TapId" -ForegroundColor Yellow

    try {
        $response = Invoke-WebRequest -Uri $endpoint -Method POST -ContentType "application/json" -Body $body
        $responseData = $response.Content | ConvertFrom-Json

        Write-Host "تم معالجة استدعاء TAP بنجاح!" -ForegroundColor Green
        Write-Host "حالة المعاملة: $($responseData.charge_data.status)" -ForegroundColor Green

        return $responseData
    }
    catch {
        Write-Host "خطأ في معالجة استدعاء TAP:" -ForegroundColor Red
        Write-Host $_.Exception.Message -ForegroundColor Red
    }
}

# ------------------------------------
# إنشاء طلب وإتمام عملية الدفع
# ------------------------------------

function Test-CreateOrder {
    param (
        [string]$PaymentMethod = "visa"
    )

    Write-Host "اختبار إنشاء طلب كامل باستخدام $PaymentMethod" -ForegroundColor Magenta

    # تأكد من وجود مستخدم وتسجيل الدخول أولاً
    # هنا يجب أن نقوم بتسجيل الدخول وإضافة منتج إلى السلة
    # لكن لاختبار تكامل TAP فقط، سنستخدم واجهة الاختبار المباشرة

    $tapResponse = Test-DirectTapPayment

    if ($tapResponse -and $tapResponse.charge_id) {
        # اختبار التعامل مع استدعاء TAP بعد نجاح الدفع
        Start-Sleep -Seconds 2 # انتظر لثانيتين لضمان معالجة المعاملة
        Test-TapCallback -TapId $tapResponse.charge_id
    }
}

# ------------------------------------
# تنفيذ الاختبارات
# ------------------------------------

# اختبار 1: إنشاء معاملة دفع TAP مباشرة
$tapTransaction = Test-DirectTapPayment
Write-Host "------------------------------------------------" -ForegroundColor White

# اختبار 2: اختبار التعامل مع استدعاء TAP
if ($tapTransaction -and $tapTransaction.charge_id) {
    Test-TapCallback -TapId $tapTransaction.charge_id
}
