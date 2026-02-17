<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    protected $payment;

    protected $status;

    /**
     * Create a new notification instance.
     *
     * @param  string  $status  ('success' or 'failed')
     * @return void
     */
    public function __construct(Payment $payment, string $status)
    {
        $this->payment = $payment;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->greeting('Hello '.$notifiable->first_name.'!');

        if ($this->status === 'success') {
            $mail->subject('Payment Successful')
                ->line('Your payment of '.number_format($this->payment->amount, 2).' '.strtoupper($this->payment->currency).' was successful.')
                ->line('Transaction ID: '.$this->payment->stripe_payment_intent_id)
                ->action('View Receipt', url('/api/v1/payments/'.$this->payment->uuid));
        } else {
            $mail->subject('Payment Failed')
                ->level('error')
                ->line('Your payment of '.number_format($this->payment->amount, 2).' '.strtoupper($this->payment->currency).' could not be processed.')
                ->line('Reason: '.($this->payment->failure_reason ?? 'Unknown error'))
                ->action('Try Again', url('/payments/retry/'.$this->payment->uuid));
        }

        return $mail->line('Thank you for using LCP VTC!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'payment_id' => $this->payment->id,
            'payment_uuid' => $this->payment->uuid,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'status' => $this->status,
            'failure_reason' => $this->payment->failure_reason,
        ];
    }
}
