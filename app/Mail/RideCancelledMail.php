<?php

namespace App\Mail;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RideCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ride;

    public $recipient; // 'passenger' or 'driver'

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Ride $ride, string $recipient = 'passenger')
    {
        $this->ride = $ride;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Ride Cancelled - LCP VTC')
            ->view('emails.ride-cancelled')
            ->with([
                'ride' => $this->ride,
                'passenger' => $this->ride->passenger,
                'driver' => $this->ride->driver,
                'cancellationReason' => $this->ride->cancellation_reason,
                'cancelledBy' => $this->ride->cancelled_by,
                'recipient' => $this->recipient,
                'refundAmount' => $this->ride->refund_amount ?? 0,
            ]);
    }
}
