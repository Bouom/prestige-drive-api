<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Payment Receipt - LCP VTC')
            ->view('emails.payment-receipt')
            ->with([
                'payment' => $this->payment,
                'payable' => $this->payment->payable,
                'amount' => $this->payment->amount,
                'currency' => $this->payment->currency,
                'paidAt' => $this->payment->paid_at,
                'paymentMethod' => $this->payment->paymentMethod,
            ]);
    }
}
