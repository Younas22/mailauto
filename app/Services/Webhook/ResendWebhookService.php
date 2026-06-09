<?php

namespace App\Services\Webhook;

use App\Models\CampaignLog;
use App\Models\EmailList;
use App\Models\Setting;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ResendWebhookService
{
    /**
     * Process an incoming Resend webhook POST.
     *
     * Resend signs webhooks using Svix. Signature verification requires the
     * webhook signing secret to be saved in settings as `resend_webhook_secret`.
     */
    public function handle(Request $request): bool
    {
        $body    = $request->getContent();
        $payload = json_decode($body, true);

        if (!is_array($payload)) {
            throw new RuntimeException('Invalid JSON payload.');
        }

        $eventType = $payload['type'] ?? 'unknown';

        $webhookLog = WebhookLog::create([
            'provider'    => 'resend',
            'event_type'  => $eventType,
            'payload'     => $payload,
            'processed'   => false,
            'received_at' => now(),
        ]);

        try {
            $this->verifySignature($request, $body);
            $this->processEvent($eventType, $payload);
            $webhookLog->update(['processed' => true]);
            return true;
        } catch (\Throwable $e) {
            $webhookLog->update(['process_error' => $e->getMessage()]);
            throw $e;
        }
    }

    // ─── Svix Signature Verification ─────────────────────────────────────────

    private function verifySignature(Request $request, string $body): void
    {
        $secret = Setting::get('resend_webhook_secret');

        if (!$secret) {
            // Log a warning but allow through in case the secret hasn't been configured yet.
            // In production, remove this bypass once the secret is set.
            Log::warning('[Resend Webhook] resend_webhook_secret not configured — skipping signature check.');
            return;
        }

        $msgId        = $request->header('svix-id');
        $msgTimestamp = $request->header('svix-timestamp');
        $msgSignature = $request->header('svix-signature');

        if (!$msgId || !$msgTimestamp || !$msgSignature) {
            throw new RuntimeException('Missing Svix signature headers.');
        }

        // Reject messages older than 5 minutes to prevent replay attacks.
        if (abs(time() - (int) $msgTimestamp) > 300) {
            throw new RuntimeException('Webhook timestamp is too old.');
        }

        // The Svix signing secret format is "whsec_<base64>"; strip the prefix.
        $signingKey = base64_decode(
            str_starts_with($secret, 'whsec_') ? substr($secret, 6) : $secret
        );

        $signedContent = "{$msgId}.{$msgTimestamp}.{$body}";
        $computed      = base64_encode(hash_hmac('sha256', $signedContent, $signingKey, true));

        // The header may contain multiple space-separated "v1,<sig>" entries.
        $valid = false;
        foreach (explode(' ', $msgSignature) as $sig) {
            [, $sigValue] = explode(',', $sig, 2) + ['', ''];
            if (hash_equals($computed, $sigValue)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            throw new RuntimeException('Resend webhook signature verification failed.');
        }
    }

    // ─── Event Routing ────────────────────────────────────────────────────────

    private function processEvent(string $type, array $payload): void
    {
        match ($type) {
            'email.bounced'    => $this->processBounce($payload),
            'email.complained' => $this->processComplaint($payload),
            'email.sent'       => $this->processDelivery($payload),
            default            => Log::info('[Resend Webhook] Unhandled event type: ' . $type),
        };
    }

    // ─── Bounce ───────────────────────────────────────────────────────────────

    private function processBounce(array $payload): void
    {
        $data      = $payload['data'] ?? [];
        $emailId   = $data['email_id'] ?? null;
        $recipients = (array) ($data['to'] ?? []);

        foreach ($recipients as $email) {
            $this->updateCampaignLog($emailId, $email, 'bounced', 'hard', null, $data['created_at'] ?? null);

            EmailList::where('email', $email)->update([
                'bounced_at'    => now(),
                'is_do_not_mail' => true,
            ]);

            Log::info("[Resend Webhook] Hard bounce — suppressed: {$email}");
        }
    }

    // ─── Complaint ────────────────────────────────────────────────────────────

    private function processComplaint(array $payload): void
    {
        $data      = $payload['data'] ?? [];
        $emailId   = $data['email_id'] ?? null;
        $recipients = (array) ($data['to'] ?? []);

        foreach ($recipients as $email) {
            $this->updateCampaignLog($emailId, $email, 'complained', null, 'spam_complaint', $data['created_at'] ?? null);

            EmailList::where('email', $email)->update([
                'complained_at'  => now(),
                'is_do_not_mail' => true,
            ]);

            Log::info("[Resend Webhook] Complaint — suppressed: {$email}");
        }
    }

    // ─── Delivery (informational) ─────────────────────────────────────────────

    private function processDelivery(array $payload): void
    {
        $data    = $payload['data'] ?? [];
        $emailId = $data['email_id'] ?? 'unknown';
        Log::info("[Resend Webhook] Delivery confirmed for email_id: {$emailId}");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function updateCampaignLog(
        ?string $messageId,
        string $email,
        string $status,
        ?string $bounceType,
        ?string $reason,
        mixed $eventAt
    ): void {
        $query = CampaignLog::where('email', $email)
            ->where('status', 'sent')
            ->orderByDesc('id');

        $log = $messageId
            ? (CampaignLog::where('provider_message_id', $messageId)->first() ?? $query->first())
            : $query->first();

        if (!$log) return;

        $log->update([
            'status'           => $status,
            'bounce_type'      => $bounceType,
            'complaint_reason' => $reason,
            'event_at'         => $eventAt ? now()->parse($eventAt) : now(),
        ]);
    }
}
