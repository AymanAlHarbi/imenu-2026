<?php

namespace App\Notifications;

use App\NotificationChannels\Expo\ExpoChannel;
use App\NotificationChannels\Expo\ExpoMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * تمديد وقت الجاهزية — يُبلَّغ العميل به تلقائيًا ويحدّث عدّاده.
 *
 * الإبلاغ شرط عدل لا تحسين: الوعد هو ما يُقاس عليه المقهى، فتمديده بلا إخبار
 * العميل يجعل الوعد بلا معنى، ويترك العميل يصل ولا يجد شيئًا.
 */
class OrderDelayed extends Notification
{
    use Queueable;

    protected $order;

    protected $title;

    protected $body;

    public function __construct($order, $readyAt)
    {
        $this->order = $order;
        $this->title = __('Your order is running a little late').' — #'.$order->id_formated;
        $this->body = __('New ready time').': '.$readyAt;
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
            \Log::error('OrderDelayed expo failed: '.$th->getMessage());
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
