<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationNotification extends Notification
{
    use Queueable;

    protected $verificationType;

    protected $status;

    /**
     * Create a new notification instance.
     *
     * @param  string  $verificationType  ('driver', 'company', 'document')
     * @param  string  $status  ('approved', 'rejected', 'pending')
     * @return void
     */
    public function __construct(string $verificationType, string $status)
    {
        $this->verificationType = $verificationType;
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

        if ($this->status === 'approved') {
            $mail->subject('Verification Approved')
                ->line('Congratulations! Your '.$this->verificationType.' verification has been approved.')
                ->line('You can now start using all features available to verified '.$this->verificationType.'s.')
                ->action('Go to Dashboard', url('/dashboard'));
        } elseif ($this->status === 'rejected') {
            $mail->subject('Verification Rejected')
                ->level('error')
                ->line('Unfortunately, your '.$this->verificationType.' verification has been rejected.')
                ->line('Please review the feedback and resubmit your verification.')
                ->action('Review Feedback', url('/verification/feedback'));
        } else {
            $mail->subject('Verification Pending')
                ->line('Your '.$this->verificationType.' verification is currently being reviewed.')
                ->line('We will notify you once the review is complete.')
                ->line('This usually takes 24-48 hours.');
        }

        return $mail->line('Thank you for your patience!');
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
            'verification_type' => $this->verificationType,
            'status' => $this->status,
            'verified_at' => now(),
        ];
    }
}
