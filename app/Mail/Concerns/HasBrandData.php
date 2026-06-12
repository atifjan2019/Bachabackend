<?php

namespace App\Mail\Concerns;

use App\Models\Setting;

trait HasBrandData
{
    /**
     * Build the brand context shared across all order emails,
     * sourced from admin Settings with sensible fallbacks.
     */
    protected function brand(): array
    {
        $keys = [
            'business_name',
            'business_email',
            'business_phone',
            'whatsapp_number',
            'business_address',
            'email_from_name',
            'order_notification_email',
            'canonical_base_url',
        ];

        $s = Setting::whereIn('setting_key', $keys)->pluck('setting_value', 'setting_key');

        $site = $s['canonical_base_url'] ?? 'https://bachastylo.pk';

        return [
            'name' => $s['business_name'] ?? 'Bacha Stylo',
            'from_name' => $s['email_from_name'] ?? ($s['business_name'] ?? 'Bacha Stylo'),
            'email' => $s['business_email'] ?? config('mail.from.address'),
            'phone' => $s['business_phone'] ?? '',
            'whatsapp' => preg_replace('/[^0-9]/', '', (string) ($s['whatsapp_number'] ?? '')),
            'address' => $s['business_address'] ?? '',
            'admin_email' => config('mail.admin_address')
                ?: ($s['order_notification_email'] ?? ($s['business_email'] ?? config('mail.from.address'))),
            'site' => rtrim($site, '/'),
            'admin_url' => rtrim((string) config('app.url'), '/') . '/admin/orders',
        ];
    }
}
