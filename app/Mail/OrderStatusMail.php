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

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels, HasBrandData;

    public function __construct(public Order $order, public string $status)
    {
    }

    /**
     * Status-specific copy + badge colour for the email.
     */
    public function statusInfo(): array
    {
        $ref = $this->order->ref;

        return match (strtolower($this->status)) {
            'paid' => [
                'subject' => 'Payment received for order ' . $ref,
                'title' => 'Payment received',
                'message' => "We've received payment for your order. It is now being prepared for dispatch.",
                'badge' => '#1f9d55',
            ],
            'processing' => [
                'subject' => 'Your order ' . $ref . ' is being prepared',
                'title' => 'Your order is being prepared',
                'message' => 'Good news — your order is now being processed and will be on its way soon.',
                'badge' => '#141414',
            ],
            'shipped' => [
                'subject' => 'Your order ' . $ref . ' is on the way',
                'title' => 'Your order is on the way',
                'message' => 'Your order has been shipped and is on its way to you. Thank you for your patience.',
                'badge' => '#2563eb',
            ],
            'delivered' => [
                'subject' => 'Your order ' . $ref . ' has been delivered',
                'title' => 'Your order has been delivered',
                'message' => 'Your order has been delivered. We hope you love it — thank you for choosing us.',
                'badge' => '#1f9d55',
            ],
            'cancelled' => [
                'subject' => 'Your order ' . $ref . ' has been cancelled',
                'title' => 'Your order has been cancelled',
                'message' => 'Your order has been cancelled. If this was unexpected or you have any questions, please get in touch and we will be glad to help.',
                'badge' => '#e81d25',
            ],
            default => [
                'subject' => 'Update on your order ' . $ref,
                'title' => 'There is an update on your order',
                'message' => 'The status of your order has been updated. The latest details are below.',
                'badge' => '#141414',
            ],
        };
    }

    public function envelope(): Envelope
    {
        $brand = $this->brand();

        return new Envelope(
            from: new Address(config('mail.from.address'), $brand['from_name']),
            replyTo: $brand['email'] ? [new Address($brand['email'], $brand['from_name'])] : [],
            subject: $this->statusInfo()['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
            with: [
                'order' => $this->order,
                'brand' => $this->brand(),
                'status' => $this->status,
                'info' => $this->statusInfo(),
            ],
        );
    }
}
