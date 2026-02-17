<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $userType = $this->user->userType->name ?? 'User';

        return (new MailMessage)
            ->subject('Welcome to LCP VTC!')
            ->greeting('Welcome to LCP VTC, '.$notifiable->first_name.'!')
            ->line('Your account has been successfully created.')
            ->line('Account Type: '.$userType)
            ->line('Email: '.$this->user->email)
            ->line('You can now start using our premium chauffeur services.')
            ->action('Complete Your Profile', url('/profile'))
            ->line('If you have any questions, feel free to contact our support team.')
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
            'user_id' => $this->user->id,
            'user_uuid' => $this->user->uuid,
            'user_type' => $this->user->userType->name ?? 'User',
            'registered_at' => $this->user->created_at,
        ];
    }
}
