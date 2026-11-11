<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EventRegistration $registration) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->registration->event;

        $mail = (new MailMessage)
            ->subject("You're registered: ".$event->title)
            ->greeting("You're going to {$event->title}")
            ->line('When: '.$event->whenLabel())
            ->line('Where: '.$event->venue);

        if ($this->registration->guests > 0) {
            $mail->line('Guests: '.$this->registration->guests);
        }

        if ($this->registration->amount > 0) {
            $mail->line('Paid: BDT '.number_format($this->registration->amount));
        }

        if ($event->online_url) {
            $mail->line('Joining link: '.$event->online_url);
        }

        return $mail
            ->action('View event', route('events.show', $event))
            ->line('Add it to your calendar from the event page.');
    }
}
