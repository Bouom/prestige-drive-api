<?php

namespace App\Notifications;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RideStatusNotification extends Notification
{
    use Queueable;

    protected $ride;

    protected $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, string $status)
    {
        $this->ride = $ride;
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
        return (new MailMessage)
            ->subject('Ride Status Update - '.ucfirst($this->status))
            ->greeting('Hello '.$notifiable->first_name.'!')
            ->line('Your ride status has been updated to: '.ucfirst($this->status))
            ->line('Pickup: '.$this->ride->pickup_address)
            ->line('Destination: '.$this->ride->dropoff_address)
            ->action('View Ride Details', url('/api/v1/rides/'.$this->ride->uuid))
            ->line('Thank you for choosing LCP VTC!');
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
            'ride_id' => $this->ride->id,
            'ride_uuid' => $this->ride->uuid,
            'status' => $this->status,
            'pickup_address' => $this->ride->pickup_address,
            'dropoff_address' => $this->ride->dropoff_address,
            'scheduled_at' => $this->ride->scheduled_at,
        ];
    }
}
