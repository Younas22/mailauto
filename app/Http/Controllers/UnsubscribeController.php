<?php

namespace App\Http\Controllers;

use App\Models\EmailList;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    public function show(string $token)
    {
        $contact = EmailList::where('unsubscribe_token', $token)->firstOrFail();

        return view('unsubscribe.confirm', compact('contact', 'token'));
    }

    public function process(string $token)
    {
        $contact = EmailList::where('unsubscribe_token', $token)->firstOrFail();

        if (!$contact->isUnsubscribed()) {
            $contact->update([
                'status'          => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return view('unsubscribe.success', compact('contact'));
    }
}
