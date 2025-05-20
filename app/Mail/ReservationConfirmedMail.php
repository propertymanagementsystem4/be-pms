<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    /**
     * Create a new message instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        $html = "
            <h1>Reservation Confirmed</h1>
            <p>Thank you for your reservation!</p>
            <p><strong>Invoice Number:</strong> {$this->reservation->invoice_number}</p>
            <p><strong>Check-in Date:</strong> {$this->reservation->check_in_date}</p>
            <p><strong>Check-out Date:</strong> {$this->reservation->check_out_date}</p>
            <p><strong>Total Price:</strong> {$this->reservation->total_price}</p>
        ";

        return $this
            ->subject('Your Reservation is Confirmed')
            ->html($html);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
