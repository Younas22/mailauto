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
        public string $recipientName  = '',
        public string $recipientEmail = '',
        public string $unsubscribeToken = ''
    ) {}

    /**
     * Render the subject and HTML body, with placeholders replaced and the
     * unsubscribe footer appended. Shared by build() and provider-based sending.
     */
    public function renderContent(): array
    {
        $body = $this->template->body;

        if ($this->recipientName) {
            $body = str_replace(['{{name}}', '{{ name }}'], $this->recipientName, $body);
        }

        if ($this->recipientEmail) {
            $body = str_replace(['{{email}}', '{{ email }}'], $this->recipientEmail, $body);
        }

        if ($this->unsubscribeToken) {
            $unsubscribeUrl  = route('unsubscribe.show', $this->unsubscribeToken);
            $unsubscribeHtml = '<div style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#94a3b8;">'
                . 'You received this email because you are on our mailing list. '
                . '<a href="' . $unsubscribeUrl . '" style="color:#6366f1;text-decoration:underline;">Unsubscribe</a>'
                . '</div>';
            $body .= $unsubscribeHtml;
        }

        return ['subject' => $this->template->subject, 'html' => $body];
    }

    public function build(): static
    {
        $content = $this->renderContent();

        return $this
            ->subject($content['subject'])
            ->html($content['html']);
    }
}
