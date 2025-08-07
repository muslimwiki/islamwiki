<?php

declare(strict_types=1);

namespace IslamWiki\Core\Queue\Jobs;

/**
 * Notification Job
 *
 * Handles user notifications in the queue.
 */
class NotificationJob extends AbstractJob
{
    private int $userId;
    private string $type;
    private array $data;

    /**
     * Create a new notification job.
     */
    public function __construct(int $userId, string $type, array $data = [])
    {
        parent::__construct([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data
        ], 'notifications');

        $this->userId = $userId;
        $this->type = $type;
        $this->data = $data;
        $this->timeout = 15; // Notification jobs should be quick
    }

    /**
     * Handle the notification job.
     */
    public function handle(): bool
    {
        try {
            // Store notification in database
            $notification = [
                'user_id' => $this->userId,
                'type' => $this->type,
                'data' => json_encode($this->data),
                'created_at' => date('Y-m-d H:i:s'),
                'read_at' => null
            ];

            // In a real application, you would use a proper database connection
            // For now, we'll simulate storing the notification
            $notificationId = uniqid('notif_', true);

            // Log the notification
            error_log("Notification created: {$notificationId} for user {$this->userId}");

            // Send real-time notification if possible
            $this->sendRealTimeNotification();

            return true;
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send real-time notification.
     */
    private function sendRealTimeNotification(): void
    {
        // In a real application, you would use WebSockets or similar
        // For now, we'll just log the notification
        $message = $this->getNotificationMessage();
        error_log("Real-time notification for user {$this->userId}: {$message}");
    }

    /**
     * Get the notification message.
     */
    private function getNotificationMessage(): string
    {
        $messages = [
            'welcome' => 'Welcome to IslamWiki!',
            'page_updated' => 'A page you follow has been updated.',
            'comment_reply' => 'Someone replied to your comment.',
            'mention' => 'You were mentioned in a comment.',
            'system' => 'System notification: ' . ($this->data['message'] ?? ''),
        ];

        return $messages[$this->type] ?? 'You have a new notification.';
    }

    /**
     * Get the user ID.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the notification type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the notification data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
