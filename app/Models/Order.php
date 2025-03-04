<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'variant_id', 'quantity', 'total_price', 'status','discount_amount','shipping_address','notes'];

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
}

}
