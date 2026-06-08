<?php

namespace App\Services\EmailProviders;

use App\Models\Setting;
use Resend;
use RuntimeException;
use Throwable;

class ResendProvider implements EmailProviderInterface
{
    public function send(array $data): array
    {
        $apiKey = Setting::get('resend_api_key');

        if (!$apiKey) {
            throw new RuntimeException('Resend API key is not configured.');
        }

        $from     = Setting::get('resend_sender_email') ?: Setting::get('mail_from_email');
        $fromName = Setting::get('mail_from_name', 'MailAuto');

        if (!$from) {
            throw new RuntimeException('No sender email configured for Resend.');
        }

        try {
            $email = Resend::client($apiKey)->emails->send([
                'from'    => $this->formatAddress($from, $fromName),
                'to'      => [$this->formatAddress($data['to'], $data['to_name'] ?? null)],
                'subject' => $data['subject'],
                'html'    => $data['html'],
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Resend send failed: ' . $e->getMessage(), previous: $e);
        }

        return ['message_id' => $email->id ?? null];
    }

    private function formatAddress(string $email, ?string $name): string
    {
        return $name ? "{$name} <{$email}>" : $email;
    }
}
