# Notification System Documentation

## Overview
This document outlines the notification system implemented in the Dhabeeh API project. The system handles notifications for order status changes and payment status changes, and now includes Firebase Cloud Messaging (FCM) support for push notifications to mobile devices.

### Version History
- **v1.0** - Basic notification system with database and email support
- **v2.0** - Added FCM integration for real-time push notifications to mobile devices

## Available Notification Types

1. **Order Status Change Notifications**
   - Triggered when an order's status changes (e.g., pending → current → inDelivery → delivered)
   - Managed by `App\Notifications\OrderStatusChanged` class
   - Now supports FCM push notifications

2. **Payment Status Change Notifications**
   - Triggered when a payment's status changes (e.g., pending → paid or failed)
   - Managed by `App\Notifications\PaymentStatusChanged` class
   - Now supports FCM push notifications

## Notification Channels

Notifications are delivered via:
- **Database**: All notifications are stored in the database
- **Email**: Notifications are sent via email if user has a valid email address
- **FCM Push Notifications**: Real-time push notifications are sent to user's devices if they have registered an FCM token and enabled notifications

## How Notifications Are Triggered

Notifications are automatically triggered by:

1. **Model Events**: The `Order` model has observer events that detect changes to `status` and `payment_status` fields and send notifications accordingly.

2. **Manual Triggers**: When handling payment callback from TAP payment gateway or when an admin updates order status.

## API Endpoints

### Notification Management

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/notifications` | GET | List all notifications for authenticated user |
| `/api/notifications/{id}/read` | POST | Mark a specific notification as read |
| `/api/notifications/read-all` | POST | Mark all notifications as read |
| `/api/notifications/{id}` | DELETE | Delete a specific notification |

### FCM Push Notification Management

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/fcm/register` | POST | Register or update FCM token for the authenticated user |
| `/api/fcm/toggle` | POST | Enable or disable push notifications |
| `/api/fcm/unregister` | DELETE | Remove FCM token (usually when logging out) |

### Testing Endpoints (For Development Only)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/notifications/test/order-status` | POST | Send a test order status notification |
| `/api/notifications/test/payment-status` | POST | Send a test payment status notification |
| `/api/fcm/test` | POST | Send a test push notification to the authenticated user |
| `/api/fcm/test/user` | POST | Send a test push notification to a specific user (admin only) |

## Notification Fields

Each notification contains:
- `id`: Unique UUID for the notification
- `type`: The type of notification (OrderStatusChanged or PaymentStatusChanged)
- `data`: JSON object containing relevant notification data
- `read_at`: Timestamp when notification was read (null if unread)
- `created_at`: Timestamp when notification was created

## Examples

### Order Status Notification Data:
```json
{
  "order_id": 123,
  "status": "inDelivery",
  "previous_status": "current",
  "product_name": "Product Name",
  "quantity": 2,
  "total_price": 150.00,
  "timestamp": "2025-05-10 15:30:45"
}
```

### Payment Status Notification Data:
```json
{
  "order_id": 123,
  "payment_status": "paid",
  "payment_method": "visa",
  "product_name": "Product Name",
  "total_price": 150.00,
  "timestamp": "2025-05-10 15:30:45"
}
```

## How to Extend

To add a new notification type:

1. Create a new notification class:
```php
php artisan make:notification NewNotificationType
```

2. Implement the notification logic in the class
3. Add any additional fields to the notification data
4. Make sure to implement `toFcm()` method to support push notifications:

```php
public function toFcm($notifiable)
{
    return [
        'title' => 'Notification Title',
        'body' => 'Notification Body',
        'data' => [
            'type' => 'notification_type',
            // Additional data...
        ],
        'click_action' => 'ACTION_NAME'
    ];
}
```

5. Include FCM channel in the `via()` method:

```php
public function via($notifiable)
{
    $channels = ['database'];
    
    if ($notifiable->email) {
        $channels[] = 'mail';
    }
    
    if ($notifiable->fcm_token && $notifiable->notifications_enabled) {
        $channels[] = \App\Channels\FcmChannel::class;
    }
    
    return $channels;
}
```

6. Update the relevant controller to trigger the notification

## FCM Setup Requirements

To use FCM in your development or production environment:

1. Create a Firebase project at [firebase.google.com](https://firebase.google.com)
2. Generate a service account key from Project Settings > Service accounts
3. Save the JSON key file in `storage/firebase/service-account.json`
4. Configure environment variables in `.env`:
   ```
   FIREBASE_PROJECT_ID=your-project-id
   FIREBASE_CREDENTIALS=firebase/service-account.json
   FIREBASE_DATABASE_URL=https://your-project-id.firebaseio.com
   ```

## FCM Settings

The application includes a flexible settings system for FCM notifications that allows enabling or disabling different types of notifications at an application level:

| Key | Description |
|-----|-------------|
| `notification_enabled` | Master switch for all FCM notifications |
| `order_notifications` | Control notifications for order status changes |
| `payment_notifications` | Control notifications for payment status changes |
| `promotion_notifications` | Control notifications for promotional messages |

Settings can be managed through the `FcmSetting` model:

```php
// Check if a feature is enabled
$isEnabled = \App\Models\FcmSetting::isEnabled('notification_enabled');

// Get a setting value
$value = \App\Models\FcmSetting::getValue('key', 'default');

// Update a setting
\App\Models\FcmSetting::setValue('key', 'value');

// Get all settings
$allSettings = \App\Models\FcmSetting::getAllSettings();
```

## Database Migrations

The following migrations were created for the FCM integration:

1. `2025_05_12_110052_add_fcm_token_to_users_table.php` - Adds FCM token and notification preference fields to users table
2. `2025_05_12_110358_create_fcm_settings_table.php` - Creates a table for FCM global settings

## Important Code Files

### Core Notification System
- `app/Models/Order.php`: Contains the model events for triggering notifications
- `app/Notifications/OrderStatusChanged.php`: Order status notification class
- `app/Notifications/PaymentStatusChanged.php`: Payment status notification class
- `app/Http/Controllers/NotificationController.php`: Handles notification API endpoints
- `app/Http/Resources/NotificationResource.php`: Formats notification data for API responses
- `database/migrations/2025_05_10_152534_create_notifications_table.php`: Database structure for notifications

### FCM Integration
- `app/Channels/FcmChannel.php`: Custom notification channel for FCM
- `app/Http/Controllers/FcmController.php`: Handles FCM token registration and preferences
- `app/Http/Controllers/FcmTestController.php`: Provides testing endpoints for FCM
- `app/Models/FcmSetting.php`: Model for managing FCM settings
- `database/migrations/2025_05_12_110052_add_fcm_token_to_users_table.php`: Adds FCM fields to users
- `database/migrations/2025_05_12_110358_create_fcm_settings_table.php`: FCM settings table
- `storage/firebase/service-account.json`: Firebase service account credentials

## Next Steps & Future Enhancements

1. **Admin Dashboard Integration**: Create a Filament admin panel for managing FCM notifications and sending broadcasts
2. **Notification Analytics**: Track delivery and open rates for notifications
3. **Scheduled Notifications**: Allow scheduling notifications for future delivery
4. **Topic Subscriptions**: Implement FCM topic subscriptions for targeting user segments
5. **Rich Media Notifications**: Add support for images in notifications
6. **Notification Preferences**: Fine-grained notification preferences per notification type
