<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentStatusChanged extends Notification
{
    use Queueable;

    protected $order;
    protected $paymentStatus;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->paymentStatus = $order->payment_status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        // إضافة قناة البريد الإلكتروني إذا كان للمستخدم بريد إلكتروني صحيح
        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        // إضافة قناة FCM إذا كان للمستخدم توكن وخاصية الإشعارات مفعلة
        if ($notifiable->fcm_token && $notifiable->notifications_enabled) {
            $channels[] = \App\Channels\FcmChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $statusMessages = [
            'pending' => 'في انتظار الدفع',
            'paid' => 'تم استلام الدفع بنجاح',
            'failed' => 'فشلت عملية الدفع',
            'refunded' => 'تم إرجاع المبلغ المدفوع'
        ];

        $message = $statusMessages[strtolower($this->paymentStatus)] ?? 'تم تحديث حالة الدفع';
        $productName = $this->order->variant->product->name ?? 'منتج';

        $mailMessage = (new MailMessage)
            ->subject('تحديث حالة الدفع للطلب #' . $this->order->id)
            ->greeting('مرحباً ' . $notifiable->first_name)
            ->line($message)
            ->line('تفاصيل الطلب:')
            ->line('رقم الطلب: #' . $this->order->id)
            ->line('المنتج: ' . $productName . ' x ' . $this->order->quantity)
            ->line('المبلغ: ' . $this->order->total_price . ' ريال')
            ->action('عرض تفاصيل الطلب', url('/orders/' . $this->order->id))
            ->line('شكراً لاستخدامك تطبيقنا!');

        if (strtolower($this->paymentStatus) === 'failed') {
            $mailMessage->line('يرجى المحاولة مرة أخرى أو استخدام وسيلة دفع أخرى.');
        } elseif (strtolower($this->paymentStatus) === 'paid') {
            $mailMessage->line('سيتم تحضير طلبك في أقرب وقت.');
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'payment_status' => $this->paymentStatus,
            'payment_method' => $this->order->payment_method,
            'product_name' => $this->order->variant->product->name ?? 'منتج',
            'total_price' => $this->order->total_price,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the firebase cloud messaging representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcm($notifiable)
    {
        $statusMessages = [
            'pending' => 'في انتظار الدفع',
            'paid' => 'تم استلام الدفع بنجاح',
            'failed' => 'فشلت عملية الدفع',
            'refunded' => 'تم إرجاع المبلغ المدفوع'
        ];

        $message = $statusMessages[strtolower($this->paymentStatus)] ?? 'تم تحديث حالة الدفع';
        $productName = $this->order->variant->product->name ?? 'منتج';

        return [
            'title' => 'تحديث حالة الدفع للطلب #' . $this->order->id,
            'body' => $message,
            'data' => [
                'type' => 'payment_status',
                'order_id' => $this->order->id,
                'payment_status' => $this->paymentStatus,
                'payment_method' => $this->order->payment_method,
                'product_name' => $productName,
                'click_action' => 'PAYMENT_DETAILS'
            ],
            'click_action' => 'PAYMENT_DETAILS'
        ];
    }
}
