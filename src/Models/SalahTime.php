<?php

/**
 * SalahTime Model
 *
 * This model handles salah time calculations, user locations,
 * notifications, and preferences for the IslamWiki application.
 *
 * @package IslamWiki
 * @version 0.0.16
 * @license AGPL-3.0
 */

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Query\Builder;
use DateTime;
use DateTimeZone;
use Exception;

class SalahTime
{
    protected $connection;
    protected $table = 'prayer_times';

    // Prayer time calculation methods
    const CALCULATION_METHODS = [
        'MWL' => 'Muslim World League',
        'ISNA' => 'Islamic Society of North America',
        'EGYPT' => 'Egyptian General Authority of Survey',
        'MAKKAH' => 'Umm Al-Qura University, Makkah',
        'KARACHI' => 'University of Islamic Sciences, Karachi',
        'TEHRAN' => 'Institute of Geophysics, University of Tehran',
        'JAFARI' => 'Shia Ithna Ashari, Leva Research Institute, Qum'
    ];

    // Asr juristic methods
    const ASR_JURISTIC_METHODS = [
        'Standard' => 'Standard (Shafi, Maliki, Hanbali)',
        'Hanafi' => 'Hanafi'
    ];

    // Prayer names in multiple languages
    const PRAYER_NAMES = [
        'en' => [
            'fajr' => 'Fajr',
            'sunrise' => 'Sunrise',
            'dhuhr' => 'Dhuhr',
            'asr' => 'Asr',
            'maghrib' => 'Maghrib',
            'isha' => 'Isha'
        ],
        'ar' => [
            'fajr' => 'الفجر',
            'sunrise' => 'الشروق',
            'dhuhr' => 'الظهر',
            'asr' => 'العصر',
            'maghrib' => 'المغرب',
            'isha' => 'العشاء'
        ],
        'ur' => [
            'fajr' => 'فجر',
            'sunrise' => 'طلوع آفتاب',
            'dhuhr' => 'ظہر',
            'asr' => 'عصر',
            'maghrib' => 'مغرب',
            'isha' => 'عشاء'
        ],
        'tr' => [
            'fajr' => 'İmsak',
            'sunrise' => 'Güneş',
            'dhuhr' => 'Öğle',
            'asr' => 'İkindi',
            'maghrib' => 'Akşam',
            'isha' => 'Yatsı'
        ]
    ];

    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Get prayer times for a specific date and location
     */
    public function getSalahTimes($date, $latitude, $longitude, $timezone = 'UTC', $method = 'MWL', $asrJuristic = 'Standard', $adjustHighLats = true, $minutesOffset = 0)
    {
        try {
            // Check cache first
            $cacheKey = $this->generateCacheKey($date, $latitude, $longitude, $method, $asrJuristic, $adjustHighLats, $minutesOffset);
            $cached = $this->getFromCache($cacheKey);

            if ($cached) {
                return $cached;
            }

            // Check database
            $existing = $this->findExisting($date, $latitude, $longitude, $method, $asrJuristic, $adjustHighLats, $minutesOffset);

            if ($existing) {
                $this->cacheResult($cacheKey, $existing);
                return $existing;
            }

            // Calculate salah times
            $salahTimes = $this->calculateSalahTimes($date, $latitude, $longitude, $timezone, $method, $asrJuristic, $adjustHighLats, $minutesOffset);

            // Store in database
            $this->storeSalahTimes($salahTimes);

            // Cache result
            $this->cacheResult($cacheKey, $salahTimes);

            return $salahTimes;
        } catch (Exception $e) {
            $this->logError('calculation_error', $e->getMessage(), [
                'date' => $date,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'method' => $method
            ]);
            throw $e;
        }
    }

    /**
     * Calculate salah times using astronomical algorithms
     */
    protected function calculateSalahTimes($date, $latitude, $longitude, $timezone, $method, $asrJuristic, $adjustHighLats, $minutesOffset)
    {
        $dateTime = new DateTime($date, new DateTimeZone($timezone));
        $julianDate = $this->getJulianDate($dateTime);

        // Calculate solar coordinates
        $solarCoords = $this->calculateSolarCoordinates($julianDate);

        // Calculate salah times
        $salahTimes = [
            'fajr' => $this->calculateFajr($solarCoords, $latitude, $longitude, $method, $adjustHighLats, $minutesOffset),
            'sunrise' => $this->calculateSunrise($solarCoords, $latitude, $longitude, $minutesOffset),
            'dhuhr' => $this->calculateDhuhr($solarCoords, $longitude, $minutesOffset),
            'asr' => $this->calculateAsr($solarCoords, $latitude, $asrJuristic, $minutesOffset),
            'maghrib' => $this->calculateMaghrib($solarCoords, $latitude, $longitude, $minutesOffset),
            'isha' => $this->calculateIsha($solarCoords, $latitude, $longitude, $method, $adjustHighLats, $minutesOffset)
        ];

        return [
            'date' => $date,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $timezone,
            'calculation_method' => $method,
            'asr_juristic' => $asrJuristic,
            'adjust_high_lats' => $adjustHighLats,
            'minutes_offset' => $minutesOffset,
            'salah_times' => $salahTimes
        ];
    }

    /**
     * Calculate Fajr prayer time
     */
    protected function calculateFajr($solarCoords, $latitude, $longitude, $method, $adjustHighLats, $minutesOffset)
    {
        $angle = $this->getFajrAngle($method);
        return $this->calculatePrayerTime($solarCoords, $latitude, $longitude, $angle, $minutesOffset);
    }

    /**
     * Calculate Sunrise time
     */
    protected function calculateSunrise($solarCoords, $latitude, $longitude, $minutesOffset)
    {
        return $this->calculatePrayerTime($solarCoords, $latitude, $longitude, -0.833, $minutesOffset);
    }

    /**
     * Calculate Dhuhr prayer time
     */
    protected function calculateDhuhr($solarCoords, $longitude, $minutesOffset)
    {
        $time = $solarCoords['transit'] + ($longitude / 15) + ($minutesOffset / 60);
        return $this->formatTime($time);
    }

    /**
     * Calculate Asr prayer time
     */
    protected function calculateAsr($solarCoords, $latitude, $asrJuristic, $minutesOffset)
    {
        $angle = $asrJuristic === 'Hanafi' ? 90 : 90 + atan(1 / (1 + tan(abs($latitude - $solarCoords['declination']))));
        return $this->calculatePrayerTime($solarCoords, $latitude, 0, $angle, $minutesOffset);
    }

    /**
     * Calculate Maghrib prayer time
     */
    protected function calculateMaghrib($solarCoords, $latitude, $longitude, $minutesOffset)
    {
        return $this->calculatePrayerTime($solarCoords, $latitude, $longitude, -0.833, $minutesOffset);
    }

    /**
     * Calculate Isha prayer time
     */
    protected function calculateIsha($solarCoords, $latitude, $longitude, $method, $adjustHighLats, $minutesOffset)
    {
        $angle = $this->getIshaAngle($method);
        return $this->calculatePrayerTime($solarCoords, $latitude, $longitude, $angle, $minutesOffset);
    }

    /**
     * Get Fajr angle based on calculation method
     */
    protected function getFajrAngle($method)
    {
        $angles = [
            'MWL' => 18,
            'ISNA' => 15,
            'EGYPT' => 19.5,
            'MAKKAH' => 18.5,
            'KARACHI' => 18,
            'TEHRAN' => 17.7,
            'JAFARI' => 16
        ];

        return $angles[$method] ?? 18;
    }

    /**
     * Get Isha angle based on calculation method
     */
    protected function getIshaAngle($method)
    {
        $angles = [
            'MWL' => 17,
            'ISNA' => 15,
            'EGYPT' => 17.5,
            'MAKKAH' => 90,
            'KARACHI' => 18,
            'TEHRAN' => 14,
            'JAFARI' => 14
        ];

        return $angles[$method] ?? 17;
    }

    /**
     * Calculate solar coordinates
     */
    protected function calculateSolarCoordinates($julianDate)
    {
        $T = ($julianDate - 2451545.0) / 36525;
        $T2 = $T * $T;
        $T3 = $T2 * $T;

        // Mean longitude
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2;

        // Mean anomaly
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T3;

        // Eccentricity
        $e = 0.016708617 - 0.000042037 * $T - 0.0000001236 * $T2;

        // Sun's equation of center
        $C = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin(deg2rad($M)) +
             (0.019993 - 0.000101 * $T) * sin(deg2rad(2 * $M)) +
             0.000290 * sin(deg2rad(3 * $M));

        // True longitude
        $L = $L0 + $C;

        // True anomaly
        $v = $M + $C;

        // Distance
        $R = (1.000001018 * (1 - $e * $e)) / (1 + $e * cos(deg2rad($v)));

        // Apparent longitude
        $lambda = $L - 0.00569 - 0.00478 * sin(deg2rad(125.04 - 1934.136 * $T));

        // Obliquity
        $epsilon = 23.439 - 0.0000004 * $T;

        // Declination
        $delta = rad2deg(asin(sin(deg2rad($epsilon)) * sin(deg2rad($lambda))));

        // Right ascension
        $alpha = rad2deg(atan2(cos(deg2rad($epsilon)) * sin(deg2rad($lambda)), cos(deg2rad($lambda))));

        return [
            'declination' => $delta,
            'right_ascension' => $alpha,
            'transit' => $alpha / 15
        ];
    }

    /**
     * Calculate prayer time using solar coordinates
     */
    protected function calculatePrayerTime($solarCoords, $latitude, $longitude, $angle, $minutesOffset)
    {
        $latRad = deg2rad($latitude);
        $deltaRad = deg2rad($solarCoords['declination']);
        $angleRad = deg2rad($angle);

        $cosH = (sin($angleRad) - sin($latRad) * sin($deltaRad)) / (cos($latRad) * cos($deltaRad));

        if ($cosH > 1) {
            $cosH = 1;
        } elseif ($cosH < -1) {
            $cosH = -1;
        }

        $H = rad2deg(acos($cosH));
        $time = $solarCoords['transit'] + ($longitude / 15) + ($H / 15) + ($minutesOffset / 60);

        return $this->formatTime($time);
    }

    /**
     * Format time as HH:MM
     */
    protected function formatTime($time)
    {
        $hours = floor($time);
        $minutes = round(($time - $hours) * 60);

        if ($minutes >= 60) {
            $hours++;
            $minutes -= 60;
        }

        if ($hours >= 24) {
            $hours -= 24;
        }

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Get Julian date
     */
    protected function getJulianDate($dateTime)
    {
        $year = (int)$dateTime->format('Y');
        $month = (int)$dateTime->format('m');
        $day = (int)$dateTime->format('d');

        if ($month <= 2) {
            $year--;
            $month += 12;
        }

        $A = floor($year / 100);
        $B = 2 - $A + floor($A / 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $B - 1524.5;
    }

    /**
     * Find existing prayer times in database
     */
    protected function findExisting($date, $latitude, $longitude, $method, $asrJuristic, $adjustHighLats, $minutesOffset)
    {
        if (!$this->connection) {
            return null;
        }

        $query = $this->connection->table($this->table);

        $result = $query->where('date', $date)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->where('calculation_method', $method)
            ->where('asr_juristic', $asrJuristic)
            ->where('adjust_high_lats', $adjustHighLats)
            ->where('minutes_offset', $minutesOffset)
            ->first();

        return $result;
    }

    /**
     * Store prayer times in database
     */
    protected function storeSalahTimes($data)
    {
        if (!$this->connection) {
            return;
        }

        $query = $this->connection->table($this->table);

        $salahTimes = $data['salah_times'];

        $query->insert([
            'date' => $data['date'],
            'location_name' => $this->getLocationName($data['latitude'], $data['longitude']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone' => $data['timezone'],
            'fajr' => $salahTimes['fajr'],
            'sunrise' => $salahTimes['sunrise'],
            'dhuhr' => $salahTimes['dhuhr'],
            'asr' => $salahTimes['asr'],
            'maghrib' => $salahTimes['maghrib'],
            'isha' => $salahTimes['isha'],
            'calculation_method' => $data['calculation_method'],
            'asr_juristic' => $data['asr_juristic'],
            'adjust_high_lats' => $data['adjust_high_lats'],
            'minutes_offset' => $data['minutes_offset'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get location name from coordinates
     */
    protected function getLocationName($latitude, $longitude)
    {
        // This would typically use a geocoding service
        // For now, return coordinates as location name
        return "{$latitude}, {$longitude}";
    }

    /**
     * Generate cache key
     */
    protected function generateCacheKey($date, $latitude, $longitude, $method, $asrJuristic, $adjustHighLats, $minutesOffset)
    {
        return "prayer_times_{$date}_{$latitude}_{$longitude}_{$method}_{$asrJuristic}_{$adjustHighLats}_{$minutesOffset}";
    }

    /**
     * Get from cache
     */
    protected function getFromCache($key)
    {
        if (!$this->connection) {
            return null;
        }

        $query = $this->connection->table('prayer_api_cache');

        $result = $query->where('cache_key', $key)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        return $result ? json_decode($result['response_data'], true) : null;
    }

    /**
     * Cache result
     */
    protected function cacheResult($key, $data)
    {
        if (!$this->connection) {
            return;
        }

        $query = $this->connection->table('prayer_api_cache');

        $query->insert([
            'cache_key' => $key,
            'response_data' => json_encode($data),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log error
     */
    protected function logError($type, $message, $context = [])
    {
        $query = new Builder($this->connection);

        $query->table('prayer_errors')->insert([
            'error_type' => $type,
            'error_message' => $message,
            'request_data' => json_encode($context),
            'occurred_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get user locations
     */
    public function getUserLocations($userId)
    {
        if (!$this->connection) {
            // Return default location when no database connection
            return [
                [
                    'id' => 1,
                    'user_id' => $userId,
                    'name' => 'Default Location',
                    'latitude' => 40.7128,
                    'longitude' => -74.0060,
                    'timezone' => 'America/New_York',
                    'is_default' => true
                ]
            ];
        }

        $query = $this->connection->table('user_locations');

        return $query->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Add user location
     */
    public function addUserLocation($userId, $data)
    {
        if (!$this->connection) {
            return 1; // Return default ID when no database
        }

        $query = $this->connection->table('user_locations');

        // If this is set as default, unset other defaults
        if ($data['is_default']) {
            $query->where('user_id', $userId)
                ->update(['is_default' => false]);
        }

        return $query->insert([
            'user_id' => $userId,
            'name' => $data['name'],
            'city' => $data['city'],
            'country' => $data['country'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone' => $data['timezone'],
            'is_default' => $data['is_default'] ?? false,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get user preferences
     */
    public function getUserPreferences($userId)
    {
        if (!$this->connection) {
            // Return default preferences when no database connection
            return [
                'user_id' => $userId,
                'calculation_method' => 'MWL',
                'asr_juristic' => 'Standard',
                'adjust_high_lats' => true,
                'minutes_offset' => 0,
                'language' => 'en',
                'time_format' => '24h',
                'show_sunrise' => true,
                'show_dua' => true,
                'show_qibla' => true
            ];
        }

        $query = $this->connection->table('prayer_preferences');

        $preferences = $query->where('user_id', $userId)
            ->first();

        if (!$preferences) {
            // Create default preferences
            $preferences = $this->createDefaultPreferences($userId);
        }

        return $preferences;
    }

    /**
     * Create default preferences
     */
    protected function createDefaultPreferences($userId)
    {
        if (!$this->connection) {
            return [
                'user_id' => $userId,
                'calculation_method' => 'MWL',
                'asr_juristic' => 'Standard',
                'adjust_high_lats' => true,
                'minutes_offset' => 0,
                'language' => 'en',
                'time_format' => '24h',
                'show_sunrise' => true,
                'show_dua' => true,
                'show_qibla' => true
            ];
        }

        $query = $this->connection->table('prayer_preferences');

        $defaults = [
            'user_id' => $userId,
            'calculation_method' => 'MWL',
            'asr_juristic' => 'Standard',
            'adjust_high_lats' => true,
            'minutes_offset' => 0,
            'language' => 'en',
            'time_format' => '24h',
            'show_sunrise' => true,
            'show_dua' => true,
            'show_qibla' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $query->insert($defaults);

        return $defaults;
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences($userId, $data)
    {
        if (!$this->connection) {
            return true; // Return success when no database
        }

        $query = $this->connection->table('prayer_preferences');

        return $query->where('user_id', $userId)
            ->update(array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]));
    }

    /**
     * Get prayer names in specified language
     */
    public function getPrayerNames($language = 'en')
    {
        return self::PRAYER_NAMES[$language] ?? self::PRAYER_NAMES['en'];
    }

    /**
     * Get calculation methods
     */
    public function getCalculationMethods()
    {
        return self::CALCULATION_METHODS;
    }

    /**
     * Get Asr juristic methods
     */
    public function getAsrJuristicMethods()
    {
        return self::ASR_JURISTIC_METHODS;
    }

    /**
     * Calculate Qibla direction
     */
    public function calculateQiblaDirection($latitude, $longitude)
    {
        // Kaaba coordinates
        $kaabaLat = 21.4225;
        $kaabaLng = 39.8262;

        $lat1 = deg2rad($latitude);
        $lng1 = deg2rad($longitude);
        $lat2 = deg2rad($kaabaLat);
        $lng2 = deg2rad($kaabaLng);

        $y = sin($lng2 - $lng1) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lng2 - $lng1);

        $qiblaDirection = rad2deg(atan2($y, $x));

        // Normalize to 0-360
        $qiblaDirection = ($qiblaDirection + 360) % 360;

        return $qiblaDirection;
    }

    /**
     * Get next prayer
     */
    public function getNextPrayer($prayerTimes, $currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = date('H:i');
        }

        $prayers = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

        foreach ($prayers as $prayer) {
            if ($prayerTimes[$prayer] > $currentTime) {
                return [
                    'prayer' => $prayer,
                    'time' => $prayerTimes[$prayer],
                    'remaining' => $this->getTimeRemaining($currentTime, $prayerTimes[$prayer])
                ];
            }
        }

        // If no prayer found, next prayer is tomorrow's Fajr
        return [
            'prayer' => 'fajr',
            'time' => $prayerTimes['fajr'],
            'remaining' => 'Tomorrow'
        ];
    }

    /**
     * Get time remaining until prayer
     */
    protected function getTimeRemaining($current, $prayer)
    {
        $currentMinutes = $this->timeToMinutes($current);
        $prayerMinutes = $this->timeToMinutes($prayer);

        $diff = $prayerMinutes - $currentMinutes;

        if ($diff < 0) {
            $diff += 1440; // Add 24 hours
        }

        $hours = floor($diff / 60);
        $minutes = $diff % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Convert time to minutes
     */
    protected function timeToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return $hours * 60 + $minutes;
    }

    /**
     * Get prayer statistics
     */
    public function getStatistics()
    {
        if (!$this->connection) {
            // Return default statistics when no database connection
            return [
                'total_requests' => 0,
                'unique_users' => 0,
                'cache_hits' => 0,
                'api_calls' => 0,
                'average_response_time' => 0
            ];
        }

        $query = $this->connection->table('prayer_statistics');

        $today = date('Y-m-d');

        $stats = $query->where('date', $today)
            ->first();

        if (!$stats) {
            $stats = [
                'total_requests' => 0,
                'unique_users' => 0,
                'cache_hits' => 0,
                'api_calls' => 0,
                'average_response_time' => 0
            ];
        }

        return $stats;
    }

    /**
     * Update statistics
     */
    public function updateStatistics($type, $responseTime = 0)
    {
        if (!$this->connection) {
            return; // Do nothing when no database
        }

        $query = $this->connection->table('prayer_statistics');

        $today = date('Y-m-d');

        $existing = $query->where('date', $today)
            ->first();

        if ($existing) {
            $query->where('date', $today)
                ->update([
                    'total_requests' => $existing['total_requests'] + 1,
                    'cache_hits' => $existing['cache_hits'] + ($type === 'cache' ? 1 : 0),
                    'api_calls' => $existing['api_calls'] + ($type === 'api' ? 1 : 0),
                    'average_response_time' => ($existing['average_response_time'] + $responseTime) / 2,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            $query->insert([
                'date' => $today,
                'total_requests' => 1,
                'unique_users' => 1,
                'cache_hits' => $type === 'cache' ? 1 : 0,
                'api_calls' => $type === 'api' ? 1 : 0,
                'average_response_time' => $responseTime,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
