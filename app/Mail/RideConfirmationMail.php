<?php

namespace App\Mail;

use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RideConfirmationMail extends Mailable
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
        return $this->subject('Ride Confirmation - LCP VTC')
            ->view('emails.ride-confirmation')
            ->with([
                'ride' => $this->ride,
                'passenger' => $this->ride->passenger,
                'pickup' => $this->ride->pickup_address,
                'dropoff' => $this->ride->dropoff_address,
                'scheduledTime' => $this->ride->scheduled_at,
                'totalPrice' => $this->ride->final_price,
            ]);
    }
}
