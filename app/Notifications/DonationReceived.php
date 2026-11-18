<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The emailed receipt for a confirmed donation.
 */
class DonationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Donation $donation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $donation = $this->donation;
        $transaction = $donation->payments()->latest('id')->value('tran_id');

        return (new MailMessage)
            ->subject('Thank you for your gift to RUGESAA')
            ->greeting('Thank you, '.$donation->name)
            ->line('We received your donation. This email is your receipt.')
            ->line('Amount: BDT '.number_format($donation->amount))
            ->line('Fund: '.$donation->fund->getLabel())
            ->line('Date: '.$donation->paid_at?->format('j M Y'))
            ->line('Transaction: '.($transaction ?? '—'))
            ->line('Your support goes straight to students and the department.');
    }
}
