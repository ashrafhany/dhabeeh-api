#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FCM Integration Test Script ===\n";
echo "Testing Firebase Configuration...\n";

// Check Environment Configuration
echo "\nChecking Environment Variables:\n";
echo "FIREBASE_PROJECT_ID: " . (env('FIREBASE_PROJECT_ID') ? "✓ Set" : "✗ Missing") . "\n";
echo "FIREBASE_CREDENTIALS: " . (env('FIREBASE_CREDENTIALS') ? "✓ Set" : "✗ Missing") . "\n";
echo "FIREBASE_DATABASE_URL: " . (env('FIREBASE_DATABASE_URL') ? "✓ Set" : "✗ Missing") . "\n";

// Check Service Account File
$credentialsPath = env('FIREBASE_CREDENTIALS', 'firebase/service-account.json');
$fullPath = storage_path($credentialsPath);
echo "\nChecking Service Account File:\n";
echo "Path: " . $fullPath . "\n";

if (file_exists($fullPath)) {
    echo "Service Account File: ✓ Exists\n";
    $credentialsJson = json_decode(file_get_contents($fullPath), true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "JSON Format: ✓ Valid\n";

        // Check for required fields
        $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($credentialsJson[$field]) || empty($credentialsJson[$field])) {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            echo "Required Fields: ✓ All present\n";
        } else {
            echo "Required Fields: ✗ Missing: " . implode(', ', $missingFields) . "\n";
        }
    } else {
        echo "JSON Format: ✗ Invalid - " . json_last_error_msg() . "\n";
    }
} else {
    echo "Service Account File: ✗ Missing\n";
}

// Check for required packages
echo "\nChecking Required Packages:\n";
$composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
$requiredPackages = [
    'firebase/php-jwt' => '✓ Found',
    'guzzlehttp/guzzle' => '✓ Found'
];

foreach ($requiredPackages as $package => $status) {
    if (!isset($composerJson['require'][$package])) {
        $requiredPackages[$package] = '✗ Missing';
    }
}

foreach ($requiredPackages as $package => $status) {
    echo "$package: $status\n";
}

// Check FCM Integration Classes
echo "\nChecking FCM Integration Classes:\n";

$fcmClassPath = app_path('Channels/FcmChannel.php');
echo "FcmChannel Class: " . (file_exists($fcmClassPath) ? "✓ Exists" : "✗ Missing") . "\n";

$fcmControllerPath = app_path('Http/Controllers/FcmController.php');
echo "FcmController Class: " . (file_exists($fcmControllerPath) ? "✓ Exists" : "✗ Missing") . "\n";

$fcmTestControllerPath = app_path('Http/Controllers/FcmTestController.php');
echo "FcmTestController Class: " . (file_exists($fcmTestControllerPath) ? "✓ Exists" : "✗ Missing") . "\n";

// Check Notification Classes for FCM Support
echo "\nChecking Notification Classes for FCM Support:\n";
$orderStatusChangedPath = app_path('Notifications/OrderStatusChanged.php');
$paymentStatusChangedPath = app_path('Notifications/PaymentStatusChanged.php');

$orderHasFcm = false;
$paymentHasFcm = false;

if (file_exists($orderStatusChangedPath)) {
    $orderContent = file_get_contents($orderStatusChangedPath);
    $orderHasFcm = strpos($orderContent, 'toFcm') !== false &&
                   strpos($orderContent, 'FcmChannel') !== false;
}

if (file_exists($paymentStatusChangedPath)) {
    $paymentContent = file_get_contents($paymentStatusChangedPath);
    $paymentHasFcm = strpos($paymentContent, 'toFcm') !== false &&
                     strpos($paymentContent, 'FcmChannel') !== false;
}

echo "OrderStatusChanged Notification: " . ($orderHasFcm ? "✓ FCM Supported" : "✗ FCM Not Supported") . "\n";
echo "PaymentStatusChanged Notification: " . ($paymentHasFcm ? "✓ FCM Supported" : "✗ FCM Not Supported") . "\n";

// Check User model
echo "\nChecking User Model:\n";
$userModelPath = app_path('Models/User.php');

$userHasFcmToken = false;
if (file_exists($userModelPath)) {
    $userContent = file_get_contents($userModelPath);
    $userHasFcmToken = strpos($userContent, 'fcm_token') !== false &&
                       strpos($userContent, 'notifications_enabled') !== false;
}

echo "User Model: " . ($userHasFcmToken ? "✓ Has FCM Token Fields" : "✗ Missing FCM Token Fields") . "\n";

// Check API Routes
echo "\nChecking API Routes:\n";
$apiRoutesPath = base_path('routes/api.php');

$hasRoutes = false;
$hasTestRoutes = false;
if (file_exists($apiRoutesPath)) {
    $routesContent = file_get_contents($apiRoutesPath);
    $hasRoutes = strpos($routesContent, '/register\', [FcmController::class') !== false ||
                 strpos($routesContent, '/fcm/register') !== false;

    $hasTestRoutes = strpos($routesContent, '/test\', [FcmTestController') !== false ||
                     strpos($routesContent, '/fcm/test') !== false;
}

echo "FCM API Routes: " . ($hasRoutes ? "✓ Present" : "✗ Missing") . "\n";
echo "FCM Test Routes: " . ($hasTestRoutes ? "✓ Present" : "✗ Missing") . "\n";

echo "\n=== FCM Integration Test Complete ===\n";
