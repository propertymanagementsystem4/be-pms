<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verifyUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($verifyUrl)
    {
        $this->verifyUrl = $verifyUrl;
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->html($this->buildHtml());
    }

    protected function buildHtml(): string
    {
        $url = $this->verifyUrl;

        return <<<HTML
            <html>
                <body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
                    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <h2 style="color: #333;">Verify Your Email Address</h2>
                        <p>Thank you for registering. Please click the button below to verify your email address:</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="$url" style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 4px;">Verify Email</a>
                        </div>
                        <p>If you didn't create an account, no further action is required.</p>
                        <hr>
                        <p style="font-size: 12px; color: #999;">If you're having trouble clicking the \"Verify Email\" button, copy and paste the URL below into your web browser:<br><a href="$url">$url</a></p>
                    </div>
                </body>
            </html>
        HTML;
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
