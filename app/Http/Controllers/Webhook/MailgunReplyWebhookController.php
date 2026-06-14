<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CampaignLog;
use App\Models\Setting;
use App\Services\EmailProviders\EmailProviderManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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

        $sender = $this->extractSender($request);

        $log->increment('reply_count');
        $log->update([
            'replied_at' => now(),
            'replied_by' => $sender,
        ]);

        $this->forwardReplyToAdmin($request, $log, $sender);

        return response('ok', 200);
    }

    private function forwardReplyToAdmin(Request $request, CampaignLog $log, string $sender): void
    {
        $adminEmail = Setting::get('mail_from_email');
        if (!$adminEmail) return;

        $adminName  = Setting::get('mail_from_name', 'MailAuto');
        $from       = $request->input('from');
        $senderName = is_array($from) ? ($from['value'][0]['name'] ?? $sender) : $sender;
        $subject    = $request->input('subject', 'Reply to your campaign');
        $html       = $request->input('html') ?: nl2br(e($request->input('text', '')));

        try {
            EmailProviderManager::send([
                'to'      => $adminEmail,
                'to_name' => $adminName,
                'subject' => 'Fwd: ' . $subject,
                'html'    => '<p><strong>From:</strong> ' . e($senderName) . ' &lt;' . e($sender) . '&gt;</p>'
                           . '<p><strong>Replied to:</strong> ' . e($log->email) . '</p>'
                           . '<hr>' . $html,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[ReplyWebhook] Forward to admin failed: ' . $e->getMessage());
        }
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
