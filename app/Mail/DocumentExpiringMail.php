<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Document $document, User $user)
    {
        $this->document = $document;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $daysUntilExpiry = now()->diffInDays($this->document->expiry_date);

        return $this->subject('Document Expiring Soon - LCP VTC')
            ->view('emails.document-expiring')
            ->with([
                'document' => $this->document,
                'user' => $this->user,
                'documentType' => $this->document->documentType,
                'expiryDate' => $this->document->expiry_date,
                'daysUntilExpiry' => $daysUntilExpiry,
            ]);
    }
}
