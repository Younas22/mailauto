<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public string $recipientName = ''
    ) {}

    public function build(): static
    {
        $body = $this->template->body;

        // Personalise with recipient name if present
        if ($this->recipientName) {
            $body = str_replace(['{{name}}', '{{ name }}'], $this->recipientName, $body);
        }

        return $this
            ->subject($this->template->subject)
            ->html($body);
    }
}
