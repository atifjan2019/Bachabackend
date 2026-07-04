<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewsletterMail extends Mailable
{
    public $emailSubject;
    public $bodyContent;
    public $subscriberEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $emailSubject, string $bodyContent, string $subscriberEmail)
    {
        $this->emailSubject = $emailSubject;
        $this->bodyContent = $bodyContent;
        $this->subscriberEmail = $subscriberEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'bodyContent' => $this->bodyContent,
                'email' => $this->subscriberEmail,
            ],
        );
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
