<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\DashboardExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Extensions\DashboardExtension\Services\DashboardService;

/**
 * Dashboard Extension
 *
 * Provides enhanced dashboard functionality with Islamic-themed widgets,
 * analytics, user management, and customizable layouts.
 */
class DashboardExtension extends Extension
{
    /**
     * @var array Dashboard widgets configuration
     */
    private array $widgets = [];

    /**
     * @var array Dashboard layouts
     */
    private array $layouts = [];

    /**
     * @var DashboardService Dashboard service instance
     */
    private DashboardService $dashboardService;

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadWidgets();
        $this->loadLayouts();
        $this->registerHooks();
        $this->initializeServices();
    }

    /**
     * Load dashboard widgets from configuration
     */
    private function loadWidgets(): void
    {
        $config = $this->getConfig();
        $this->widgets = $config['widgets'] ?? [];
    }

    /**
     * Load available dashboard layouts
     */
    private function loadLayouts(): void
    {
        $this->layouts = [
            'islamic' => [
                'name' => 'Islamic Layout',
                'description' => 'Traditional Islamic design with geometric patterns',
                'columns' => 3,
                'defaultWidgets' => ['user-overview', 'islamic-calendar', 'prayer-times', 'content-stats', 'recent-activity', 'quran-verse']
            ],
            'modern' => [
                'name' => 'Modern Layout',
                'description' => 'Clean, modern design with cards and shadows',
                'columns' => 4,
                'defaultWidgets' => ['user-overview', 'content-stats', 'recent-activity', 'quick-actions', 'notifications', 'system-status']
            ],
            'compact' => [
                'name' => 'Compact Layout',
                'description' => 'Space-efficient layout for small screens',
                'columns' => 2,
                'defaultWidgets' => ['user-overview', 'content-stats', 'recent-activity', 'quick-actions']
            ]
        ];
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Dashboard initialization hook
        $hookManager->register('DashboardInit', [$this, 'onDashboardInit'], 10);

        // Dashboard rendering hook
        $hookManager->register('DashboardRender', [$this, 'onDashboardRender'], 10);

        // Widget rendering hook
        $hookManager->register('DashboardWidget', [$this, 'onDashboardWidget'], 10);

        // User statistics hook
        $hookManager->register('UserStats', [$this, 'onUserStats'], 10);

        // Content statistics hook
        $hookManager->register('ContentStats', [$this, 'onContentStats'], 10);

        // System status hook
        $hookManager->register('SystemStatus', [$this, 'onSystemStatus'], 10);

        // Notification hooks
        $hookManager->register('NotificationCreate', [$this, 'onNotificationCreate'], 10);
        $hookManager->register('NotificationDisplay', [$this, 'onNotificationDisplay'], 10);
    }

    /**
     * Initialize dashboard services
     */
    private function initializeServices(): void
    {
        $this->dashboardService = new DashboardService($this->getConfig());
    }

    /**
     * Dashboard initialization hook
     *
     * @param array $context Dashboard context
     * @return array Modified context
     */
    public function onDashboardInit(array $context): array
    {
        $context['layouts'] = $this->layouts;
        $context['extension_config'] = $this->getConfig();
        $context['available_widgets'] = $this->widgets;

        return $context;
    }

    /**
     * Dashboard rendering hook
     *
     * @param array $context Dashboard context
     * @return array Modified context
     */
    public function onDashboardRender(array $context): array
    {
        // Add dashboard-specific CSS and JS
        $context['dashboard_css'] = $this->getResourceUrls('css');
        $context['dashboard_js'] = $this->getResourceUrls('js');

        // Add widget data
        $context['widget_data'] = $this->getWidgetData($context);

        return $context;
    }

    /**
     * Dashboard widget hook
     *
     * @param string $widgetName Widget identifier
     * @param array $context Widget context
     * @return array Modified context
     */
    public function onDashboardWidget(string $widgetName, array $context): array
    {
        if (!isset($this->widgets[$widgetName])) {
            return $context;
        }

        $widget = $this->widgets[$widgetName];
        $context['widget'] = $widget;
        $context['widget_data'] = $this->getWidgetContent($widgetName, $context);

        return $context;
    }

    /**
     * User statistics hook
     *
     * @param int $userId User ID
     * @return array User statistics
     */
    public function onUserStats(int $userId): array
    {
        // Placeholder implementation
        return [
            'articles_created' => 0,
            'articles_edited' => 0,
            'total_edits' => 0,
            'last_edit' => null,
            'join_date' => null,
            'reputation' => 0
        ];
    }

    /**
     * Content statistics hook
     *
     * @return array Content statistics
     */
    public function onContentStats(): array
    {
        // Return placeholder statistics for now
        return [
            'total_articles' => 0,
            'total_edits' => 0,
            'recent_activity' => 0,
            'popular_content' => []
        ];
    }

    /**
     * System status hook
     *
     * @return array System status information
     */
    public function onSystemStatus(): array
    {
        $status = [
            'database' => 'online',
            'cache' => 'online',
            'storage' => 'online',
            'extensions' => 'online',
            'last_check' => date('Y-m-d H:i:s')
        ];

        return $status;
    }

    /**
     * Notification creation hook
     *
     * @param array $notification Notification data
     * @return array Modified notification
     */
    public function onNotificationCreate(array $notification): array
    {
        // Add dashboard-specific notification properties
        $notification['dashboard_priority'] = $this->getNotificationPriority($notification['type'] ?? 'general');
        $notification['dashboard_category'] = $this->getNotificationCategory($notification['type'] ?? 'general');
        
        return $notification;
    }

    /**
     * Notification display hook
     *
     * @param array $notification Notification data
     * @return array Modified notification
     */
    public function onNotificationDisplay(array $notification): array
    {
        // Format notification for dashboard display
        $notification['formatted_time'] = $this->formatNotificationTime($notification['created_at'] ?? '');
        $notification['icon'] = $this->getNotificationIcon($notification['type'] ?? 'general');
        
        return $notification;
    }

    /**
     * Get widget data for dashboard
     *
     * @param array $context Dashboard context
     * @return array Widget data
     */
    private function getWidgetData(array $context): array
    {
        $widgetData = [];

        foreach ($this->widgets as $widgetId => $widget) {
            $widgetData[$widgetId] = $this->getWidgetContent($widgetId, $context);
        }

        return $widgetData;
    }

    /**
     * Get widget content
     *
     * @param string $widgetId Widget identifier
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getWidgetContent(string $widgetId, array $context): array
    {
        switch ($widgetId) {
            case 'user-overview':
                return $this->getUserOverviewContent($context);
            case 'content-stats':
                return $this->getContentStatsContent($context);
            case 'recent-activity':
                return $this->getRecentActivityContent($context);
            case 'system-status':
                return $this->getSystemStatusContent($context);
            case 'islamic-calendar':
                return $this->getIslamicCalendarContent($context);
            case 'prayer-times':
                return $this->getPrayerTimesContent($context);
            case 'quran-verse':
                return $this->getQuranVerseContent($context);
            case 'hadith-quote':
                return $this->getHadithQuoteContent($context);
            case 'quick-actions':
                return $this->getQuickActionsContent($context);
            case 'notifications':
                return $this->getNotificationsContent($context);
            default:
                return [];
        }
    }

    /**
     * Get user overview widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getUserOverviewContent(array $context): array
    {
        // Placeholder implementation
        return [
            'user' => [
                'username' => 'User',
                'role' => 'user',
                'islamic_role' => null
            ],
            'stats' => [
                'articles_created' => 0,
                'total_edits' => 0,
                'last_edit' => null
            ]
        ];
    }

    /**
     * Get content statistics widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getContentStatsContent(array $context): array
    {
        return $this->onContentStats();
    }

    /**
     * Get recent activity widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getRecentActivityContent(array $context): array
    {
        // Return placeholder recent activity for now
        return [
            'recent_edits' => [],
            'recent_articles' => [],
            'recent_comments' => []
        ];
    }

    /**
     * Get system status widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getSystemStatusContent(array $context): array
    {
        return $this->onSystemStatus();
    }

    /**
     * Get Islamic calendar widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getIslamicCalendarContent(array $context): array
    {
        // Return placeholder Islamic calendar data for now
        return [
            'year' => 1446,
            'month' => 3,
            'day' => 15,
            'month_name' => 'Rabi\' al-Awwal',
            'gregorian_date' => date('Y-m-d')
        ];
    }

    /**
     * Get prayer times widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getPrayerTimesContent(array $context): array
    {
        // Return placeholder prayer times for now
        return [
            'fajr' => '5:30 AM',
            'dhuhr' => '12:30 PM',
            'asr' => '3:45 PM',
            'maghrib' => '6:15 PM',
            'isha' => '7:45 PM',
            'next_prayer' => 'Asr',
            'next_prayer_time' => '3:45 PM'
        ];
    }

    /**
     * Get Quran verse widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getQuranVerseContent(array $context): array
    {
        // Return placeholder Quran verse for now
        return [
            'surah' => 'Al-Baqarah',
            'ayah' => 255,
            'text' => 'Allah - there is no deity except Him, the Ever-Living, the Self-Sustaining...',
            'translation' => 'Allah - there is no deity except Him, the Ever-Living, the Self-Sustaining...',
            'reference' => '2:255'
        ];
    }

    /**
     * Get hadith quote widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getHadithQuoteContent(array $context): array
    {
        // Return placeholder hadith for now
        return [
            'collection' => 'Sahih Bukhari',
            'book' => 'Book of Faith',
            'number' => 1,
            'text' => 'Actions are judged by intentions...',
            'narrator' => 'Umar ibn al-Khattab',
            'reference' => 'Bukhari 1:1'
        ];
    }

    /**
     * Get quick actions widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getQuickActionsContent(array $context): array
    {
        $actions = [];

        $actions[] = [
            'title' => 'Create Article',
            'url' => '/wiki/create',
            'icon' => '✏️',
            'description' => 'Start writing a new article'
        ];

        $actions[] = [
            'title' => 'Edit Profile',
            'url' => '/profile/edit',
            'icon' => '👤',
            'description' => 'Update your profile information'
        ];

        $actions[] = [
            'title' => 'Settings',
            'url' => '/settings',
            'icon' => '⚙️',
            'description' => 'Configure your preferences'
        ];

        return $actions;
    }

    /**
     * Get notifications widget content
     *
     * @param array $context Widget context
     * @return array Widget content
     */
    private function getNotificationsContent(array $context): array
    {
        // Placeholder implementation
        return [
            [
                'id' => 1,
                'type' => 'general',
                'title' => 'Welcome to IslamWiki',
                'message' => 'Thank you for joining our community!',
                'time' => date('Y-m-d H:i:s'),
                'is_read' => false
            ]
        ];
    }

    /**
     * Get notification priority
     *
     * @param string $type Notification type
     * @return string Priority level
     */
    private function getNotificationPriority(string $type): string
    {
        $priorities = [
            'system_alert' => 'high',
            'security' => 'high',
            'content_approval' => 'medium',
            'user_mention' => 'medium',
            'content_update' => 'low',
            'general' => 'low'
        ];

        return $priorities[$type] ?? 'low';
    }

    /**
     * Get notification category
     *
     * @param string $type Notification type
     * @return string Category
     */
    private function getNotificationCategory(string $type): string
    {
        $categories = [
            'system_alert' => 'system',
            'security' => 'security',
            'content_approval' => 'content',
            'user_mention' => 'social',
            'content_update' => 'content',
            'general' => 'general'
        ];

        return $categories[$type] ?? 'general';
    }

    /**
     * Format notification time
     *
     * @param string $timestamp Timestamp
     * @return string Formatted time
     */
    private function formatNotificationTime(string $timestamp): string
    {
        if (empty($timestamp)) {
            return 'Unknown';
        }

        try {
            $time = new \DateTime($timestamp);
            $now = new \DateTime();
            $diff = $now->diff($time);

            if ($diff->days > 0) {
                return $diff->days . ' days ago';
            } elseif ($diff->h > 0) {
                return $diff->h . ' hours ago';
            } elseif ($diff->i > 0) {
                return $diff->i . ' minutes ago';
            } else {
                return 'Just now';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get notification icon
     *
     * @param string $type Notification type
     * @return string Icon
     */
    private function getNotificationIcon(string $type): string
    {
        $icons = [
            'system_alert' => '⚠️',
            'security' => '🔒',
            'content_approval' => '📝',
            'user_mention' => '👤',
            'content_update' => '📄',
            'general' => 'ℹ️'
        ];

        return $icons[$type] ?? 'ℹ️';
    }

    /**
     * Get resource URLs
     *
     * @param string $type Resource type (css or js)
     * @return array Resource URLs
     */
    private function getResourceUrls(string $type): array
    {
        $config = $this->getConfig();
        $resources = $config['resources'][$type] ?? [];
        $urls = [];

        foreach ($resources as $resource) {
            $urls[] = '/extensions/DashboardExtension/' . $resource;
        }

        return $urls;
    }
} 