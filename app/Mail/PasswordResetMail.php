<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $token;

    public $resetUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
        $this->resetUrl = url('/reset-password?token='.$token.'&email='.urlencode($user->email));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reset Your Password - LCP VTC')
            ->view('emails.password-reset')
            ->with([
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'expiresIn' => config('auth.passwords.users.expire', 60),
            ]);
    }
}
