<?php

namespace App\Notifications;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverAssignedNotification extends Notification
{
    use Queueable;

    protected $ride;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ride $ride)
    {
        $this->ride = $ride;
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
        $driver = $this->ride->driver;
        $vehicle = $this->ride->vehicle;

        return (new MailMessage)
            ->subject('Driver Assigned to Your Ride')
            ->greeting('Hello '.$notifiable->first_name.'!')
            ->line('A driver has been assigned to your ride.')
            ->line('Driver: '.$driver->first_name.' '.$driver->last_name)
            ->line('Rating: '.number_format($driver->rating, 1).'/5.0 ('.$driver->total_reviews.' reviews)')
            ->line('Vehicle: '.($vehicle->brand ?? 'N/A').' '.($vehicle->model ?? 'N/A'))
            ->line('License Plate: '.($vehicle->license_plate ?? 'N/A'))
            ->line('Pickup Time: '.$this->ride->scheduled_at->format('F d, Y \a\t g:i A'))
            ->action('View Ride Details', url('/api/v1/rides/'.$this->ride->uuid))
            ->line('Your driver will contact you shortly.');
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
            'driver_id' => $this->ride->driver_id,
            'driver_name' => $this->ride->driver->first_name.' '.$this->ride->driver->last_name,
            'vehicle_info' => $this->ride->vehicle ?
                ($this->ride->vehicle->brand.' '.$this->ride->vehicle->model) : null,
            'scheduled_at' => $this->ride->scheduled_at,
        ];
    }
}
