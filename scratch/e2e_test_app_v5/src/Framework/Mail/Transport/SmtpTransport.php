<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail\Transport;

use RuntimeException;
use Veldora\Framework\Mail\MailMessage;

class SmtpTransport implements TransportInterface
{
    /**
     * @param array{
     *     host?: string,
     *     port?: int,
     *     encryption?: string|null,
     *     username?: string|null,
     *     password?: string|null,
     *     timeout?: int
     * } $config
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * Send email via raw SMTP socket.
     */
    public function send(MailMessage $message): bool
    {
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = (int) ($this->config['port'] ?? 587);
        $encryption = $this->config['encryption'] ?? 'tls';
        $username = $this->config['username'] ?? null;
        $password = $this->config['password'] ?? null;
        $timeout = (int) ($this->config['timeout'] ?? 15);

        $protocol = ($encryption === 'ssl') ? 'ssl://' : '';
        $socket = @stream_socket_client(
            "{$protocol}{$host}:{$port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new RuntimeException("Could not connect to SMTP host [{$host}:{$port}]: {$errstr} ({$errno})");
        }

        try {
            $this->readResponse($socket, '220');

            // EHLO
            $this->sendCommand($socket, "EHLO " . gethostname());
            $this->readResponse($socket, '250');

            // STARTTLS if TLS encryption requested
            if ($encryption === 'tls') {
                $this->sendCommand($socket, "STARTTLS");
                $this->readResponse($socket, '220');

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException("Failed to establish TLS encryption with SMTP server.");
                }

                $this->sendCommand($socket, "EHLO " . gethostname());
                $this->readResponse($socket, '250');
            }

            // Authentication
            if ($username !== null && $username !== '') {
                $this->sendCommand($socket, "AUTH LOGIN");
                $this->readResponse($socket, '334');

                $this->sendCommand($socket, base64_encode($username));
                $this->readResponse($socket, '334');

                $this->sendCommand($socket, base64_encode($password ?? ''));
                $this->readResponse($socket, '235');
            }

            // From
            $fromEmail = $message->from['email'] ?? $username ?? 'noreply@veldora.local';
            $this->sendCommand($socket, "MAIL FROM:<{$fromEmail}>");
            $this->readResponse($socket, '250');

            // Recipients (To, CC, BCC)
            $allRecipients = array_merge(
                array_keys($message->to),
                array_keys($message->cc),
                array_keys($message->bcc)
            );

            if (empty($allRecipients)) {
                throw new RuntimeException("No recipients specified for mail message.");
            }

            foreach ($allRecipients as $recipient) {
                $this->sendCommand($socket, "RCPT TO:<{$recipient}>");
                $this->readResponse($socket, '250');
            }

            // DATA
            $this->sendCommand($socket, "DATA");
            $this->readResponse($socket, '354');

            // Build MIME message content
            $emailContent = $this->buildMimePayload($message);
            $this->sendCommand($socket, $emailContent . "\r\n.");
            $this->readResponse($socket, '250');

            // QUIT
            $this->sendCommand($socket, "QUIT");
            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            if (is_resource($socket)) {
                @fclose($socket);
            }
            throw $e;
        }
    }

    /**
     * Build standard MIME email content.
     */
    protected function buildMimePayload(MailMessage $message): string
    {
        $boundary = '=_veldora_' . md5((string) microtime());
        $headers = [];

        // Headers
        if ($message->from) {
            $headers[] = 'From: ' . $this->formatAddress($message->from['email'], $message->from['name']);
        }
        if (!empty($message->to)) {
            $headers[] = 'To: ' . $this->formatAddresses($message->to);
        }
        if (!empty($message->cc)) {
            $headers[] = 'Cc: ' . $this->formatAddresses($message->cc);
        }
        if ($message->replyTo) {
            $headers[] = 'Reply-To: ' . $this->formatAddress($message->replyTo['email'], $message->replyTo['name']);
        }

        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($message->subject) . '?=';
        $headers[] = 'Date: ' . date('r');
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

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    /**
     * Send command to socket.
     */
    protected function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    /**
     * Read response from socket and assert expected code.
     */
    protected function readResponse($socket, string $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if (!str_starts_with($response, $expectedCode)) {
            throw new RuntimeException("SMTP error [expected {$expectedCode}]: {$response}");
        }

        return $response;
    }

    protected function formatAddresses(array $addresses): string
    {
        $formatted = [];
        foreach ($addresses as $email => $name) {
            $formatted[] = $this->formatAddress($email, $name);
        }
        return implode(', ', $formatted);
    }

    protected function formatAddress(string $email, ?string $name): string
    {
        if ($name) {
            return "=?UTF-8?B?" . base64_encode($name) . "?= <{$email}>";
        }
        return $email;
    }
}
