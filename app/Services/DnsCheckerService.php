<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DnsCheckerService
{
    private const CACHE_TTL_HOURS = 6;

    // DKIM selectors to probe for SES + Resend + common ESPs
    private const DKIM_SELECTORS = [
        'amazonses',   // AWS SES default
        'resend',      // Resend
        'default',     // Generic default
        'mail',        // Generic
        'email',       // Generic
        'google',      // Google Workspace
        's1',          // Various
        's2',          // Various
        'k1',          // Klaviyo
        'smtp',        // Generic SMTP
    ];

    public function check(string $domain, bool $force = false): array
    {
        $domain = strtolower(trim($domain));
        $cacheKey = "dns_check:{$domain}";

        if ($force) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addHours(self::CACHE_TTL_HOURS), function () use ($domain) {
            return [
                'domain'     => $domain,
                'spf'        => $this->checkSpf($domain),
                'dkim'       => $this->checkDkim($domain),
                'dmarc'      => $this->checkDmarc($domain),
                'checked_at' => now(),
            ];
        });
    }

    private function checkSpf(string $domain): array
    {
        $records = $this->lookupTxt($domain);

        foreach ($records as $txt) {
            if (str_starts_with($txt, 'v=spf1')) {
                $status = str_contains($txt, '-all') || str_contains($txt, '~all') || str_contains($txt, '?all')
                    ? 'valid'
                    : 'invalid';
                return ['status' => $status, 'record' => $txt];
            }
        }

        return ['status' => 'missing', 'record' => null];
    }

    private function checkDkim(string $domain): array
    {
        foreach (self::DKIM_SELECTORS as $selector) {
            $host    = "{$selector}._domainkey.{$domain}";
            $records = $this->lookupTxt($host);

            foreach ($records as $txt) {
                // A valid DKIM record must have a non-revoked public key (p=...)
                // Some providers (e.g. Resend) omit v=DKIM1/k=rsa tags — accept any record with a real key value
                if (str_contains($txt, 'p=')
                    && !preg_match('/p=\s*;/', $txt)  // p= followed immediately by ; means revoked key
                    && preg_match('/p=[A-Za-z0-9+\/=]{20,}/', $txt)  // key must have real content
                ) {
                    return ['status' => 'valid', 'record' => $txt, 'selector' => $selector];
                }
            }
        }

        return ['status' => 'missing', 'record' => null, 'selector' => null];
    }

    private function checkDmarc(string $domain): array
    {
        $host    = "_dmarc.{$domain}";
        $records = $this->lookupTxt($host);

        foreach ($records as $txt) {
            if (str_starts_with($txt, 'v=DMARC1')) {
                // p=none is technically present but not enforcing — mark invalid
                if (preg_match('/\bp=(\w+)/i', $txt, $m) && strtolower($m[1]) === 'none') {
                    return ['status' => 'invalid', 'record' => $txt, 'note' => 'Policy is p=none (not enforced)'];
                }
                return ['status' => 'valid', 'record' => $txt, 'note' => null];
            }
        }

        return ['status' => 'missing', 'record' => null, 'note' => null];
    }

    private function lookupTxt(string $host): array
    {
        $results = [];

        try {
            $records = @dns_get_record($host, DNS_TXT);
            if (!is_array($records)) {
                return $results;
            }

            foreach ($records as $record) {
                // PHP may return entries as array or as single string depending on PHP version
                if (isset($record['entries']) && is_array($record['entries'])) {
                    $results[] = implode('', $record['entries']);
                } elseif (isset($record['txt'])) {
                    $results[] = $record['txt'];
                }
            }
        } catch (\Throwable) {
            // DNS lookup errors are non-fatal — treat as missing
        }

        return $results;
    }
}
