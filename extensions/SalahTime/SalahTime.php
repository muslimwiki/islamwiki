<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SalahTime;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Core\Islamic\PrayerTimeCalculator;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Salah Time Extension
 *
 * Provides comprehensive Salah time functionality including calculation, display,
 * management, and integration with the wiki system.
 */
class SalahTime extends Extension
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * @var ShahidLogger Logger instance
     */
    private ShahidLogger $logger;

    /**
     * @var array Available calculation methods
     */
    private array $calculationMethods = [];

    /**
     * @var array Salah widgets
     */
    private array $widgets = [];

    /**
     * @var array User locations cache
     */
    private array $userLocations = [];

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadDependencies();
        $this->loadCalculationMethods();
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
        $this->prayerTimeCalculator = new PrayerTimeCalculator($this->logger);
    }

    /**
     * Load available calculation methods
     */
    private function loadCalculationMethods(): void
    {
        $this->calculationMethods = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority of Survey',
            'MAKKAH' => 'Umm Al-Qura University, Makkah',
            'KARACHI' => 'University of Islamic Sciences, Karachi',
            'TEHRAN' => 'Institute of Geophysics, Tehran',
            'JAFARI' => 'Shia Ithna Ashari'
        ];
    }

    /**
     * Load salah widgets
     */
    private function loadWidgets(): void
    {
        $this->widgets = [
            'salah_times' => new SalahTimesWidget($this->prayerTimeCalculator),
            'salah_calculator' => new SalahCalculatorWidget($this->prayerTimeCalculator),
            'qibla_direction' => new QiblaDirectionWidget($this->prayerTimeCalculator),
            'lunar_phase' => new LunarPhaseWidget($this->prayerTimeCalculator)
        ];
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hook for salah time syntax
        $hookManager->register('ContentParse', [$this, 'onContentParse'], 10);

        // Page display hook for salah time content
        $hookManager->register('PageDisplay', [$this, 'onPageDisplay'], 10);

        // Search indexing hook
        $hookManager->register('SearchIndex', [$this, 'onSearchIndex'], 10);

        // Widget rendering hook
        $hookManager->register('WidgetRender', [$this, 'onWidgetRender'], 10);

        // Template loading hook
        $hookManager->register('TemplateLoad', [$this, 'onTemplateLoad'], 10);

        // Admin menu hook
        $hookManager->register('AdminMenu', [$this, 'onAdminMenu'], 10);

        // User profile hook
        $hookManager->register('UserProfile', [$this, 'onUserProfile'], 10);

        // Salah time calculation hook
        $hookManager->register('SalahTimeCalculation', [$this, 'onSalahTimeCalculation'], 10);

        // Location update hook
        $hookManager->register('LocationUpdate', [$this, 'onLocationUpdate'], 10);
    }

    /**
     * Handle content parsing for salah time syntax
     */
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        if ($format === 'markdown') {
            $content = $this->parseSalahTimeSyntax($content);
        }

        return $content;
    }

    /**
     * Parse salah time syntax in markdown content
     */
    private function parseSalahTimeSyntax(string $content): string
    {
        // Parse {{salah-times}} syntax
        $content = preg_replace_callback(
            '/\{\{salah-times(?:\s+([^}]+))?\}\}/',
            [$this, 'renderSalahTimes'],
            $content
        );

        // Parse {{qibla-direction}} syntax
        $content = preg_replace_callback(
            '/\{\{qibla-direction(?:\s+([^}]+))?\}\}/',
            [$this, 'renderQiblaDirection'],
            $content
        );

        // Parse {{lunar-phase}} syntax
        $content = preg_replace_callback(
            '/\{\{lunar-phase(?:\s+([^}]+))?\}\}/',
            [$this, 'renderLunarPhase'],
            $content
        );

        return $content;
    }

    /**
     * Render salah times for the syntax
     */
    private function renderSalahTimes(array $matches): string
    {
        $params = isset($matches[1]) ? $this->parseParams($matches[1]) : [];
        $location = $params['location'] ?? 'default';
        $method = $params['method'] ?? 'MWL';
        $date = $params['date'] ?? date('Y-m-d');

        try {
            $times = $this->calculateSalahTimes($location, $method, $date);
            return $this->renderSalahTimesHtml($times, $params);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render salah times: ' . $e->getMessage());
            return '<div class="salah-error">Error loading salah times</div>';
        }
    }

    /**
     * Render qibla direction for the syntax
     */
    private function renderQiblaDirection(array $matches): string
    {
        $params = isset($matches[1]) ? $this->parseParams($matches[1]) : [];
        $location = $params['location'] ?? 'default';

        try {
            $qibla = $this->calculateQiblaDirection($location);
            return $this->renderQiblaDirectionHtml($qibla, $params);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render qibla direction: ' . $e->getMessage());
            return '<div class="salah-error">Error loading qibla direction</div>';
        }
    }

    /**
     * Render lunar phase for the syntax
     */
    private function renderLunarPhase(array $matches): string
    {
        $params = isset($matches[1]) ? $this->parseParams($matches[1]) : [];
        $date = $params['date'] ?? date('Y-m-d');

        try {
            $lunar = $this->calculateLunarPhase($date);
            return $this->renderLunarPhaseHtml($lunar, $params);
        } catch (\Exception $e) {
            $this->logger->error('Failed to render lunar phase: ' . $e->getMessage());
            return '<div class="salah-error">Error loading lunar phase</div>';
        }
    }

    /**
     * Parse parameters from syntax
     */
    private function parseParams(string $paramString): array
    {
        $params = [];
        preg_match_all('/(\w+)=([^\s]+)/', $paramString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $params[$match[1]] = $match[2];
        }

        return $params;
    }

    /**
     * Handle page display for salah time content
     */
    public function onPageDisplay(array $pageData, array $context = []): array
    {
        if ($this->containsSalahContent($pageData['content'] ?? '')) {
            $pageData['salah_data'] = $this->extractSalahMetadata($pageData['content']);
            $this->loadSalahResources();
        }

        return $pageData;
    }

    /**
     * Check if content contains salah-related content
     */
    private function containsSalahContent(string $content): bool
    {
        return strpos($content, '{{salah-times') !== false ||
               strpos($content, '{{qibla-direction') !== false ||
               strpos($content, '{{lunar-phase') !== false;
    }

    /**
     * Extract salah metadata from content
     */
    private function extractSalahMetadata(string $content): array
    {
        $metadata = [
            'has_salah_times' => false,
            'has_qibla_direction' => false,
            'has_lunar_phase' => false,
            'locations' => [],
            'methods' => []
        ];

        if (preg_match_all('/\{\{salah-times(?:\s+([^}]+))?\}\}/', $content, $matches)) {
            $metadata['has_salah_times'] = true;
            foreach ($matches[1] as $paramString) {
                $params = $this->parseParams($paramString);
                if (isset($params['location'])) {
                    $metadata['locations'][] = $params['location'];
                }
                if (isset($params['method'])) {
                    $metadata['methods'][] = $params['method'];
                }
            }
        }

        if (strpos($content, '{{qibla-direction') !== false) {
            $metadata['has_qibla_direction'] = true;
        }

        if (strpos($content, '{{lunar-phase') !== false) {
            $metadata['has_lunar_phase'] = true;
        }

        return $metadata;
    }

    /**
     * Handle search indexing for salah content
     */
    public function onSearchIndex(array $content, array $context = []): array
    {
        if (isset($content['salah_data'])) {
            $content['searchable_content'] .= ' ' . $this->generateSearchableContent($content['salah_data']);
        }

        return $content;
    }

    /**
     * Generate searchable content from salah data
     */
    private function generateSearchableContent(array $salahData): string
    {
        $searchable = [];

        if ($salahData['has_salah_times']) {
            $searchable[] = 'salah times prayer times';
        }

        if ($salahData['has_qibla_direction']) {
            $searchable[] = 'qibla direction mecca';
        }

        if ($salahData['has_lunar_phase']) {
            $searchable[] = 'lunar phase hijri calendar';
        }

        return implode(' ', $searchable);
    }

    /**
     * Handle widget rendering
     */
    public function onWidgetRender(string $widgetName, array $context = []): ?string
    {
        if (isset($this->widgets[$widgetName])) {
            try {
                return $this->widgets[$widgetName]->render($context);
            } catch (\Exception $e) {
                $this->logger->error("Failed to render widget {$widgetName}: " . $e->getMessage());
                return null;
            }
        }

        return null;
    }

    /**
     * Handle template loading
     */
    public function onTemplateLoad(string $templateName): ?string
    {
        $templatePath = $this->getExtensionPath() . "/templates/{$templateName}.twig";

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return null;
    }

    /**
     * Handle admin menu
     */
    public function onAdminMenu(array $menuItems): array
    {
        $menuItems['salah'] = [
            'title' => 'Salah Times',
            'url' => '/admin/salah',
            'icon' => 'clock',
            'permission' => 'salah_manage',
            'children' => [
                'settings' => [
                    'title' => 'Settings',
                    'url' => '/admin/salah/settings',
                    'permission' => 'salah_edit'
                ],
                'locations' => [
                    'title' => 'Locations',
                    'url' => '/admin/salah/locations',
                    'permission' => 'salah_manage'
                ],
                'methods' => [
                    'title' => 'Calculation Methods',
                    'url' => '/admin/salah/methods',
                    'permission' => 'salah_manage'
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
        $userId = $context['user_id'] ?? null;

        if ($userId) {
            $profileData['salah_preferences'] = $this->getUserSalahPreferences($userId);
            $profileData['salah_locations'] = $this->getUserSalahLocations($userId);
        }

        return $profileData;
    }

    /**
     * Handle salah time calculation
     */
    public function onSalahTimeCalculation(array $data, array $context = []): array
    {
        $location = $data['location'] ?? 'default';
        $method = $data['method'] ?? 'MWL';
        $date = $data['date'] ?? date('Y-m-d');

        try {
            $times = $this->calculateSalahTimes($location, $method, $date);
            $data['calculated_times'] = $times;
            $data['calculation_method'] = $method;
            $data['calculation_success'] = true;
        } catch (\Exception $e) {
            $data['calculation_error'] = $e->getMessage();
            $data['calculation_success'] = false;
            $this->logger->error('Salah time calculation failed: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Handle location update
     */
    public function onLocationUpdate(array $data, array $context = []): array
    {
        $userId = $context['user_id'] ?? null;
        $location = $data['location'] ?? null;

        if ($userId && $location) {
            $this->updateUserLocation($userId, $location);
            $data['location_updated'] = true;
        }

        return $data;
    }

    /**
     * Calculate salah times for a location and date
     */
    public function calculateSalahTimes(string $location, string $method, string $date): array
    {
        $locationData = $this->getLocationData($location);

        if (!$locationData) {
            throw new \Exception("Location '{$location}' not found");
        }

        $dateParts = explode('-', $date);
        $year = (int) $dateParts[0];
        $month = (int) $dateParts[1];
        $day = (int) $dateParts[2];

        return $this->prayerTimeCalculator->calculateTimes(
            $locationData['latitude'],
            $locationData['longitude'],
            $year,
            $month,
            $day,
            $method
        );
    }

    /**
     * Calculate qibla direction for a location
     */
    public function calculateQiblaDirection(string $location): array
    {
        $locationData = $this->getLocationData($location);

        if (!$locationData) {
            throw new \Exception("Location '{$location}' not found");
        }

        return $this->prayerTimeCalculator->calculateQiblaDirection(
            $locationData['latitude'],
            $locationData['longitude']
        );
    }

    /**
     * Calculate lunar phase for a date
     */
    public function calculateLunarPhase(string $date): array
    {
        $dateParts = explode('-', $date);
        $year = (int) $dateParts[0];
        $month = (int) $dateParts[1];
        $day = (int) $dateParts[2];

        $jd = $this->prayerTimeCalculator->gregorianToJulianDay($year, $month, $day);
        return $this->prayerTimeCalculator->calculateLunarPhase($jd);
    }

    /**
     * Get location data
     */
    private function getLocationData(string $location): ?array
    {
        // Default locations
        $defaultLocations = [
            'default' => ['latitude' => 21.4225, 'longitude' => 39.8262, 'name' => 'Makkah'],
            'makkah' => ['latitude' => 21.4225, 'longitude' => 39.8262, 'name' => 'Makkah'],
            'madinah' => ['latitude' => 24.5247, 'longitude' => 39.5692, 'name' => 'Madinah'],
            'istanbul' => ['latitude' => 41.0082, 'longitude' => 28.9784, 'name' => 'Istanbul'],
            'cairo' => ['latitude' => 30.0444, 'longitude' => 31.2357, 'name' => 'Cairo'],
            'jakarta' => ['latitude' => -6.2088, 'longitude' => 106.8456, 'name' => 'Jakarta']
        ];

        return $defaultLocations[$location] ?? null;
    }

    /**
     * Get user salah preferences
     */
    private function getUserSalahPreferences(int $userId): array
    {
        // This would typically query the database
        return [
            'calculation_method' => 'MWL',
            'notifications_enabled' => true,
            'timezone' => 'UTC'
        ];
    }

    /**
     * Get user salah locations
     */
    private function getUserSalahLocations(int $userId): array
    {
        // This would typically query the database
        return [
            'primary' => 'default',
            'saved_locations' => ['makkah', 'madinah']
        ];
    }

    /**
     * Update user location
     */
    private function updateUserLocation(int $userId, array $location): void
    {
        // This would typically update the database
        $this->userLocations[$userId] = $location;
    }

    /**
     * Get calculation methods
     */
    public function getCalculationMethods(): array
    {
        return $this->calculationMethods;
    }

    /**
     * Get widgets
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * Search locations
     */
    public function searchLocations(string $query): array
    {
        // This would typically search a locations database
        $locations = [
            'makkah' => 'Makkah, Saudi Arabia',
            'madinah' => 'Madinah, Saudi Arabia',
            'istanbul' => 'Istanbul, Turkey',
            'cairo' => 'Cairo, Egypt',
            'jakarta' => 'Jakarta, Indonesia'
        ];

        $results = [];
        foreach ($locations as $key => $name) {
            if (stripos($name, $query) !== false) {
                $results[$key] = $name;
            }
        }

        return $results;
    }

    /**
     * Get extension information
     */
    public function getExtensionInfo(): array
    {
        return [
            'name' => 'SalahTime',
            'version' => '0.0.1',
            'description' => 'Comprehensive Salah time system',
            'author' => 'IslamWiki Team',
            'calculation_methods' => $this->calculationMethods,
            'widgets' => array_keys($this->widgets)
        ];
    }
}
