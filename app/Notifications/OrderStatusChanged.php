<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    protected $order;
    protected $previousStatus;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Order $order
     * @param string|null $previousStatus
     * @return void
     */
    public function __construct($order, $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
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
            'pending' => 'تم استلام طلبك وهو قيد المعالجة',
            'current' => 'جاري تحضير طلبك',
            'inDelivery' => 'طلبك في طريقه إليك الآن',
            'delivered' => 'تم توصيل طلبك بنجاح',
            'refused' => 'تم رفض الطلب'
        ];

        $message = $statusMessages[strtolower($this->order->status)] ?? 'تم تغيير حالة طلبك';
        $productName = $this->order->variant->product->name ?? 'منتج';
        $totalPrice = $this->order->total_price;

        return (new MailMessage)
                    ->subject('تحديث حالة الطلب #' . $this->order->id)
                    ->greeting('مرحباً ' . $notifiable->first_name)
                    ->line($message)
                    ->line('تفاصيل الطلب:')
                    ->line('رقم الطلب: #' . $this->order->id)
                    ->line('المنتج: ' . $productName . ' x ' . $this->order->quantity)
                    ->line('السعر الإجمالي: ' . $totalPrice . ' ريال')
                    ->action('عرض تفاصيل الطلب', url('/orders/' . $this->order->id))
                    ->line('شكراً لاستخدامك تطبيقنا!');
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
            'status' => $this->order->status,
            'previous_status' => $this->previousStatus,
            'product_name' => $this->order->variant->product->name ?? 'منتج',
            'quantity' => $this->order->quantity,
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
}
