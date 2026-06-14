<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailgunReplyWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $recipient = $this->extractRecipient($request);

        if (!$recipient) {
            return response('ok', 200);
        }

        if (!preg_match('/^([^@]+)@/', $recipient, $matches)) {
            return response('ok', 200);
        }

        $replyToken = $matches[1];

        $log = CampaignLog::where('tracking_token', 'LIKE', $replyToken . '%')->first();
        if (!$log) {
            return response('ok', 200);
        }

        $log->increment('reply_count');
        $log->update([
            'replied_at' => now(),
            'replied_by' => $this->extractSender($request),
        ]);

        return response('ok', 200);
    }

    private function extractRecipient(Request $request): ?string
    {
        // ForwardEmail: "recipients" array (simplest)
        $recipients = $request->input('recipients');
        if (!empty($recipients) && is_array($recipients)) {
            return $recipients[0];
        }

        // ForwardEmail: "session.recipient"
        $session = $request->input('session');
        if (!empty($session['recipient'])) {
            return $session['recipient'];
        }

        // ForwardEmail: "to.value[0].address"
        $to = $request->input('to');
        if (is_array($to) && !empty($to['value'][0]['address'])) {
            return $to['value'][0]['address'];
        }

        // Mailgun format
        return $request->input('recipient');
    }

    private function extractSender(Request $request): string
    {
        // ForwardEmail: "session.sender"
        $session = $request->input('session');
        if (!empty($session['sender'])) {
            return $session['sender'];
        }

        // ForwardEmail: "from.value[0].address"
        $from = $request->input('from');
        if (is_array($from) && !empty($from['value'][0]['address'])) {
            return $from['value'][0]['address'];
        }

        // Mailgun format
        return $request->input('sender', '');
    }
}
