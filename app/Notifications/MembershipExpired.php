<?php

namespace App\Notifications;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpired extends Notification implements ShouldQueue
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
            ->subject('Your RUGESAA membership has ended')
            ->line('Your '.$this->membership->plan->name.' membership ended on '.$this->membership->expires_at?->format('j F Y').'.')
            ->line('Renew from the members portal to get back into the alumni directory and event registration. You will not need to be verified again.')
            ->action('Renew my membership', route('portal'));
    }
}
