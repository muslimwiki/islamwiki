<?php
declare(strict_types=1);

namespace IslamWiki\Core\Queue\Jobs;

/**
 * Email Job
 *
 * Handles email sending in the queue.
 */
class EmailJob extends AbstractJob
{
    private string $to;
    private string $subject;
    private string $body;
    private array $options;

    /**
     * Create a new email job.
     */
    public function __construct(string $to, string $subject, string $body, array $options = [])
    {
        parent::__construct([
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'options' => $options
        ], 'emails');

        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->options = $options;
        $this->timeout = 30; // Email jobs should timeout quickly
    }

    /**
     * Handle the email job.
     */
    public function handle(): bool
    {
        try {
            // Simple email sending implementation
            // In a real application, you would use a proper email library
            $headers = [
                'From: ' . ($this->options['from'] ?? 'noreply@islam.wiki'),
                'Reply-To: ' . ($this->options['reply_to'] ?? 'noreply@islam.wiki'),
                'Content-Type: text/html; charset=UTF-8',
                'X-Mailer: IslamWiki Queue System'
            ];

            if (isset($this->options['cc'])) {
                $headers[] = 'Cc: ' . $this->options['cc'];
            }

            if (isset($this->options['bcc'])) {
                $headers[] = 'Bcc: ' . $this->options['bcc'];
            }

            $result = mail(
                $this->to,
                $this->subject,
                $this->body,
                implode("\r\n", $headers)
            );

            if (!$result) {
                throw new \Exception('Failed to send email');
            }

            return true;
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Get the recipient email.
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * Get the email subject.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Get the email body.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get the email options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }
} 