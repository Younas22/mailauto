<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MailgunReplyWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // Log raw payload once for debugging (remove after confirmed working)
        Log::info('[ReplyWebhook] payload', ['body' => $request->all(), 'raw' => $request->getContent()]);

        $recipient = $this->extractRecipient($request);

        if (!$recipient) {
            return response('ok', 200);
        }

        // Extract local part (token) from email: token@domain
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
        // ForwardEmail JSON format: "to" field
        $to = $request->input('to');
        if ($to) {
            if (is_string($to)) return $to;
            if (is_array($to)) {
                $first = $to[0] ?? null;
                if (is_string($first)) return $first;
                if (is_array($first)) return $first['address'] ?? $first['email'] ?? null;
            }
        }

        // Mailgun format
        $recipient = $request->input('recipient');
        if ($recipient) return $recipient;

        // ForwardEmail: envelope.to
        $envelope = $request->input('envelope');
        if ($envelope) {
            $envTo = is_array($envelope) ? ($envelope['to'] ?? null) : null;
            if (is_string($envTo)) return $envTo;
            if (is_array($envTo)) return $envTo[0] ?? null;
        }

        return null;
    }

    private function extractSender(Request $request): string
    {
        // ForwardEmail JSON: "from" field
        $from = $request->input('from');
        if ($from) {
            if (is_string($from)) return $from;
            if (is_array($from)) return $from['address'] ?? $from['email'] ?? '';
        }

        // Mailgun format
        return $request->input('sender', '');
    }
}
