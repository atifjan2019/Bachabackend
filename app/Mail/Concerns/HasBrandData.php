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

        // Frontend (customer-facing) base URL — e.g. https://www.bachastylo.com.
        // Prefer FRONTEND_URL, then the canonical_base_url admin setting.
        $frontend = config('app.frontend_url')
            ?: ($s['canonical_base_url'] ?? 'https://www.bachastylo.com');
        // FRONTEND_URL may hold a comma-separated CORS allow-list; use the first origin.
        $frontend = rtrim(trim(explode(',', (string) $frontend)[0]), '/');

        // Backend (admin panel) base URL — e.g. https://admin.bachastylo.com.
        $admin = rtrim((string) (config('app.admin_url') ?: config('app.url') ?: 'https://admin.bachastylo.com'), '/');

        return [
            'name' => $s['business_name'] ?? 'Bacha Stylo',
            'from_name' => $s['email_from_name'] ?? ($s['business_name'] ?? 'Bacha Stylo'),
            'email' => $s['business_email'] ?? config('mail.from.address'),
            'phone' => $s['business_phone'] ?? '',
            'whatsapp' => preg_replace('/[^0-9]/', '', (string) ($s['whatsapp_number'] ?? '')),
            'address' => $s['business_address'] ?? '',
            'admin_email' => config('mail.admin_address')
                ?: ($s['order_notification_email'] ?? ($s['business_email'] ?? config('mail.from.address'))),
            // Frontend storefront base (store links, unsubscribe, password reset).
            'site' => $frontend,
            // Admin dashboard order-view base (staff-facing "View in Dashboard").
            'admin_url' => $admin . '/admin/orders',
        ];
    }
}
