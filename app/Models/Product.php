<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;    protected $fillable = [
        'name',
        'description',
        'image',
        'category_id'
    ];

    /**
     * Cast attributes to their native types
     */
    protected $casts = [
        'image' => 'array',
    ];
    /*
    public function getImageUrl()
{
    return $this->image ? asset('uploads/' . $this->image) : null;
}
    */    public function getImageUrl()
    {
        if (empty($this->image)) {
            return null;
        }

        // تأكد من أن الصورة موجودة في التخزين العام
        $imagePath = null;

        // تعامل مع صيغ الصور المختلفة من Filament 2
        if (is_string($this->image)) {
            // إذا كان النص JSON
            if (str_starts_with($this->image, '[') || str_starts_with($this->image, '{')) {
                $decodedImage = json_decode($this->image, true);
                $imagePath = isset($decodedImage[0]) ? $decodedImage[0] : null;
            } else {
                $imagePath = $this->image;
            }
        } else if (is_array($this->image)) {
            // إذا كان بالفعل مصفوفة
            $imagePath = isset($this->image[0]) ? $this->image[0] : null;
        }

        if (empty($imagePath)) {
            return null;
        }

        // تحقق من وجود الملف
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
            // إذا كان المسار يحتوي على مجلد products سابقاً
            if (!str_starts_with($imagePath, 'products/') && \Illuminate\Support\Facades\Storage::disk('public')->exists('products/' . $imagePath)) {
                $imagePath = 'products/' . $imagePath;
            }
        }

        // إضافة timestamp لتجنب التخزين المؤقت
        $cache_buster = '?v=' . md5($this->updated_at ?? time());

        return asset('storage/' . $imagePath) . $cache_buster;
    }

    /**
     * الحصول على مسار الصورة بدون عنوان URL كامل
     */
    public function getImagePathAttribute()
    {
        if (empty($this->image)) {
            return null;
        }

        if (is_string($this->image)) {
            if (str_starts_with($this->image, '[') || str_starts_with($this->image, '{')) {
                $decodedImage = json_decode($this->image, true);
                return isset($decodedImage[0]) ? $decodedImage[0] : null;
            }
            return $this->image;
        } else if (is_array($this->image)) {
            return isset($this->image[0]) ? $this->image[0] : null;
        }

        return null;
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
