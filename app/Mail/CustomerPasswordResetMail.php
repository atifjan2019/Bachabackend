<?php

namespace App\Mail;

use App\Mail\Concerns\HasBrandData;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

    public function __construct(public Customer $customer, public string $resetUrl)
    {
    }

    public function envelope(): Envelope
    {
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            replyTo: $brand['email'] ? [new Address($brand['email'], $brand['from_name'])] : [],
            subject: 'Reset your ' . $brand['name'] . ' password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-password-reset',
            with: [
                'customer' => $this->customer,
                'resetUrl' => $this->resetUrl,
                'brand' => $this->brand(),
            ],
        );
    }
}
