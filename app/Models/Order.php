<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'city',
        'country',
        'items',
        'subtotal',
        'shipping_fee',
        'total_amount',
        'payment_method',
        'payment_receipt',
        'status',
    ];
    
    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /** Statuses that permanently lock an order from further status edits. */
    public const LOCKED_STATUSES = ['Delivered', 'Cancelled'];

    /** Admin comments/notes on this order (most recent first). */
    public function comments()
    {
        return $this->hasMany(OrderComment::class)->latest();
    }

    /** Delivered and Cancelled orders are locked and can no longer be edited. */
    public function isLocked(): bool
    {
        return in_array($this->status, self::LOCKED_STATUSES, true);
    }

    /**
     * Non-sequential public reference (e.g. "BS-482913") shown to customers,
     * so the internal sequential id is never exposed and order volume can't be
     * estimated from it.
     */
    public static function generateReference(): string
    {
        do {
            $ref = 'BS-' . random_int(100000, 999999);
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->reference)) {
                $order->reference = static::generateReference();
            }
        });
    }

    /** Customer-facing order number: the reference, falling back to "#id". */
    public function getRefAttribute(): string
    {
        return $this->reference ?: '#' . $this->id;
    }
}

