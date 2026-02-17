<?php

namespace App\Mail;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $refund;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Refund Processed - LCP VTC')
            ->view('emails.refund-processed')
            ->with([
                'refund' => $this->refund,
                'payment' => $this->refund->payment,
                'amount' => $this->refund->amount,
                'currency' => $this->refund->currency,
                'reason' => $this->refund->reason,
                'processedAt' => $this->refund->processed_at,
            ]);
    }
}
