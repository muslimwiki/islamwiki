<?php

/**
 * Salah Time API Controller
 *
 * Handles API requests for salah time calculations and related functionality.
 *
 * @package IslamWiki\Http\Controllers\Api
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers\Api;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Salah Time API Controller - Handles Prayer Time API Functionality
 */
class SalahTimeApiController extends Controller
{
    /**
     * Calculate salah times for a given location, method, and date.
     */
    public function calculate(Request $request): Response
    {
        try {
            $location = $request->getQueryParams()['location'] ?? '';
            $method = $request->getQueryParams()['method'] ?? 'MWL';
            $date = $request->getQueryParams()['date'] ?? date('Y-m-d');

            if (empty($location)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Location parameter is required'
                ]));
            }

            $locationData = $this->getLocationData($location);
            if (!$locationData) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => "Location '{$location}' not found"
                ]));
            }

            $times = $this->calculatePrayerTimes($locationData, $method, $date);
            $hijriDate = $this->getHijriDate($date);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'times' => $times,
                'location' => $locationData['name'],
                'method' => $this->getMethodName($method),
                'date' => $date,
                'hijri_date' => $hijriDate,
                'coordinates' => [
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude']
                ]
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get salah times for today.
     */
    public function today(Request $request): Response
    {
        try {
            $location = $request->getQueryParams()['location'] ?? '';
            $method = $request->getQueryParams()['method'] ?? 'MWL';

            if (empty($location)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Location parameter is required'
                ]));
            }

            $locationData = $this->getLocationData($location);
            if (!$locationData) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => "Location '{$location}' not found"
                ]));
            }

            $today = date('Y-m-d');
            $times = $this->calculatePrayerTimes($locationData, $method, $today);
            $hijriDate = $this->getHijriDate($today);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'times' => $times,
                'location' => $locationData['name'],
                'method' => $this->getMethodName($method),
                'date' => $today,
                'hijri_date' => $hijriDate
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get available calculation methods.
     */
    public function methods(Request $request): Response
    {
        try {
            $methods = $this->getCalculationMethods();

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'methods' => $methods
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get location data.
     */
    private function getLocationData(string $location): ?array
    {
        try {
            $sql = "SELECT id, name, latitude, longitude, timezone FROM prayer_locations 
                    WHERE name LIKE ? OR city LIKE ? OR country LIKE ? LIMIT 1";
            $searchTerm = "%{$location}%";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate prayer times.
     */
    private function calculatePrayerTimes(array $locationData, string $method, string $date): array
    {
        // TODO: Implement actual prayer time calculation
        // For now, return sample times
        return [
            'fajr' => '05:30',
            'sunrise' => '06:45',
            'dhuhr' => '12:30',
            'asr' => '15:45',
            'maghrib' => '18:15',
            'isha' => '19:45'
        ];
    }

    /**
     * Get Hijri date.
     */
    private function getHijriDate(string $gregorianDate): array
    {
        // TODO: Implement actual Hijri date conversion
        // For now, return sample Hijri date
        return [
            'day' => '15',
            'month' => 'Ramadan',
            'year' => '1445'
        ];
    }

    /**
     * Get method name.
     */
    private function getMethodName(string $method): string
    {
        $methods = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority of Survey',
            'MAKKAH' => 'Umm Al-Qura University, Makkah',
            'KARACHI' => 'University of Islamic Sciences, Karachi',
            'TEHRAN' => 'Institute of Geophysics, University of Tehran',
            'JAFARI' => 'Shia Ithna-Ashari, Leva Research Institute, Qum'
        ];

        return $methods[$method] ?? $method;
    }

    /**
     * Get available calculation methods.
     */
    private function getCalculationMethods(): array
    {
        return [
            'MWL' => [
                'name' => 'Muslim World League',
                'description' => 'Standard method used by most Islamic organizations',
                'fajr_angle' => 18,
                'maghrib_angle' => 0
            ],
            'ISNA' => [
                'name' => 'Islamic Society of North America',
                'description' => 'Common in North America',
                'fajr_angle' => 15,
                'maghrib_angle' => 0
            ],
            'EGYPT' => [
                'name' => 'Egyptian General Authority of Survey',
                'description' => 'Used in Egypt and some African countries',
                'fajr_angle' => 19.5,
                'maghrib_angle' => 0
            ],
            'MAKKAH' => [
                'name' => 'Umm Al-Qura University, Makkah',
                'description' => 'Official method of Saudi Arabia',
                'fajr_angle' => 18.5,
                'maghrib_angle' => 0
            ]
        ];
    }
}
