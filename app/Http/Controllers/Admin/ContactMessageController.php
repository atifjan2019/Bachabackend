<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::orderByDesc('id')->paginate(20);
        $unread = ContactMessage::where('is_read', false)->count();
        return view('admin.contact-messages.index', compact('messages', 'unread'));
    }

    public function show(string $id)
    {
        $message = ContactMessage::findOrFail($id);

        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', compact('message'));
    }

    public function destroy(string $id)
    {
        ContactMessage::findOrFail($id)->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted.');
    }
}
