<?php

namespace App\Notifications;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipActivated extends Notification implements ShouldQueue
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
        $membership = $this->membership;
        $validity = $membership->expires_at
            ? 'It is valid until '.$membership->expires_at->format('j F Y').'.'
            : 'It is a life membership and never needs renewing.';

        return (new MailMessage)
            ->subject($membership->isRenewal() ? 'Your RUGESAA membership is renewed' : 'Welcome to RUGESAA — your membership is active')
            ->greeting($membership->isRenewal() ? 'Thanks for renewing' : 'Welcome to the network')
            ->line('Your '.$membership->plan->name.' membership is active. '.$validity)
            ->line('You can now search the alumni directory and register for events.')
            ->action('Open the members portal', route('portal'));
    }
}
