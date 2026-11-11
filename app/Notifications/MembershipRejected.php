<?php

namespace App\Notifications;

use App\Models\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipRejected extends Notification implements ShouldQueue
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
            ->subject('About your RUGESAA registration')
            ->line('We could not verify your registration against department records.')
            ->line('Reason: '.$this->membership->rejection_reason)
            ->line('If you think this is a mistake, reply with any document that shows your batch and student ID, or contact us.')
            ->action('Contact the association', route('contact'));
    }
}
