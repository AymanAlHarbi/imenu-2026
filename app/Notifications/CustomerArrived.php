<?php

namespace App\Notifications;

use App\NotificationChannels\Expo\ExpoChannel;
use App\NotificationChannels\Expo\ExpoMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerArrived extends Notification
{
    use Queueable;

    protected $order;

    protected $title;

    protected $body;

    public function __construct($order)
    {
        $this->order = $order;
        $this->title = __('Customer has arrived').' — '.__('Order').' #'.$order->id_formated;

        $vehicle = trim($order->getConfig('vehicle_brand', '').' '.$order->getConfig('vehicle_model', ''));
        $parts = array_filter([
            $vehicle,
            $order->getConfig('vehicle_color', ''),
            $order->getConfig('vehicle_plate', ''),
        ]);
        $this->body = count($parts) > 0 ? implode(' · ', $parts) : __('From my car');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        $notificationClasses = ['database'];
        if ($notifiable != null && strlen($notifiable->expotoken.'') > 3) {
            array_push($notificationClasses, ExpoChannel::class);
        }

        return $notificationClasses;
    }

    public function toExpo($notifiable)
    {
        try {
            return ExpoMessage::create()
                ->title($this->title)
                ->body($this->body)
                ->badge(1);
        } catch (\Throwable $th) {
            \Log::error('CustomerArrived expo failed: '.$th->getMessage());
        }
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
