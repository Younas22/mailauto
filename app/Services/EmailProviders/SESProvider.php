<?php

namespace App\Services\EmailProviders;

use App\Models\Setting;
use Aws\Ses\SesClient;
use RuntimeException;
use Throwable;

class SESProvider implements EmailProviderInterface
{
    public function send(array $data): array
    {
        $key    = Setting::get('ses_access_key');
        $secret = Setting::get('ses_secret_key');

        if (!$key || !$secret) {
            throw new RuntimeException('Amazon SES credentials are not configured.');
        }

        $from     = Setting::get('ses_sender_email') ?: Setting::get('mail_from_email');
        $fromName = Setting::get('mail_from_name', 'MailAuto');

        if (!$from) {
            throw new RuntimeException('No sender email configured for Amazon SES.');
        }

        $client = new SesClient([
            'version'     => 'latest',
            'region'      => Setting::get('ses_region', 'us-east-1'),
            'credentials' => ['key' => $key, 'secret' => $secret],
        ]);

        try {
            $params = [
                'Source'      => $this->formatAddress($from, $fromName),
                'Destination' => [
                    'ToAddresses' => [$this->formatAddress($data['to'], $data['to_name'] ?? null)],
                ],
                'Message' => [
                    'Subject' => ['Data' => $data['subject'], 'Charset' => 'UTF-8'],
                    'Body'    => ['Html' => ['Data' => $data['html'], 'Charset' => 'UTF-8']],
                ],
            ];

            if (!empty($data['reply_to'])) {
                $replyTo = is_array($data['reply_to']) ? $data['reply_to'] : [$data['reply_to']];
                $params['ReplyToAddresses'] = $replyTo;
            }

            $result = $client->sendEmail($params);
        } catch (Throwable $e) {
            throw new RuntimeException('Amazon SES send failed: ' . $e->getMessage(), previous: $e);
        }

        return ['message_id' => $result->get('MessageId')];
    }

    private function formatAddress(string $email, ?string $name): string
    {
        return $name ? "{$name} <{$email}>" : $email;
    }
}
