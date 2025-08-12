<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HijriCalendar;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Hijri Calendar Extension
 *
 * Provides comprehensive Hijri calendar functionality including date conversion,
 * display, Islamic events, and integration with the wiki system.
 */
class HijriCalendar extends Extension
{
    /**
     * @var ShahidLogger Logger instance
     */
    private ShahidLogger $logger;

    /**
     * @var HijriDateConverter Date converter instance
     */
    private HijriDateConverter $dateConverter;

    /**
     * @var IslamicEventsManager Islamic events manager
     */
    private IslamicEventsManager $eventsManager;

    /**
     * @var LunarPhaseCalculator Lunar phase calculator
     */
    private LunarPhaseCalculator $lunarPhaseCalculator;

    /**
     * @var array Available locales
     */
    private array $locales = [];

    /**
     * @var array Hijri widgets
     */
    private array $widgets = [];

    /**
     * @var array User preferences cache
     */
    private array $userPreferences = [];

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadDependencies();
        $this->loadLocales();
        $this->loadWidgets();
        $this->registerHooks();
        $this->loadResources();
    }

    /**
     * Load extension dependencies
     */
    private function loadDependencies(): void
    {
        $this->logger = new ShahidLogger($this->getExtensionPath() . '/logs');
        $this->dateConverter = new HijriDateConverter($this->logger);
        $this->eventsManager = new IslamicEventsManager($this->logger);
        $this->lunarPhaseCalculator = new LunarPhaseCalculator($this->logger);
    }

    /**
     * Load available locales
     */
    private function loadLocales(): void
    {
        $this->locales = [
            'en' => 'English',
            'ar' => 'العربية',
            'ur' => 'اردو',
            'tr' => 'Türkçe',
            'ms' => 'Bahasa Melayu',
            'id' => 'Bahasa Indonesia'
        ];
    }

    /**
     * Load Hijri widgets
     */
    private function loadWidgets(): void
    {
        $this->widgets = [
            'hijri_date' => new HijriDateWidget($this->dateConverter),
            'hijri_converter' => new HijriConverterWidget($this->dateConverter),
            'hijri_calendar' => new HijriCalendarWidget($this->dateConverter),
            'islamic_events' => new IslamicEventsWidget($this->eventsManager),
            'lunar_phase' => new LunarPhaseWidget($this->lunarPhaseCalculator)
        ];
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hooks
        $hookManager->register('ContentParse', [$this, 'onContentParse']);
        $hookManager->register('PageDisplay', [$this, 'onPageDisplay']);
        $hookManager->register('SearchIndex', [$this, 'onSearchIndex']);

        // Widget and template hooks
        $hookManager->register('WidgetRender', [$this, 'onWidgetRender']);
        $hookManager->register('TemplateLoad', [$this, 'onTemplateLoad']);

        // Admin and user hooks
        $hookManager->register('AdminMenu', [$this, 'onAdminMenu']);
        $hookManager->register('UserProfile', [$this, 'onUserProfile']);

        // Custom Hijri hooks
        $hookManager->register('HijriDateConversion', [$this, 'onHijriDateConversion']);
        $hookManager->register('IslamicEventCalculation', [$this, 'onIslamicEventCalculation']);
    }

    /**
     * Parse content for Hijri calendar syntax
     */
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        if (!$this->getConfigValue('enableHijriDisplay', true)) {
            return $content;
        }

        // Parse Hijri date syntax: {{hijri:YYYY-MM-DD}}
        $content = $this->parseHijriDateSyntax($content);

        // Parse Hijri calendar syntax: {{hijri_calendar:month|year}}
        $content = $this->parseHijriCalendarSyntax($content);

        // Parse Islamic events syntax: {{islamic_events:month}}
        $content = $this->parseIslamicEventsSyntax($content);

        return $content;
    }

    /**
     * Parse Hijri date syntax
     */
    private function parseHijriDateSyntax(string $content): string
    {
        $pattern = '/\{\{hijri:([^}]+)\}\}/';
        return preg_replace_callback($pattern, [$this, 'renderHijriDate'], $content);
    }

    /**
     * Parse Hijri calendar syntax
     */
    private function parseHijriCalendarSyntax(string $content): string
    {
        $pattern = '/\{\{hijri_calendar:([^}]+)\}\}/';
        return preg_replace_callback($pattern, [$this, 'renderHijriCalendar'], $content);
    }

    /**
     * Parse Islamic events syntax
     */
    private function parseIslamicEventsSyntax(string $content): string
    {
        $pattern = '/\{\{islamic_events:([^}]+)\}\}/';
        return preg_replace_callback($pattern, [$this, 'renderIslamicEvents'], $content);
    }

    /**
     * Render Hijri date
     */
    private function renderHijriDate(array $matches): string
    {
        $dateString = trim($matches[1]);
        $params = $this->parseParams($dateString);

        $date = $params['date'] ?? 'today';
        $locale = $params['locale'] ?? $this->getConfigValue('defaultLocale', 'en');
        $format = $params['format'] ?? 'full';

        try {
            $hijriDate = $this->dateConverter->convertToHijri($date);
            return $this->formatHijriDate($hijriDate, $locale, $format);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render Hijri date: ' . $e->getMessage());
            return '<span class="error">Invalid Hijri date</span>';
        }
    }

    /**
     * Render Hijri calendar
     */
    private function renderHijriCalendar(array $matches): string
    {
        $params = $this->parseParams($matches[1]);

        $month = $params['month'] ?? date('n');
        $year = $params['year'] ?? date('Y');
        $view = $params['view'] ?? $this->getConfigValue('defaultCalendarView', 'month');

        try {
            $calendarData = $this->generateCalendarData($month, $year, $view);
            return $this->renderCalendarTemplate($calendarData, $view);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render Hijri calendar: ' . $e->getMessage());
            return '<span class="error">Failed to generate calendar</span>';
        }
    }

    /**
     * Render Islamic events
     */
    private function renderIslamicEvents(array $matches): string
    {
        $params = $this->parseParams($matches[1]);

        $month = $params['month'] ?? date('n');
        $year = $params['year'] ?? date('Y');
        $locale = $params['locale'] ?? $this->getConfigValue('defaultLocale', 'en');

        try {
            $events = $this->eventsManager->getEventsForMonth($month, $year, $locale);
            return $this->renderEventsTemplate($events, $locale);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render Islamic events: ' . $e->getMessage());
            return '<span class="error">Failed to load events</span>';
        }
    }

    /**
     * Parse parameters from string
     */
    private function parseParams(string $paramString): array
    {
        $params = [];
        $pairs = explode('|', $paramString);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                [$key, $value] = explode('=', $pair, 2);
                $params[trim($key)] = trim($value);
            } else {
                $params[] = trim($pair);
            }
        }
        
        return $params;
    }

    /**
     * Handle page display
     */
    public function onPageDisplay(array $pageData, array $context = []): array
    {
        if (!$this->getConfigValue('enableHijriDisplay', true)) {
            return $pageData;
        }

        // Add Hijri date to page metadata
        if ($this->containsHijriContent($pageData['content'] ?? '')) {
            $pageData['metadata']['hijri_date'] = $this->getCurrentHijriDate();
            $pageData['metadata']['islamic_events'] = $this->getUpcomingEvents();
        }

        return $pageData;
    }

    /**
     * Check if content contains Hijri-related content
     */
    private function containsHijriContent(string $content): bool
    {
        return strpos($content, '{{hijri:') !== false ||
               strpos($content, '{{hijri_calendar:') !== false ||
               strpos($content, '{{islamic_events:') !== false;
    }

    /**
     * Get current Hijri date
     */
    private function getCurrentHijriDate(): array
    {
        try {
            return $this->dateConverter->convertToHijri('today');
        } catch (\Exception $e) {
            $this->logger->error('Failed to get current Hijri date: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming Islamic events
     */
    private function getUpcomingEvents(): array
    {
        try {
            $currentMonth = (int)date('n');
            $currentYear = (int)date('Y');
            return $this->eventsManager->getUpcomingEvents($currentMonth, $currentYear);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get upcoming events: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle search indexing
     */
    public function onSearchIndex(array $content, array $context = []): array
    {
        if (!$this->getConfigValue('enableHijriDisplay', true)) {
            return $content;
        }

        // Add Hijri dates and Islamic events to search index
        if ($this->containsHijriContent($content['text'] ?? '')) {
            $hijriData = $this->extractHijriData($content['text']);
            $content['searchable_content'] .= ' ' . $this->generateSearchableContent($hijriData);
        }

        return $content;
    }

    /**
     * Extract Hijri data from content
     */
    private function extractHijriData(string $content): array
    {
        $data = [];
        
        // Extract Hijri dates
        preg_match_all('/\{\{hijri:([^}]+)\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $data['hijri_dates'] = $matches[1];
        }

        // Extract Islamic events
        preg_match_all('/\{\{islamic_events:([^}]+)\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $data['islamic_events'] = $matches[1];
        }

        return $data;
    }

    /**
     * Generate searchable content from Hijri data
     */
    private function generateSearchableContent(array $hijriData): string
    {
        $searchable = [];

        if (isset($hijriData['hijri_dates'])) {
            foreach ($hijriData['hijri_dates'] as $date) {
                try {
                    $hijriDate = $this->dateConverter->convertToHijri($date);
                    $searchable[] = "Hijri date: {$hijriDate['day']} {$hijriDate['month_name']} {$hijriDate['year']}";
                } catch (\Exception $e) {
                    // Skip invalid dates
                }
            }
        }

        if (isset($hijriData['islamic_events'])) {
            foreach ($hijriData['islamic_events'] as $event) {
                $searchable[] = "Islamic events: $event";
            }
        }

        return implode(' ', $searchable);
    }

    /**
     * Handle widget rendering
     */
    public function onWidgetRender(string $widgetName, array $context = []): ?string
    {
        if (!$this->getConfigValue('enableHijriWidgets', true)) {
            return null;
        }

        if (isset($this->widgets[$widgetName])) {
            try {
                return $this->widgets[$widgetName]->render($context);
            } catch (\Exception $e) {
                $this->logger->error("Failed to render widget $widgetName: " . $e->getMessage());
                return '<span class="error">Widget error</span>';
            }
        }

        return null;
    }

    /**
     * Handle template loading
     */
    public function onTemplateLoad(string $templateName): ?string
    {
        if (!$this->getConfigValue('enableHijriTemplates', true)) {
            return null;
        }

        $extensionPath = $this->getExtensionPath();
        $templateFile = $extensionPath . '/templates/' . $templateName . '.twig';

        if (file_exists($templateFile)) {
            return file_get_contents($templateFile);
        }

        return null;
    }

    /**
     * Handle admin menu
     */
    public function onAdminMenu(array $menuItems): array
    {
        if (!$this->getConfigValue('enableHijriDisplay', true)) {
            return $menuItems;
        }

        $menuItems['hijri_calendar'] = [
            'title' => 'Hijri Calendar',
            'url' => '/admin/hijri',
            'icon' => 'calendar',
            'permission' => 'hijri_manage',
            'children' => [
                'settings' => [
                    'title' => 'Settings',
                    'url' => '/admin/hijri/settings'
                ],
                'events' => [
                    'title' => 'Manage Events',
                    'url' => '/admin/hijri/events'
                ],
                'import' => [
                    'title' => 'Import Data',
                    'url' => '/admin/hijri/import'
                ]
            ]
        ];

        return $menuItems;
    }

    /**
     * Handle user profile
     */
    public function onUserProfile(array $profileData, array $context = []): array
    {
        if (!$this->getConfigValue('enableHijriDisplay', true)) {
            return $profileData;
        }

        $userId = $context['user_id'] ?? null;
        if ($userId) {
            $preferences = $this->getUserHijriPreferences($userId);
            $profileData['hijri_preferences'] = $preferences;
        }

        return $profileData;
    }

    /**
     * Handle Hijri date conversion hook
     */
    public function onHijriDateConversion(array $data, array $context = []): array
    {
        if (!$this->getConfigValue('enableDateConversion', true)) {
            return $data;
        }

        try {
            $date = $data['date'] ?? 'today';
            $direction = $data['direction'] ?? 'to_hijri';
            $locale = $data['locale'] ?? $this->getConfigValue('defaultLocale', 'en');

            if ($direction === 'to_hijri') {
                $result = $this->dateConverter->convertToHijri($date);
            } else {
                $result = $this->dateConverter->convertToGregorian($date);
            }

            $data['result'] = $result;
            $data['success'] = true;
        } catch (\Exception $e) {
            $this->logger->error('Hijri date conversion failed: ' . $e->getMessage());
            $data['error'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }

    /**
     * Handle Islamic event calculation hook
     */
    public function onIslamicEventCalculation(array $data, array $context = []): array
    {
        if (!$this->getConfigValue('enableIslamicEvents', true)) {
            return $data;
        }

        try {
            $month = $data['month'] ?? date('n');
            $year = $data['year'] ?? date('Y');
            $locale = $data['locale'] ?? $this->getConfigValue('defaultLocale', 'en');

            $events = $this->eventsManager->getEventsForMonth($month, $year, $locale);
            $data['events'] = $events;
            $data['success'] = true;
        } catch (\Exception $e) {
            $this->logger->error('Islamic event calculation failed: ' . $e->getMessage());
            $data['error'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }

    /**
     * Convert Gregorian date to Hijri
     */
    public function convertToHijri(string $date): array
    {
        try {
            return $this->dateConverter->convertToHijri($date);
        } catch (\Exception $e) {
            $this->logger->error('Date conversion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Hijri date to Gregorian
     */
    public function convertToGregorian(string $hijriDate): array
    {
        try {
            return $this->dateConverter->convertToGregorian($hijriDate);
        } catch (\Exception $e) {
            $this->logger->error('Date conversion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Islamic events for month
     */
    public function getIslamicEvents(int $month, int $year, string $locale = 'en'): array
    {
        try {
            return $this->eventsManager->getEventsForMonth($month, $year, $locale);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Islamic events: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get lunar phase for date
     */
    public function getLunarPhase(string $date): array
    {
        try {
            return $this->lunarPhaseCalculator->calculatePhase($date);
        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate lunar phase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate calendar data
     */
    private function generateCalendarData(int $month, int $year, string $view): array
    {
        $data = [];
        
        switch ($view) {
            case 'month':
                $data = $this->generateMonthView($month, $year);
                break;
            case 'year':
                $data = $this->generateYearView($year);
                break;
            case 'week':
                $data = $this->generateWeekView($month, $year);
                break;
            case 'day':
                $data = $this->generateDayView($month, $year);
                break;
        }

        return $data;
    }

    /**
     * Generate month view data
     */
    private function generateMonthView(int $month, int $year): array
    {
        $data = [
            'view' => 'month',
            'month' => $month,
            'year' => $year,
            'weeks' => []
        ];

        // Get first day of month and number of days
        $firstDay = $this->dateConverter->getFirstDayOfMonth($month, $year);
        $daysInMonth = $this->dateConverter->getDaysInMonth($month, $year);

        // Generate weeks
        $currentDay = 1;
        $week = [];
        
        // Add empty cells for days before month starts
        for ($i = 0; $i < $firstDay; $i++) {
            $week[] = null;
        }

        // Add days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $week[] = [
                'day' => $day,
                'hijri_date' => $this->dateConverter->getHijriDateForDay($day, $month, $year),
                'events' => $this->eventsManager->getEventsForDay($day, $month, $year)
            ];

            if (count($week) === 7) {
                $data['weeks'][] = $week;
                $week = [];
            }
        }

        // Add remaining days to last week
        if (!empty($week)) {
            while (count($week) < 7) {
                $week[] = null;
            }
            $data['weeks'][] = $week;
        }

        return $data;
    }

    /**
     * Generate year view data
     */
    private function generateYearView(int $year): array
    {
        $data = [
            'view' => 'year',
            'year' => $year,
            'months' => []
        ];

        for ($month = 1; $month <= 12; $month++) {
            $data['months'][$month] = [
                'name' => $this->getMonthName($month),
                'days' => $this->dateConverter->getDaysInMonth($month, $year),
                'events' => $this->eventsManager->getEventsForMonth($month, $year)
            ];
        }

        return $data;
    }

    /**
     * Generate week view data
     */
    private function generateWeekView(int $month, int $year): array
    {
        // Implementation for week view
        return [
            'view' => 'week',
            'month' => $month,
            'year' => $year,
            'days' => []
        ];
    }

    /**
     * Generate day view data
     */
    private function generateDayView(int $month, int $year): array
    {
        // Implementation for day view
        return [
            'view' => 'day',
            'month' => $month,
            'year' => $year,
            'day' => date('j')
        ];
    }

    /**
     * Get month name
     */
    private function getMonthName(int $month): string
    {
        $monthNames = [
            1 => 'Muharram', 2 => 'Safar', 3 => 'Rabi al-Awwal',
            4 => 'Rabi al-Thani', 5 => 'Jumada al-Awwal', 6 => 'Jumada al-Thani',
            7 => 'Rajab', 8 => 'Shaban', 9 => 'Ramadan',
            10 => 'Shawwal', 11 => 'Dhu al-Qadah', 12 => 'Dhu al-Hijjah'
        ];

        return $monthNames[$month] ?? 'Unknown';
    }

    /**
     * Render calendar template
     */
    private function renderCalendarTemplate(array $data, string $view): string
    {
        // This would render the appropriate template based on the view
        // For now, return a simple HTML representation
        $html = "<div class='hijri-calendar hijri-calendar-{$view}'>";
        $html .= "<h3>Hijri Calendar - {$view}</h3>";
        
        switch ($view) {
            case 'month':
                $html .= $this->renderMonthView($data);
                break;
            case 'year':
                $html .= $this->renderYearView($data);
                break;
            default:
                $html .= "<p>View type '{$view}' not implemented</p>";
        }
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Render month view
     */
    private function renderMonthView(array $data): string
    {
        $html = "<table class='hijri-month-calendar'>";
        $html .= "<thead><tr>";
        
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        foreach ($dayNames as $dayName) {
            $html .= "<th>{$dayName}</th>";
        }
        $html .= "</tr></thead><tbody>";

        foreach ($data['weeks'] as $week) {
            $html .= "<tr>";
            foreach ($week as $day) {
                if ($day === null) {
                    $html .= "<td class='empty'></td>";
                } else {
                    $hijriDate = $day['hijri_date'];
                    $html .= "<td class='day' data-hijri='{$hijriDate['day']} {$hijriDate['month_name']} {$hijriDate['year']}'>";
                    $html .= "<span class='gregorian-day'>{$day['day']}</span>";
                    $html .= "<span class='hijri-day'>{$hijriDate['day']}</span>";
                    $html .= "</td>";
                }
            }
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Render year view
     */
    private function renderYearView(array $data): string
    {
        $html = "<div class='hijri-year-calendar'>";
        $html .= "<h4>{$data['year']} CE</h4>";
        $html .= "<div class='year-months'>";

        foreach ($data['months'] as $monthNum => $month) {
            $html .= "<div class='year-month'>";
            $html .= "<h5>{$month['name']}</h5>";
            $html .= "<p>Days: {$month['days']}</p>";
            $html .= "<p>Events: " . count($month['events']) . "</p>";
            $html .= "</div>";
        }

        $html .= "</div></div>";
        return $html;
    }

    /**
     * Render events template
     */
    private function renderEventsTemplate(array $events, string $locale): string
    {
        if (empty($events)) {
            return "<div class='islamic-events'><p>No events for this period</p></div>";
        }

        $html = "<div class='islamic-events'>";
        $html .= "<h3>Islamic Events</h3>";
        $html .= "<ul>";

        foreach ($events as $event) {
            $html .= "<li class='event event-{$event['type']}'>";
            $html .= "<span class='event-date'>{$event['date']}</span>";
            $html .= "<span class='event-name'>{$event['name']}</span>";
            if (isset($event['description'])) {
                $html .= "<span class='event-description'>{$event['description']}</span>";
            }
            $html .= "</li>";
        }

        $html .= "</ul></div>";
        return $html;
    }

    /**
     * Format Hijri date
     */
    private function formatHijriDate(array $hijriDate, string $locale, string $format): string
    {
        switch ($format) {
            case 'short':
                return "{$hijriDate['day']}/{$hijriDate['month']}/{$hijriDate['year']}";
            case 'medium':
                return "{$hijriDate['day']} {$hijriDate['month_name']} {$hijriDate['year']}";
            case 'full':
            default:
                return "{$hijriDate['day']} {$hijriDate['month_name']} {$hijriDate['year']} AH";
        }
    }

    /**
     * Get user Hijri preferences
     */
    private function getUserHijriPreferences(int $userId): array
    {
        if (isset($this->userPreferences[$userId])) {
            return $this->userPreferences[$userId];
        }

        // In a real implementation, this would fetch from database
        $preferences = [
            'locale' => $this->getConfigValue('defaultLocale', 'en'),
            'calendar_view' => $this->getConfigValue('defaultCalendarView', 'month'),
            'show_events' => true,
            'show_lunar_phases' => true,
            'notifications_enabled' => $this->getConfigValue('enableNotifications', true)
        ];

        $this->userPreferences[$userId] = $preferences;
        return $preferences;
    }

    /**
     * Get available locales
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Get widgets
     */
    public function getWidgets(): array
    {
        return array_keys($this->widgets);
    }

    /**
     * Get extension information
     */
    public function getExtensionInfo(): array
    {
        $info = parent::toArray();
        $info['locales'] = $this->locales;
        $info['widgets'] = $this->getWidgets();
        $info['calculation_methods'] = $this->dateConverter->getSupportedMethods();
        
        return $info;
    }
}
