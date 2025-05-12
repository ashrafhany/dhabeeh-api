<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'variant_id', 'quantity', 'total_price', 'status', 'previous_status', 'discount_amount','shipping_address','notes','payment_method','payment_status', 'previous_payment_status', 'tap_id','payment_url'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }


    // ربط الطلب بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // تعريف القيم المسموح بها لحالة الطلب
    public static function getStatuses()
    {
        return ['Pending', 'Current', 'inDelivery', 'Delivered', 'Refused'];
    }
    protected static function booted()
    {
        static::deleting(function ($order) {
            if ($order->variant) {
                $order->variant->increment('stock', $order->quantity);
            }
        });

        static::updating(function ($order) {
            $originalOrder = Order::find($order->id);

            // Check if status has changed
            if ($originalOrder && $order->status !== $originalOrder->status) {
                $order->previous_status = $originalOrder->status;
            }

            // Check if payment status has changed
            if ($originalOrder && $order->payment_status !== $originalOrder->payment_status) {
                $order->previous_payment_status = $originalOrder->payment_status;
            }
        });

        static::updated(function ($order) {
            // Handle status change notifications
            if (isset($order->previous_status) && $order->previous_status !== $order->status) {
                if ($order->user) {
                    $order->user->notify(new \App\Notifications\OrderStatusChanged($order, $order->previous_status));
                }
            }

            // Handle payment status change notifications
            if (isset($order->previous_payment_status) && $order->previous_payment_status !== $order->payment_status) {
                if ($order->user) {
                    $order->user->notify(new \App\Notifications\PaymentStatusChanged($order));
                }
            }
        });
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
