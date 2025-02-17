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
    'price',
    'category_id',
    'weight',
    'stock',
    'options'
    ];
    protected $casts = [
        'options' => 'array' // ✅ تحويل البيانات تلقائيًا من JSON إلى Array
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);

    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
