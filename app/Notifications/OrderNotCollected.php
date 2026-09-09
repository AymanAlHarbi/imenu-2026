<?php

namespace App\Notifications;

use App\NotificationChannels\Expo\ExpoChannel;
use App\NotificationChannels\Expo\ExpoMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * إشعار العميل بأن طلبه سُجّل «لم يُستلم».
 *
 * الإشعار متطلَّب لا تحسين: بلا إبلاغ العميل بالسبب، لا معنى لحق الاعتراض،
 * والحظر يصير عقوبة بلا بيان — وهو ما يمنعه نظام حماية البيانات (PDPL).
 */
class OrderNotCollected extends Notification
{
    use Queueable;

    protected $order;

    protected $title;

    protected $body;

    public function __construct($order)
    {
        $this->order = $order;
        $this->title = __('Order not collected').' — #'.$order->id_formated;
        $this->body = __('The coffee shop marked your order as not collected. If this is a mistake, you can object from your orders page.');
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable != null && strlen($notifiable->expotoken.'') > 3) {
            array_push($channels, ExpoChannel::class);
        }

        return $channels;
    }

    public function toExpo($notifiable)
    {
        try {
            return ExpoMessage::create()
                ->title($this->title)
                ->body($this->body)
                ->badge(1);
        } catch (\Throwable $th) {
            \Log::error('OrderNotCollected expo failed: '.$th->getMessage());
        }
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
