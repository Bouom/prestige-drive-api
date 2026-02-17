<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewReceivedNotification extends Notification
{
    use Queueable;

    protected $review;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
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
        return (new MailMessage)
            ->subject('New Review Received')
            ->greeting('Hello '.$notifiable->first_name.'!')
            ->line('You have received a new review!')
            ->line('Overall Rating: '.number_format($this->review->rating_overall, 1).'/5.0')
            ->line('Comment: '.($this->review->comment ?? 'No comment provided'))
            ->line('Reviewer: '.$this->review->reviewer->first_name)
            ->action('View Review', url('/api/v1/reviews/'.$this->review->uuid))
            ->line('Thank you for providing excellent service!');
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
            'review_id' => $this->review->id,
            'review_uuid' => $this->review->uuid,
            'ride_id' => $this->review->ride_id,
            'reviewer_id' => $this->review->reviewer_id,
            'reviewer_name' => $this->review->reviewer->first_name.' '.$this->review->reviewer->last_name,
            'rating_overall' => $this->review->rating_overall,
            'comment' => $this->review->comment,
        ];
    }
}
