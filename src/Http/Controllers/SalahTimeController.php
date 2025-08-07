<?php

/**
 * SalahTimeController
 *
 * This controller handles salah time requests, user locations,
 * notifications, and preferences for the IslamWiki application.
 *
 * @package IslamWiki
 * @version 0.0.16
 * @license AGPL-3.0
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Models\SalahTime;
use IslamWiki\Models\User;

class SalahTimeController extends Controller
{
    protected $salahTime;

    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->salahTime = new SalahTime($db);
    }

    /**
     * Salah times index page
     */
    public function index(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $preferences = $this->salahTime->getUserPreferences($userId);
            $locations = $this->salahTime->getUserLocations($userId);
            $statistics = $this->salahTime->getStatistics();

            $defaultLocation = null;
            foreach ($locations as $location) {
                if ($location['is_default']) {
                    $defaultLocation = $location;
                    break;
                }
            }

            // Get today's salah times for default location
            $todaySalahTimes = null;
            if ($defaultLocation) {
                $todaySalahTimes = $this->salahTime->getSalahTimes(
                    date('Y-m-d'),
                    $defaultLocation['latitude'],
                    $defaultLocation['longitude'],
                    $defaultLocation['timezone'],
                    $preferences['calculation_method'],
                    $preferences['asr_juristic'],
                    $preferences['adjust_high_lats'],
                    $preferences['minutes_offset']
                );
            }

            $nextPrayer = null;
            if ($todaySalahTimes) {
                $nextPrayer = $this->salahTime->getNextPrayer($todaySalahTimes['prayer_times']);
            }

            return $this->view('salah/index', [
                'title' => 'Salah Times',
                'preferences' => $preferences,
                'locations' => $locations,
                'defaultLocation' => $defaultLocation,
                'todaySalahTimes' => $todaySalahTimes,
                'nextPrayer' => $nextPrayer,
                'statistics' => $statistics,
                'calculationMethods' => $this->salahTime->getCalculationMethods(),
                'asrJuristicMethods' => $this->salahTime->getAsrJuristicMethods(),
                'prayerNames' => $this->salahTime->getPrayerNames($preferences['language'])
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading salah times: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Prayer times for specific date and location
     */
    public function show(Request $request, $date = null, $locationId = null)
    {
        try {
            $userId = $this->getUserId($request);
            $date = $date ?? date('Y-m-d');
            $preferences = $this->salahTime->getUserPreferences($userId);

            // Get location
            $location = null;
            if ($locationId) {
                $locations = $this->salahTime->getUserLocations($userId);
                foreach ($locations as $loc) {
                    if ($loc['id'] == $locationId) {
                        $location = $loc;
                        break;
                    }
                }
            } else {
                // Get default location
                $locations = $this->salahTime->getUserLocations($userId);
                foreach ($locations as $loc) {
                    if ($loc['is_default']) {
                        $location = $loc;
                        break;
                    }
                }
            }

            if (!$location) {
                return new Response('Location not found: Please add a location to view salah times.', 404);
            }

            // Get salah times
            $prayerTimes = $this->salahTime->getSalahTimes(
                $date,
                $location['latitude'],
                $location['longitude'],
                $location['timezone'],
                $preferences['calculation_method'],
                $preferences['asr_juristic'],
                $preferences['adjust_high_lats'],
                $preferences['minutes_offset']
            );

            // Calculate Qibla direction
            $qiblaDirection = $this->salahTime->calculateQiblaDirection(
                $location['latitude'],
                $location['longitude']
            );

            // Get next prayer
            $nextPrayer = $this->salahTime->getNextPrayer($prayerTimes['salah_times']);

            return $this->view('salah/show', [
                'title' => "Salah Times - {$date}",
                'date' => $date,
                'location' => $location,
                'prayerTimes' => $prayerTimes,
                'nextPrayer' => $nextPrayer,
                'qiblaDirection' => $qiblaDirection,
                'preferences' => $preferences,
                'prayerNames' => $this->salahTime->getPrayerNames($preferences['language'])
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading salah times: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Prayer times search page
     */
    public function search(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $preferences = $this->salahTime->getUserPreferences($userId);
            $calculationMethods = $this->salahTime->getCalculationMethods();
            $asrJuristicMethods = $this->salahTime->getAsrJuristicMethods();

            return $this->view('salah/search', [
                'title' => 'Search Salah Times',
                'preferences' => $preferences,
                'calculationMethods' => $calculationMethods,
                'asrJuristicMethods' => $asrJuristicMethods
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading search page: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Prayer time widget
     */
    public function widget(Request $request, $widgetKey = null)
    {
        try {
            if (!$widgetKey) {
                return new Response('Widget key required', 400);
            }

            // Get widget configuration
            $query = new \Core\Database\Query\Builder($this->connection);
            $widget = $query->table('prayer_widgets')
                ->where('widget_key', $widgetKey)
                ->where('is_active', true)
                ->first();

            if (!$widget) {
                return new Response('Widget not found', 404);
            }

            // Get salah times
            $prayerTimes = $this->salahTime->getSalahTimes(
                date('Y-m-d'),
                $widget['latitude'],
                $widget['longitude'],
                $widget['timezone'],
                $widget['calculation_method'],
                'Standard',
                true,
                0
            );

            // Update view count
            $query->table('prayer_widgets')
                ->where('widget_key', $widgetKey)
                ->update(['view_count' => $widget['view_count'] + 1]);

            return $this->view('salah/widget', [
                'widget' => $widget,
                'prayerTimes' => $prayerTimes,
                'prayerNames' => $this->salahTime->getPrayerNames($widget['language'])
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading widget', 500);
        }
    }

    /**
     * User locations management
     */
    public function locations(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $locations = $this->salahTime->getUserLocations($userId);

            return $this->view('salah/locations', [
                'title' => 'My Locations',
                'locations' => $locations
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading locations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User preferences page
     */
    public function preferences(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $preferences = $this->salahTime->getUserPreferences($userId);
            $calculationMethods = $this->salahTime->getCalculationMethods();
            $asrJuristicMethods = $this->salahTime->getAsrJuristicMethods();

            return $this->view('salah/preferences', [
                'title' => 'Prayer Preferences',
                'preferences' => $preferences,
                'calculationMethods' => $calculationMethods,
                'asrJuristicMethods' => $asrJuristicMethods
            ]);
        } catch (\Exception $e) {
            return new Response('Error loading preferences: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Get prayer times
     */
    public function apiGetPrayerTimes(Request $request)
    {
        try {
            $date = $request->getQueryParam('date', date('Y-m-d'));
            $latitude = $request->getQueryParam('latitude');
            $longitude = $request->getQueryParam('longitude');
            $timezone = $request->getQueryParam('timezone', 'UTC');
            $method = $request->getQueryParam('method', 'MWL');
            $asrJuristic = $request->getQueryParam('asr_juristic', 'Standard');
            $adjustHighLats = $request->getQueryParam('adjust_high_lats', true);
            $minutesOffset = $request->getQueryParam('minutes_offset', 0);

            if (!$latitude || !$longitude) {
                return new Response(json_encode([
                    'error' => 'Latitude and longitude are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $startTime = microtime(true);
            $prayerTimes = $this->salahTime->getSalahTimes(
                $date,
                $latitude,
                $longitude,
                $timezone,
                $method,
                $asrJuristic,
                $adjustHighLats,
                $minutesOffset
            );
            $responseTime = (microtime(true) - $startTime) * 1000;

            // Update statistics
            $this->salahTime->updateStatistics('api', $responseTime);

            return new Response(json_encode([
                'success' => true,
                'data' => $prayerTimes,
                'response_time' => round($responseTime, 2)
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get user locations
     */
    public function apiGetLocations(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $locations = $this->salahTime->getUserLocations($userId);

            return new Response(json_encode([
                'success' => true,
                'data' => $locations
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Add user location
     */
    public function apiAddLocation(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $data = json_decode($request->getBody(), true);

            $required = ['name', 'city', 'country', 'latitude', 'longitude', 'timezone'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    return new Response(json_encode([
                        'error' => "Field '{$field}' is required"
                    ]), 400, ['Content-Type' => 'application/json']);
                }
            }

            $locationId = $this->salahTime->addUserLocation($userId, $data);

            return new Response(json_encode([
                'success' => true,
                'data' => ['id' => $locationId]
            ]), 201, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get user preferences
     */
    public function apiGetPreferences(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $preferences = $this->salahTime->getUserPreferences($userId);

            return new Response(json_encode([
                'success' => true,
                'data' => $preferences
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Update user preferences
     */
    public function apiUpdatePreferences(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            $data = json_decode($request->getBody(), true);

            $this->salahTime->updateUserPreferences($userId, $data);

            return new Response(json_encode([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Calculate Qibla direction
     */
    public function apiCalculateQibla(Request $request)
    {
        try {
            $latitude = $request->getQueryParam('latitude');
            $longitude = $request->getQueryParam('longitude');

            if (!$latitude || !$longitude) {
                return new Response(json_encode([
                    'error' => 'Latitude and longitude are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $qiblaDirection = $this->salahTime->calculateQiblaDirection($latitude, $longitude);

            return new Response(json_encode([
                'success' => true,
                'data' => [
                    'direction' => $qiblaDirection,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get next prayer
     */
    public function apiGetNextPrayer(Request $request)
    {
        try {
            $latitude = $request->getQueryParam('latitude');
            $longitude = $request->getQueryParam('longitude');
            $timezone = $request->getQueryParam('timezone', 'UTC');
            $method = $request->getQueryParam('method', 'MWL');
            $asrJuristic = $request->getQueryParam('asr_juristic', 'Standard');
            $adjustHighLats = $request->getQueryParam('adjust_high_lats', true);
            $minutesOffset = $request->getQueryParam('minutes_offset', 0);

            if (!$latitude || !$longitude) {
                return new Response(json_encode([
                    'error' => 'Latitude and longitude are required'
                ]), 400, ['Content-Type' => 'application/json']);
            }

            $prayerTimes = $this->salahTime->getSalahTimes(
                date('Y-m-d'),
                $latitude,
                $longitude,
                $timezone,
                $method,
                $asrJuristic,
                $adjustHighLats,
                $minutesOffset
            );

            $nextPrayer = $this->salahTime->getNextPrayer($prayerTimes['salah_times']);

            return new Response(json_encode([
                'success' => true,
                'data' => $nextPrayer
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get prayer statistics
     */
    public function apiGetStatistics(Request $request)
    {
        try {
            $statistics = $this->salahTime->getStatistics();

            return new Response(json_encode([
                'success' => true,
                'data' => $statistics
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get calculation methods
     */
    public function apiGetCalculationMethods(Request $request)
    {
        try {
            $methods = $this->salahTime->getCalculationMethods();
            $asrMethods = $this->salahTime->getAsrJuristicMethods();

            return new Response(json_encode([
                'success' => true,
                'data' => [
                    'calculation_methods' => $methods,
                    'asr_juristic_methods' => $asrMethods
                ]
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * API: Get prayer names
     */
    public function apiGetPrayerNames(Request $request)
    {
        try {
            $language = $request->getQueryParam('language', 'en');
            $prayerNames = $this->salahTime->getPrayerNames($language);

            return new Response(json_encode([
                'success' => true,
                'data' => $prayerNames
            ]), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'error' => $e->getMessage()
            ]), 500, ['Content-Type' => 'application/json']);
        }
    }

    /**
     * Get user ID from request
     */
    protected function getUserId(Request $request)
    {
        // This would typically get the user ID from the session
        // For now, return a default user ID
        return 1;
    }
}
