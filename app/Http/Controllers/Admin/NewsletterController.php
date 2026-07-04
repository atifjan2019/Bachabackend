<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;

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

    public function send(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $subscribers = NewsletterSubscriber::all();

        foreach ($subscribers as $subscriber) {
            \App\Jobs\SendNewsletterJob::dispatch(
                $subscriber->email, 
                $request->subject, 
                $request->body
            );
        }

        return redirect()->route('admin.newsletter.index')->with('success', "Newsletter queued for {$subscribers->count()} subscribers.");
    }
}
