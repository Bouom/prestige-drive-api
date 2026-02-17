<?php

namespace App\Mail;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RideAssignedMail extends Mailable
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
        $subject = $this->recipient === 'driver'
            ? 'New Ride Assignment - LCP VTC'
            : 'Driver Assigned to Your Ride - LCP VTC';

        return $this->subject($subject)
            ->view('emails.ride-assigned')
            ->with([
                'ride' => $this->ride,
                'passenger' => $this->ride->passenger,
                'driver' => $this->ride->driver,
                'vehicle' => $this->ride->vehicle,
                'recipient' => $this->recipient,
            ]);
    }
}
