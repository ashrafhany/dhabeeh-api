<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable =
    ['name',
    'description',
    'image',
    'category_id'
    ];
    /*
    public function getImageUrl()
{
    return $this->image ? asset('uploads/' . $this->image) : null;
}
    */
    public function getImageUrl()
{
    return $this->image ? asset('storage/' . $this->image) : null;
}

    public function category()
    {
        return $this->belongsTo(Category::class);

    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }
}
