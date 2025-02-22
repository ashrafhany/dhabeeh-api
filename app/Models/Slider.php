<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $fillable = ['image', 'title', 'description', 'active'];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function ($query) {
            $query->orderBy('id', 'desc'); // تغيير الترتيب حسب الحاجة
        });
    }
    public function getImageAttribute($value)
{
    return $value ? asset('storage/' . ltrim($value, '/')) : null;
}

}
