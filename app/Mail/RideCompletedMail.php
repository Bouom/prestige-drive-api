<?php

namespace App\Mail;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RideCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ride;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Ride $ride)
    {
        $this->ride = $ride;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Ride Completed - Thank You! - LCP VTC')
            ->view('emails.ride-completed')
            ->with([
                'ride' => $this->ride,
                'passenger' => $this->ride->passenger,
                'driver' => $this->ride->driver,
                'duration' => $this->ride->actual_duration,
                'distance' => $this->ride->actual_distance,
                'finalPrice' => $this->ride->final_price,
                'payment' => $this->ride->payment,
            ]);
    }
}
