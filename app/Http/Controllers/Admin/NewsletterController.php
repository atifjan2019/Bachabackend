<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
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
        return view('admin.newsletter.compose');
    }

    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $subscribers = NewsletterSubscriber::all();
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
