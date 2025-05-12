# PowerShell script to test order creation with payment

# Define the API endpoints
$baseUrl = "http://localhost:8000/api"
$ordersUrl = "$baseUrl/orders"
$cartUrl = "$baseUrl/cart"

# Define authentication token
$token = "12|3j6i8yJGowRAuFTSIcOcUhHrgckiLJhzSRXj2ihk67470625" # توكن المصادقة

# First, check the cart contents
Write-Host "Checking cart contents..."
try {
    $cartResponse = Invoke-WebRequest -Uri $cartUrl -Method GET -Headers @{
        "Authorization" = "Bearer $token"
    }
    $cartContent = $cartResponse.Content | ConvertFrom-Json
    Write-Host "Cart content:" -ForegroundColor Green
    $cartContent | ConvertTo-Json -Depth 5
} catch {
    Write-Host "Error checking cart: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Red
    }
}

# Add a product to cart
Write-Host "Adding product to cart..."
$addToCartData = @{
    variant_id = 1  # تأكد من وجود هذا المنتج في قاعدة البيانات
    quantity = 1
}

try {
    $addCartResponse = Invoke-WebRequest -Uri "$cartUrl/add" -Method POST -Body ($addToCartData | ConvertTo-Json) -ContentType "application/json" -Headers @{
        "Authorization" = "Bearer $token"
    }
    Write-Host "Product added to cart successfully!" -ForegroundColor Green
    $addCartResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 5
} catch {
    Write-Host "Error adding product to cart: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Red
    }
}

# Check cart contents again
Write-Host "Checking cart contents after adding product..."
try {
    $cartResponse = Invoke-WebRequest -Uri $cartUrl -Method GET -Headers @{
        "Authorization" = "Bearer $token"
    }
    $cartContent = $cartResponse.Content | ConvertFrom-Json
    Write-Host "Cart content:" -ForegroundColor Green
    $cartContent | ConvertTo-Json -Depth 5
} catch {
    Write-Host "Error checking cart: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Red
    }
}

# Create order
Write-Host "Creating order..."
$orderData = @{
    shipping_address = "Test Address"
    payment_method = "visa"  # طريقة الدفع: visa, apple_pay, cash, bank
}

# Convert the order data to JSON
$jsonData = $orderData | ConvertTo-Json -Depth 10

# Send the POST request with authentication
try {
    $response = Invoke-WebRequest -Uri $ordersUrl -Method POST -Body $jsonData -ContentType "application/json" -Headers @{
        "Authorization" = "Bearer $token"
    }

    # Output the response
    Write-Host "Status Code: $($response.StatusCode)"
    Write-Host "Response Content:"
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error Status Code: $($_.Exception.Response.StatusCode.value__)"
    Write-Host "Error Message: $($_.Exception.Message)"

    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body: $responseBody"
    }
}

# Define the order data - بيانات الطلب
$orderData = @{
    shipping_address = "Test Address"
    payment_method = "visa"  # طريقة الدفع: visa, apple_pay, cash, bank
}

# Convert the order data to JSON
$jsonData = $orderData | ConvertTo-Json -Depth 10

# Send the POST request with authentication
try {
    $response = Invoke-WebRequest -Uri $apiUrl -Method POST -Body $jsonData -ContentType "application/json" -Headers @{
        "Authorization" = "Bearer $token"
    }

    # Output the response
    Write-Host "Status Code: $($response.StatusCode)"
    Write-Host "Response Content:"
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
}
catch {
    Write-Host "Error Status Code: $($_.Exception.Response.StatusCode.value__)"
    Write-Host "Error Message: $($_.Exception.Message)"

    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body: $responseBody"
    }
}
