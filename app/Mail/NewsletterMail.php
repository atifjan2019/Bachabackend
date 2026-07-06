<?php

namespace App\Mail;

use App\Mail\Concerns\HasBrandData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

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
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            replyTo: $brand['email'] ? [new Address($brand['email'], $brand['from_name'])] : [],
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $brand = $this->brand();

        return new Content(
            view: 'emails.newsletter',
            with: [
                'subject'        => $this->emailSubject,
                'bodyContent'    => $this->bodyContent,
                'email'          => $this->subscriberEmail,
                'brand'          => $brand,
                // Unsubscribe is a customer-facing action → frontend, not the admin domain.
                'unsubscribeUrl' => $brand['site'] . '/newsletter/unsubscribe?email=' . urlencode($this->subscriberEmail),
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
