<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartOption extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'option_id', 'quantity', 'total_price'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function option()
    {
        return $this->belongsTo(ProductOption::class);
    }
}

