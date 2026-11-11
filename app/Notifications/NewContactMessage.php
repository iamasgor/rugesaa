<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->contactMessage;

        return (new MailMessage)
            ->subject('[RUGESAA] '.$message->topic->getLabel().': '.$message->subject)
            ->replyTo($message->email, $message->name)
            ->greeting('New message from the website')
            ->line('From: '.$message->name.' <'.$message->email.'>'.($message->batch ? ' · Batch '.$message->batch : ''))
            ->line('Topic: '.$message->topic->getLabel())
            ->line($message->message)
            ->action('Open in admin', url('/admin/contact-messages/'.$message->id));
    }
}
