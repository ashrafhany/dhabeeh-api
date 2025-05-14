<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    /**
     * الصفات التي يمكن تعيينها بشكل جماعي
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'body',
        'target_type', // all, specific, topic
        'topic',
        'sent_count',
        'admin_id',
        'data',
    ];

    /**
     * الصفات التي يجب تحويلها إلى أنواع محددة
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * علاقة مع المدير الذي أرسل الإشعار
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
