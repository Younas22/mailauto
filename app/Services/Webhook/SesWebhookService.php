<?php

namespace App\Services\Webhook;

use App\Models\CampaignLog;
use App\Models\EmailList;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SesWebhookService
{
    /**
     * Process an incoming SNS HTTP POST.
     *
     * Returns true on success, throws RuntimeException on security failure,
     * returns false when the notification type is unknown/unhandled.
     */
    public function handle(Request $request): bool
    {
        $body    = $request->getContent();
        $payload = json_decode($body, true);

        if (!is_array($payload)) {
            throw new RuntimeException('Invalid JSON payload.');
        }

        $type = $payload['Type'] ?? '';

        // Log everything before processing so we have the raw payload even on failure.
        $webhookLog = WebhookLog::create([
            'provider'    => 'ses',
            'event_type'  => strtolower($type),
            'payload'     => $payload,
            'processed'   => false,
            'received_at' => now(),
        ]);

        try {
            $this->verifySignature($payload);

            if ($type === 'SubscriptionConfirmation') {
                $this->confirmSubscription($payload);
                $webhookLog->update(['processed' => true]);
                return true;
            }

            if ($type === 'Notification') {
                $message = json_decode($payload['Message'] ?? '{}', true);
                $this->processNotification($message, $webhookLog);
                $webhookLog->update(['processed' => true]);
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            $webhookLog->update(['process_error' => $e->getMessage()]);
            throw $e;
        }
    }

    // ─── SNS Signature Verification ───────────────────────────────────────────

    private function verifySignature(array $payload): void
    {
        $certUrl = $payload['SigningCertURL'] ?? '';

        // Only trust certs hosted on amazonaws.com.
        if (!preg_match('#^https://sns\.[a-z0-9\-]+\.amazonaws\.com/#', $certUrl)) {
            throw new RuntimeException('Invalid SNS signing cert URL: ' . $certUrl);
        }

        $cert = Http::get($certUrl)->body();
        if (!$cert) {
            throw new RuntimeException('Could not download SNS signing certificate.');
        }

        $signedString = $this->buildStringToSign($payload);
        $signature    = base64_decode($payload['Signature'] ?? '');

        $pubKey = openssl_get_publickey($cert);
        $valid  = openssl_verify($signedString, $signature, $pubKey, OPENSSL_ALGO_SHA1);

        if ($valid !== 1) {
            throw new RuntimeException('SNS signature verification failed.');
        }
    }

    private function buildStringToSign(array $payload): string
    {
        $type = $payload['Type'];

        $keys = $type === 'Notification'
            ? ['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type']
            : ['Message', 'MessageId', 'SubscribeURL', 'Timestamp', 'Token', 'TopicArn', 'Type'];

        $parts = '';
        foreach ($keys as $key) {
            if (isset($payload[$key])) {
                $parts .= $key . "\n" . $payload[$key] . "\n";
            }
        }

        return $parts;
    }

    // ─── SNS Subscription Auto-Confirmation ───────────────────────────────────

    private function confirmSubscription(array $payload): void
    {
        $url = $payload['SubscribeURL'] ?? '';

        // Only auto-confirm from amazonaws.com
        if (!preg_match('#^https://sns\.[a-z0-9\-]+\.amazonaws\.com/#', $url)) {
            throw new RuntimeException('Invalid SubscribeURL: ' . $url);
        }

        Http::get($url);
        Log::info('[SES Webhook] SNS subscription confirmed for topic: ' . ($payload['TopicArn'] ?? 'unknown'));
    }

    // ─── Notification Routing ─────────────────────────────────────────────────

    private function processNotification(array $message, WebhookLog $log): void
    {
        $notificationType = $message['notificationType'] ?? '';
        $log->update(['event_type' => strtolower($notificationType)]);

        match ($notificationType) {
            'Bounce'    => $this->processBounce($message),
            'Complaint' => $this->processComplaint($message),
            'Delivery'  => $this->processDelivery($message),
            default     => Log::info('[SES Webhook] Unhandled notification type: ' . $notificationType),
        };
    }

    // ─── Bounce ───────────────────────────────────────────────────────────────

    private function processBounce(array $message): void
    {
        $bounce    = $message['bounce'];
        $messageId = $message['mail']['messageId'] ?? null;

        // SES bounce types: Permanent (hard), Transient (soft), Undetermined
        $rawType   = $bounce['bounceType'] ?? 'Undetermined';
        $bounceType = match ($rawType) {
            'Permanent'    => 'hard',
            'Transient'    => 'soft',
            default        => 'soft',
        };

        foreach ($bounce['bouncedRecipients'] as $recipient) {
            $email  = $recipient['emailAddress'] ?? null;
            $reason = ($recipient['diagnosticCode'] ?? null) ?: ($bounce['bounceSubType'] ?? null);

            if (!$email) continue;

            // Update the campaign log for this send.
            $this->updateCampaignLog($messageId, $email, 'bounced', $bounceType, $reason, $bounce['timestamp'] ?? null);

            // Hard bounces permanently suppress the address.
            if ($bounceType === 'hard') {
                EmailList::where('email', $email)->update([
                    'bounced_at'    => now(),
                    'is_do_not_mail' => true,
                ]);
                Log::info("[SES Webhook] Hard bounce — suppressed: {$email}");
            } else {
                EmailList::where('email', $email)->update(['bounced_at' => now()]);
                Log::info("[SES Webhook] Soft bounce: {$email}");
            }
        }
    }

    // ─── Complaint ────────────────────────────────────────────────────────────

    private function processComplaint(array $message): void
    {
        $complaint = $message['complaint'];
        $messageId = $message['mail']['messageId'] ?? null;
        $reason    = $complaint['complaintFeedbackType'] ?? 'abuse';

        foreach ($complaint['complainedRecipients'] as $recipient) {
            $email = $recipient['emailAddress'] ?? null;

            if (!$email) continue;

            $this->updateCampaignLog($messageId, $email, 'complained', null, $reason, $complaint['timestamp'] ?? null);

            EmailList::where('email', $email)->update([
                'complained_at'  => now(),
                'is_do_not_mail' => true,
            ]);

            Log::info("[SES Webhook] Complaint ({$reason}) — suppressed: {$email}");
        }
    }

    // ─── Delivery (informational) ─────────────────────────────────────────────

    private function processDelivery(array $message): void
    {
        $messageId = $message['mail']['messageId'] ?? null;
        foreach ($message['delivery']['recipients'] ?? [] as $email) {
            Log::info("[SES Webhook] Delivery confirmed: {$email} (msgId: {$messageId})");
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function updateCampaignLog(
        ?string $messageId,
        string $email,
        string $status,
        ?string $bounceType,
        ?string $reason,
        mixed $eventAt
    ): void {
        $query = CampaignLog::where('email', $email)
            ->whereIn('status', ['sent'])
            ->orderByDesc('id');

        if ($messageId) {
            // Prefer exact match by provider message ID.
            $log = CampaignLog::where('provider_message_id', $messageId)->first()
                ?? $query->first();
        } else {
            $log = $query->first();
        }

        if (!$log) return;

        $log->update([
            'status'           => $status,
            'bounce_type'      => $bounceType,
            'complaint_reason' => $reason,
            'event_at'         => $eventAt ? now()->parse($eventAt) : now(),
        ]);
    }
}
