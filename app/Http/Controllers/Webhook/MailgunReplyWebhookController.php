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
        // Verify Mailgun webhook signature if signing key is configured
        $signingKey = env('MAILGUN_WEBHOOK_SIGNING_KEY', '');
        if ($signingKey) {
            $timestamp = $request->input('timestamp', '');
            $token     = $request->input('token', '');
            $signature = $request->input('signature', '');
            $expected  = hash_hmac('sha256', $timestamp . $token, $signingKey);

            if (!hash_equals($expected, $signature)) {
                return response('forbidden', 403);
            }
        }

        // Extract tracking token from recipient: reply+{token}@domain
        $recipient = $request->input('recipient', '');
        if (!preg_match('/reply\+([^@]+)@/', $recipient, $matches)) {
            return response('ok', 200);
        }

        $trackingToken = $matches[1];

        $log = CampaignLog::where('tracking_token', $trackingToken)->first();
        if (!$log) {
            return response('ok', 200);
        }

        $log->increment('reply_count');
        $log->update([
            'replied_at' => now(),
            'replied_by' => $request->input('sender', ''),
        ]);

        return response('ok', 200);
    }
}
