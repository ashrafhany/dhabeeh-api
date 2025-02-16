<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'quantity', 'total_price', 'status'];

    // ربط الطلب بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ربط الطلب بالمنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // تعريف القيم المسموح بها لحالة الطلب
    public static function getStatuses()
    {
        return ['Pending', 'Current', 'inDelivery', 'Delivered', 'Refused'];
    }
}
