<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DriverVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $driver;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Driver Account Verified - LCP VTC')
            ->view('emails.driver-verified')
            ->with([
                'driver' => $this->driver,
                'profile' => $this->driver->driverProfile,
                'verifiedAt' => now(),
            ]);
    }
}
