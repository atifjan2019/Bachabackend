@php
    $orderItems = is_array($order->items) ? $order->items : [];
    $shippingLabel = (float) $order->shipping_fee > 0
        ? 'Rs. ' . number_format((float) $order->shipping_fee)
        : 'Free';
@endphp

{{-- Items --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0;">
    <tr>
        <td style="padding:0 0 10px; border-bottom:2px solid #141414; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Item</td>
        <td align="center" style="padding:0 0 10px; border-bottom:2px solid #141414; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Qty</td>
        <td align="right" style="padding:0 0 10px; border-bottom:2px solid #141414; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#6b6b6b;">Price</td>
    </tr>
    @foreach($orderItems as $item)
        @php
            $itemName = $item['name'] ?? 'Item';
            $itemQty = (int) ($item['quantity'] ?? 1);
            $itemTotal = (float) ($item['price'] ?? 0) * $itemQty;
            $metaParts = [];
            if (! empty($item['size'])) { $metaParts[] = 'Size: ' . $item['size']; }
            if (! empty($item['color'])) { $metaParts[] = $item['color']; }
            $itemMeta = implode(' · ', $metaParts);
        @endphp
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #e6e6e8; font-size:14px; color:#141414;">
                {{ $itemName }}
                @if($itemMeta !== '')
                    <br><span style="font-size:12px; color:#6b6b6b;">{{ $itemMeta }}</span>
                @endif
            </td>
            <td align="center" style="padding:12px 0; border-bottom:1px solid #e6e6e8; font-size:14px; color:#6b6b6b;">{{ $itemQty }}</td>
            <td align="right" style="padding:12px 0; border-bottom:1px solid #e6e6e8; font-size:14px; color:#141414;">Rs. {{ number_format($itemTotal) }}</td>
        </tr>
    @endforeach
</table>

{{-- Totals --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0 0;">
    <tr>
        <td style="padding:3px 0; font-size:14px; color:#6b6b6b;">Subtotal</td>
        <td align="right" style="padding:3px 0; font-size:14px; color:#141414;">Rs. {{ number_format((float) $order->subtotal) }}</td>
    </tr>
    <tr>
        <td style="padding:3px 0; font-size:14px; color:#6b6b6b;">Shipping</td>
        <td align="right" style="padding:3px 0; font-size:14px; color:#141414;">{{ $shippingLabel }}</td>
    </tr>
    <tr>
        <td style="padding:12px 0 0; border-top:2px solid #141414; font-size:16px; font-weight:bold; color:#141414;">Total</td>
        <td align="right" style="padding:12px 0 0; border-top:2px solid #141414; font-size:16px; font-weight:bold; color:#e81d25;">Rs. {{ number_format((float) $order->total_amount) }}</td>
    </tr>
</table>
