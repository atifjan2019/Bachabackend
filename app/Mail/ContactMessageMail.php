<?php

namespace App\Mail;

use App\Mail\Concerns\HasBrandData;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

    public function __construct(public ContactMessage $contact)
    {
    }

    public function envelope(): Envelope
    {
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            // Reply goes straight to the person who contacted us.
            replyTo: $this->contact->email
                ? [new Address($this->contact->email, $this->contact->name)]
                : [],
            subject: 'New contact message: ' . $this->contact->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            with: ['contact' => $this->contact, 'brand' => $this->brand()],
        );
    }
}
