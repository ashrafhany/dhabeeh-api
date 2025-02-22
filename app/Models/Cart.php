<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'variant_id', 'quantity', 'total_price','discount_amount'];


    public function variant()
{
    return $this->belongsTo(ProductVariant::class, 'variant_id');
}
public function product()
{
    return $this->variant->belongsTo(Product::class, 'product_id');
}


    public function options()
    {
        return $this->hasMany(CartOption::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function coupon()
{
    return $this->belongsTo(Coupon::class, 'coupon_id');
}

}
