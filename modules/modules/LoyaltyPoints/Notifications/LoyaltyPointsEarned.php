<?php

namespace Modules\LoyaltyPoints\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\LoyaltyPoints\Models\LoyaltyAccount;
use Modules\LoyaltyPoints\Models\LoyaltyTransaction;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class LoyaltyPointsEarned extends Notification
{
    use Queueable;

    protected LoyaltyAccount $account;

    protected LoyaltyTransaction $transaction;

    public function __construct(LoyaltyAccount $account, LoyaltyTransaction $transaction)
    {
        $this->account = $account;
        $this->transaction = $transaction;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (config('settings.onesignal_app_id')) {
            $channels[] = OneSignalChannel::class;
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->transaction->type === 'welcome'
                ? __('loyalty.notification_welcome_title')
                : __('loyalty.notification_points_title'),
            'body' => __('loyalty.notification_points_body', [
                'points' => $this->transaction->points,
                'balance' => $this->account->points_balance,
                'vendor' => $this->account->restorant->name ?? '',
            ]),
        ];
    }

    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject(__('loyalty.notification_points_title'))
            ->setBody(__('loyalty.notification_points_body', [
                'points' => $this->transaction->points,
                'balance' => $this->account->points_balance,
                'vendor' => $this->account->restorant->name ?? '',
            ]));
    }
}
