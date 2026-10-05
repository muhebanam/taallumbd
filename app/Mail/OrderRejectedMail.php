<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "পেমেন্ট তথ্য আপডেট সংক্রান্ত নোটিশ (অর্ডার #{$this->order->id}) — আত-তাআল্লুম",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-rejected',
        );
    }
}
