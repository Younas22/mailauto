<?php

namespace App\Services\EmailProviders;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class EmailProviderManager
{
    public static function provider(?string $name = null): EmailProviderInterface
    {
        $name ??= Setting::get('active_email_provider', 'ses');

        return match ($name) {
            'ses'    => new SESProvider(),
            'resend' => new ResendProvider(),
            default  => throw new InvalidArgumentException("Unsupported email provider: {$name}"),
        };
    }

    /**
     * Send through the active provider. If automatic fallback is enabled and
     * the active provider fails, retries once through the configured backup
     * provider. The returned array always carries a 'provider' key identifying
     * whichever provider actually delivered the email.
     */
    public static function send(array $data): array
    {
        $primary = Setting::get('active_email_provider', 'ses');

        try {
            $result = static::provider($primary)->send($data);

            return array_merge($result, ['provider' => $primary]);
        } catch (Throwable $e) {
            $backup = static::backupProvider($primary);

            if (!$backup) {
                throw $e;
            }

            Log::warning("Email provider [{$primary}] failed, falling back to [{$backup}]: " . $e->getMessage());

            $result = static::provider($backup)->send($data);

            return array_merge($result, ['provider' => $backup, 'fallback_from' => $primary]);
        }
    }

    private static function backupProvider(string $primary): ?string
    {
        if (Setting::get('email_fallback_enabled', '0') !== '1') {
            return null;
        }

        $backup = Setting::get('backup_email_provider');

        return ($backup && $backup !== $primary && in_array($backup, ['ses', 'resend'], true)) ? $backup : null;
    }
}
