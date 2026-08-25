<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail\Transport;

use Veldora\Framework\Mail\MailMessage;

class PhpMailTransport implements TransportInterface
{
    /**
     * Send email using PHP's native mail() function.
     */
    public function send(MailMessage $message): bool
    {
        $to = $this->formatAddresses($message->to);
        $subject = $message->subject;

        $boundary = '=_veldora_' . md5((string) microtime());
        $headers = [];

        // From
        if ($message->from) {
            $headers[] = 'From: ' . $this->formatAddress($message->from['email'], $message->from['name']);
        }

        // CC
        if (!empty($message->cc)) {
            $headers[] = 'Cc: ' . $this->formatAddresses($message->cc);
        }

        // BCC
        if (!empty($message->bcc)) {
            $headers[] = 'Bcc: ' . $this->formatAddresses($message->bcc);
        }

        // Reply-To
        if ($message->replyTo) {
            $headers[] = 'Reply-To: ' . $this->formatAddress($message->replyTo['email'], $message->replyTo['name']);
        }

        // MIME version
        $headers[] = 'MIME-Version: 1.0';

        $body = '';

        if (!empty($message->attachments)) {
            $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

            // Message body part
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= ($message->htmlBody ?: $message->textBody) . "\r\n\r\n";

            // Attachments
            foreach ($message->attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $content = chunk_split(base64_encode((string) file_get_contents($attachment['path'])));
                    $body .= "--{$boundary}\r\n";
                    $body .= "Content-Type: {$attachment['mime']}; name=\"{$attachment['name']}\"\r\n";
                    $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n";
                    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $body .= $content . "\r\n";
                }
            }

            $body .= "--{$boundary}--\r\n";
        } else {
            $isHtml = !empty($message->htmlBody);
            $contentType = $isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8';
            $headers[] = "Content-Type: {$contentType}";
            $body = $isHtml ? $message->htmlBody : $message->textBody;
        }

        $headerStr = implode("\r\n", $headers);

        return @mail($to, $subject, $body, $headerStr);
    }

    /**
     * Format an email list for headers.
     *
     * @param array<string, string|null> $addresses
     */
    protected function formatAddresses(array $addresses): string
    {
        $formatted = [];
        foreach ($addresses as $email => $name) {
            $formatted[] = $this->formatAddress($email, $name);
        }
        return implode(', ', $formatted);
    }

    /**
     * Format a single email address.
     */
    protected function formatAddress(string $email, ?string $name): string
    {
        if ($name) {
            return "{$name} <{$email}>";
        }
        return $email;
    }
}
