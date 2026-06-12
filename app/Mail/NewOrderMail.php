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

class NewOrderMail extends Mailable
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
            subject: 'New order #' . $this->order->id . ' — Rs. ' . number_format((float) $this->order->total_amount),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-admin',
            with: ['order' => $this->order, 'brand' => $this->brand()],
        );
    }
}
