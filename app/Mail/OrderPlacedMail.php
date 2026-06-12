<?php

namespace App\Mail;

use App\Mail\Concerns\HasBrandData;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            replyTo: $brand['email'] ? [new Address($brand['email'], $brand['from_name'])] : [],
            subject: 'Your order #' . $this->order->id . ' is confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
            with: ['order' => $this->order, 'brand' => $this->brand()],
        );
    }
}
