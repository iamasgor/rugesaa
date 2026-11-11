<?php

namespace App\Notifications;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Membership $membership) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your RUGESAA membership ends on '.$this->membership->expires_at?->format('j F Y'))
            ->line('Your '.$this->membership->plan->name.' membership ends on '.$this->membership->expires_at?->format('j F Y').'.')
            ->line('Renew now and the new year starts when the current one ends — you lose no time.')
            ->action('Renew my membership', route('portal'));
    }
}
