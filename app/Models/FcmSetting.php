<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FcmSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * الحصول على قيمة إعداد محدد
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $cacheKey = 'fcm_setting_' . $key;

        return Cache::remember($cacheKey, 60 * 60, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * تعيين قيمة إعداد
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setValue($key, $value)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            $setting = new static;
            $setting->key = $key;
        }

        $setting->value = $value;
        $result = $setting->save();

        // تحديث الكاش
        if ($result) {
            Cache::put('fcm_setting_' . $key, $value, 60 * 60);
        }

        return $result;
    }

    /**
     * التحقق ما إذا كانت قيمة محددة تساوي true
     *
     * @param string $key
     * @return bool
     */
    public static function isEnabled($key)
    {
        $value = static::getValue($key);
        return $value === 'true' || $value === true || $value === 1 || $value === '1';
    }

    /**
     * الحصول على جميع الإعدادات كمصفوفة
     *
     * @return array
     */
    public static function getAllSettings()
    {
        return Cache::remember('fcm_all_settings', 60 * 60, function () {
            $settings = static::all();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value;
            }

            return $result;
        });
    }

    /**
     * مسح الكاش الخاص بالإعدادات
     */
    public static function clearCache()
    {
        $settings = static::all();

        foreach ($settings as $setting) {
            Cache::forget('fcm_setting_' . $setting->key);
        }

        Cache::forget('fcm_all_settings');
    }
}
