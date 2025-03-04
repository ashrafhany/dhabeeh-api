<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'discount', 'expires_at'];

    public function isValid()
    {
        return !$this->expires_at || $this->expires_at > now();
    }
    public function calculateDiscount($totalOrderPrice)
{
    if ($this->type === 'percentage') {
        return $totalOrderPrice * ($this->discount / 100);
    } elseif ($this->type === 'fixed') {
        return min($this->discount, $totalOrderPrice);
    }
    return 0;
}


}
