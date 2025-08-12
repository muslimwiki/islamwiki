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
                $nextPrayer = $this->salahTime->getNextSalah($todaySalahTimes['salah_times']);
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
                'prayerNames' => $this->salahTime->getSalahNames($preferences['language'])
            ]);
        } catch (\Exception $e) {
            return new Response(500, [], 'Error loading salah times: ' . $e->getMessage());
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
                return new Response(404, [], 'Location not found: Please add a location to view salah times.');
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
            $nextPrayer = $this->salahTime->getNextSalah($prayerTimes['salah_times']);

            return $this->view('salah/show', [
                'title' => "Salah Times - {$date}",
                'date' => $date,
                'location' => $location,
                'prayerTimes' => $prayerTimes,
                'nextPrayer' => $nextPrayer,
                'qiblaDirection' => $qiblaDirection,
                'preferences' => $preferences,
                'prayerNames' => $this->salahTime->getSalahNames($preferences['language'])
            ]);
        } catch (\Exception $e) {
            return new Response(500, [], 'Error loading salah times: ' . $e->getMessage());
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
            return new Response(500, [], 'Error loading search page: ' . $e->getMessage());
        }
    }

    /**
     * Prayer time widget
     */
    public function widget(Request $request, $widgetKey = null)
    {
        try {
            if (!$widgetKey) {
                return new Response(400, [], 'Widget key required');
            }

            // Get widget configuration
            $query = new \IslamWiki\Core\Database\Query\Builder($this->db);
            $widget = $query->from('prayer_widgets')
                ->where('widget_key', $widgetKey)
                ->where('is_active', true)
                ->first();

            if (!$widget) {
                return new Response(404, [], 'Widget not found');
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
            $query->from('prayer_widgets')
                ->where('widget_key', $widgetKey)
                ->update(['view_count' => $widget['view_count'] + 1]);

            return $this->view('salah/widget', [
                'widget' => $widget,
                'prayerTimes' => $prayerTimes,
                'prayerNames' => $this->salahTime->getSalahNames($widget['language'])
            ]);
        } catch (\Exception $e) {
            return new Response(500, [], 'Error loading widget');
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
            return new Response(500, [], 'Error loading locations: ' . $e->getMessage());
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
            return new Response(500, [], 'Error loading preferences: ' . $e->getMessage());
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
                return Response::json([
                    'error' => 'Latitude and longitude are required'
                ], 400);
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

            return Response::json([
                'success' => true,
                'data' => $prayerTimes,
                'response_time' => round($responseTime, 2)
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            return Response::json([
                'success' => true,
                'data' => $locations
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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
                    return Response::json([
                        'error' => "Field '{$field}' is required"
                    ], 400);
                }
            }

            $locationId = $this->salahTime->addUserLocation($userId, $data);

            return Response::json([
                'success' => true,
                'data' => ['id' => $locationId]
            ], 201);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            return Response::json([
                'success' => true,
                'data' => $preferences
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            return Response::json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            return Response::json([
                'success' => true,
                'data' => [
                    'direction' => $qiblaDirection,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            $nextPrayer = $this->salahTime->getNextSalah($prayerTimes['salah_times']);

            return Response::json([
                'success' => true,
                'data' => $nextPrayer
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get prayer statistics
     */
    public function apiGetStatistics(Request $request)
    {
        try {
            $statistics = $this->salahTime->getStatistics();

            return Response::json([
                'success' => true,
                'data' => $statistics
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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

            return Response::json([
                'success' => true,
                'data' => [
                    'calculation_methods' => $methods,
                    'asr_juristic_methods' => $asrMethods
                ]
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get prayer names
     */
    public function apiGetPrayerNames(Request $request)
    {
        try {
            $language = $request->getQueryParam('language', 'en');
            $prayerNames = $this->salahTime->getSalahNames($language);

            return Response::json([
                'success' => true,
                'data' => $prayerNames
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
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
