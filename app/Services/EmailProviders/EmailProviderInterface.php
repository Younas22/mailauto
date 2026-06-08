<?php

namespace App\Services\EmailProviders;

interface EmailProviderInterface
{
    /**
     * Send an email through the provider.
     *
     * Expected $data keys: to, to_name, subject, html.
     * Returns an array containing at least a 'message_id' key on success.
     * Throws \RuntimeException on failure.
     */
    public function send(array $data): array;
}
