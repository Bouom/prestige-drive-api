<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification
{
    use Queueable;

    protected $document;

    protected $daysUntilExpiry;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Document $document, int $daysUntilExpiry)
    {
        $this->document = $document;
        $this->daysUntilExpiry = $daysUntilExpiry;
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
        $urgency = $this->daysUntilExpiry <= 7 ? 'urgent' : 'important';

        return (new MailMessage)
            ->subject('Document Expiring Soon - Action Required')
            ->level($urgency)
            ->greeting('Hello '.$notifiable->first_name.'!')
            ->line('Your '.$this->document->documentType->name.' is expiring in '.$this->daysUntilExpiry.' days.')
            ->line('Expiry Date: '.$this->document->expiry_date->format('F d, Y'))
            ->line('Please upload a new version to avoid service interruption.')
            ->action('Upload Document', url('/documents/upload'))
            ->line('Thank you for keeping your documents up to date.');
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
            'document_id' => $this->document->id,
            'document_uuid' => $this->document->uuid,
            'document_type' => $this->document->documentType->name,
            'expiry_date' => $this->document->expiry_date->toDateString(),
            'days_until_expiry' => $this->daysUntilExpiry,
        ];
    }
}
