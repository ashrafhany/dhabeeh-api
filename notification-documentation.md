# Notification System Documentation

## Overview
This document outlines the notification system implemented in the Dhabeeh API project. The system handles notifications for order status changes and payment status changes.

## Available Notification Types

1. **Order Status Change Notifications**
   - Triggered when an order's status changes (e.g., pending → current → inDelivery → delivered)
   - Managed by `App\Notifications\OrderStatusChanged` class

2. **Payment Status Change Notifications**
   - Triggered when a payment's status changes (e.g., pending → paid or failed)
   - Managed by `App\Notifications\PaymentStatusChanged` class

## Notification Channels

Notifications are delivered via:
- **Database**: All notifications are stored in the database
- **Email**: All notifications are also sent via email

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

### Testing Endpoints (For Development Only)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/notifications/test/order-status` | POST | Send a test order status notification |
| `/api/notifications/test/payment-status` | POST | Send a test payment status notification |

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
4. Update the relevant controller to trigger the notification

## Important Code Files

- `app/Models/Order.php`: Contains the model events for triggering notifications
- `app/Notifications/OrderStatusChanged.php`: Order status notification class
- `app/Notifications/PaymentStatusChanged.php`: Payment status notification class
- `app/Http/Controllers/NotificationController.php`: Handles notification API endpoints
- `app/Http/Resources/NotificationResource.php`: Formats notification data for API responses
- `database/migrations/2025_05_10_152534_create_notifications_table.php`: Database structure for notifications
