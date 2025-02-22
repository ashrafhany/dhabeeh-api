<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'variant_id', 'quantity', 'total_price', 'status'];

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
}
