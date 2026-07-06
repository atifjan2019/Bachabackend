<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::orderByDesc('id')->paginate(30);
        return view('admin.newsletter.index', compact('subscribers'));
    }

    public function destroy(string $id)
    {
        NewsletterSubscriber::findOrFail($id)->delete();
        return redirect()->route('admin.newsletter.index')->with('success', 'Subscriber removed.');
    }

    public function compose()
    {
        $subscribers = NewsletterSubscriber::orderBy('email')->get();

        // Brand context for the live preview (mirrors the email template header/footer).
        $s = Setting::whereIn('setting_key', [
            'business_name', 'business_email', 'business_phone',
            'business_address', 'canonical_base_url',
        ])->pluck('setting_value', 'setting_key');

        $brand = [
            'name'    => $s['business_name'] ?? 'Bacha Stylo',
            'email'   => $s['business_email'] ?? '',
            'phone'   => $s['business_phone'] ?? '',
            'address' => $s['business_address'] ?? '',
            'site'    => rtrim($s['canonical_base_url'] ?? 'https://www.bachastylo.com', '/'),
        ];

        return view('admin.newsletter.compose', compact('subscribers', 'brand'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject'        => 'required|string|max:255',
            'body'           => 'required|string',
            'recipient_mode' => 'required|in:all,include,exclude',
            'recipients'     => 'nullable|array',
            'recipients.*'   => 'integer',
        ]);

        $mode     = $request->recipient_mode;
        $selected = $request->input('recipients', []);

        $query = NewsletterSubscriber::query();

        if ($mode === 'include') {
            if (empty($selected)) {
                return back()->withInput()
                    ->with('error', 'Please select at least one subscriber to include.');
            }
            $query->whereIn('id', $selected);
        } elseif ($mode === 'exclude' && !empty($selected)) {
            $query->whereNotIn('id', $selected);
        }

        $subscribers = $query->get();

        if ($subscribers->isEmpty()) {
            return back()->withInput()
                ->with('error', 'No subscribers match the selected recipients.');
        }

        $sent  = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(
                    new NewsletterMail($request->subject, $request->body, $subscriber->email)
                );
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                Log::error("Newsletter send failed for {$subscriber->email}: " . $e->getMessage());
            }
        }

        $message = "Newsletter sent to {$sent} subscriber(s).";
        if ($failed > 0) {
            $message .= " {$failed} failed (check logs).";
        }

        return redirect()->route('admin.newsletter.index')->with('success', $message);
    }
}
