<?php

namespace App\Console\Commands;

use App\Mail\ContactMessageMail;
use App\Mail\CustomerPasswordResetMail;
use App\Mail\NewOrderMail;
use App\Mail\NewsletterMail;
use App\Mail\OrderCommentMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusMail;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one of each transactional email to an address you control, using
 * existing records (nothing is created or changed), so mail delivery can be
 * confirmed end to end after changing the mail configuration:
 *
 *   php artisan mail:test you@example.com
 *   php artisan mail:test you@example.com --only=order-placed,password-reset
 */
class MailTestCommand extends Command
{
    protected $signature = 'mail:test {to : Address that receives every test email}
                            {--only= : Comma-separated subset of: order-placed, new-order, order-status, order-comment, password-reset, contact, newsletter}';

    protected $description = 'Send every transactional email to one address to verify mail delivery';

    public function handle(): int
    {
        $to = (string) $this->argument('to');
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("'$to' is not a valid email address.");

            return self::FAILURE;
        }

        $only = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        $wanted = fn (string $key) => $only === [] || in_array($key, $only, true);

        $this->line('Mailer: '.config('mail.default').' via '.config('mail.mailers.'.config('mail.default').'.host', '-')
            .' | From: '.config('mail.from.address').' ('.config('mail.from.name').')'
            .' | Admin inbox: '.(config('mail.admin_address') ?: '-'));

        $order = Order::query()->latest('id')->first();
        if (! $order) {
            $this->warn('No orders in the database: order emails will be skipped.');
        }

        $customer = Customer::query()->latest('id')->first() ?? new Customer(['name' => 'Test Customer', 'email' => $to]);

        $jobs = [
            'order-placed' => fn () => $order ? new OrderPlacedMail($order) : null,
            'new-order' => fn () => $order ? new NewOrderMail($order) : null,
            'order-status' => fn () => $order ? new OrderStatusMail($order, 'Shipped') : null,
            'order-comment' => fn () => $order ? new OrderCommentMail($order, 'This is a test message from the admin panel.') : null,
            'password-reset' => fn () => new CustomerPasswordResetMail($customer, url('/reset-password?token=test&email='.urlencode($customer->email))),
            'contact' => fn () => new ContactMessageMail(new ContactMessage([
                'name' => 'Test Visitor', 'email' => $to, 'subject' => 'Mail delivery test', 'message' => 'Sent by php artisan mail:test.',
            ])),
            'newsletter' => fn () => new NewsletterMail('Mail delivery test', '<p>Sent by <strong>php artisan mail:test</strong>.</p>', $to),
        ];

        $sent = 0;
        $failed = 0;
        foreach ($jobs as $key => $make) {
            if (! $wanted($key)) {
                continue;
            }
            $mailable = $make();
            if ($mailable === null) {
                $this->line("  - $key: skipped");

                continue;
            }
            try {
                Mail::to($to)->send($mailable);
                $this->info("  ✓ $key");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ✗ $key: ".$e->getMessage());
                $failed++;
            }
        }

        $this->line("Sent $sent, failed $failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
