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

class OrderCommentMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

    public function __construct(public Order $order, public string $comment)
    {
    }

    public function envelope(): Envelope
    {
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            replyTo: $brand['email'] ? [new Address($brand['email'], $brand['from_name'])] : [],
            subject: 'A message about your order ' . ($this->order->reference ?: '#' . $this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-comment',
            with: [
                'order' => $this->order,
                'brand' => $this->brand(),
                'comment' => $this->comment,
            ],
        );
    }
}
