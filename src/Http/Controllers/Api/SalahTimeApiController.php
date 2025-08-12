<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers\Api;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Islamic\PrayerTimeCalculator;
use IslamWiki\Core\Logging\Shahid;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Salah Time API Controller
 *
 * Handles API requests for salah time calculations and related functionality.
 */
class SalahTimeApiController extends Controller
{
    /**
     * @var PrayerTimeCalculator Prayer time calculator instance
     */
    private PrayerTimeCalculator $prayerTimeCalculator;

    /**
     * @var Shahid Logger instance
     */
    private Shahid $logger;

    /**
     * Create a new controller instance.
     */
    public function __construct(PrayerTimeCalculator $prayerTimeCalculator, Shahid $logger)
    {
        $this->prayerTimeCalculator = $prayerTimeCalculator;
        $this->logger = $logger;
    }

    /**
     * Calculate salah times for a given location, method, and date.
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'location' => 'required|string|max:100',
                'method' => 'required|string|max:10',
                'date' => 'required|date_format:Y-m-d'
            ]);

            $location = $request->input('location');
            $method = $request->input('method');
            $date = $request->input('date');

            $locationData = $this->getLocationData($location);
            if (!$locationData) {
                return response()->json([
                    'success' => false,
                    'error' => "Location '{$location}' not found"
                ], 400);
            }

            $dateParts = explode('-', $date);
            $year = (int) $dateParts[0];
            $month = (int) $dateParts[1];
            $day = (int) $dateParts[2];

            $times = $this->prayerTimeCalculator->calculateTimes(
                $locationData['latitude'],
                $locationData['longitude'],
                $year,
                $month,
                $day,
                $method
            );

            $hijriDate = $this->getHijriDate($date);

            $this->logger->info('Salah times calculated successfully', [
                'location' => $location,
                'method' => $method,
                'date' => $date
            ]);

            return response()->json([
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
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            $this->logger->error('Salah time calculation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate salah times: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get qibla direction for a location.
     */
    public function qibla(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'location' => 'required|string|max:100'
            ]);

            $location = $request->input('location');
            $locationData = $this->getLocationData($location);

            if (!$locationData) {
                return response()->json([
                    'success' => false,
                    'error' => "Location '{$location}' not found"
                ], 400);
            }

            $qibla = $this->calculateQiblaDirection(
                $locationData['latitude'],
                $locationData['longitude']
            );

            return response()->json([
                'success' => true,
                'qibla' => $qibla,
                'location' => $locationData['name'],
                'coordinates' => [
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude']
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Qibla calculation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate qibla direction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lunar phase information for a date.
     */
    public function lunar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'required|date_format:Y-m-d'
            ]);

            $date = $request->input('date');
            $lunar = $this->calculateLunarPhase($date);

            return response()->json([
                'success' => true,
                'lunar' => $lunar,
                'date' => $date
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Lunar phase calculation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to calculate lunar phase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available calculation methods.
     */
    public function methods(): JsonResponse
    {
        $methods = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority of Survey',
            'MAKKAH' => 'Umm Al-Qura University, Makkah',
            'KARACHI' => 'University of Islamic Sciences, Karachi',
            'TEHRAN' => 'Institute of Geophysics, Tehran',
            'JAFARI' => 'Shia Ithna Ashari'
        ];

        return response()->json([
            'success' => true,
            'methods' => $methods
        ]);
    }

    /**
     * Get available locations.
     */
    public function locations(): JsonResponse
    {
        $locations = [
            'makkah' => [
                'name' => 'Makkah, Saudi Arabia',
                'latitude' => 21.4225,
                'longitude' => 39.8262,
                'timezone' => 'Asia/Riyadh'
            ],
            'madinah' => [
                'name' => 'Madinah, Saudi Arabia',
                'latitude' => 24.5247,
                'longitude' => 39.5692,
                'timezone' => 'Asia/Riyadh'
            ],
            'istanbul' => [
                'name' => 'Istanbul, Turkey',
                'latitude' => 41.0082,
                'longitude' => 28.9784,
                'timezone' => 'Europe/Istanbul'
            ],
            'cairo' => [
                'name' => 'Cairo, Egypt',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'timezone' => 'Africa/Cairo'
            ],
            'jakarta' => [
                'name' => 'Jakarta, Indonesia',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'timezone' => 'Asia/Jakarta'
            ],
            'london' => [
                'name' => 'London, UK',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'timezone' => 'Europe/London'
            ],
            'newyork' => [
                'name' => 'New York, USA',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'timezone' => 'America/New_York'
            ],
            'toronto' => [
                'name' => 'Toronto, Canada',
                'latitude' => 43.6532,
                'longitude' => -79.3832,
                'timezone' => 'America/Toronto'
            ],
            'sydney' => [
                'name' => 'Sydney, Australia',
                'latitude' => -33.8688,
                'longitude' => 151.2093,
                'timezone' => 'Australia/Sydney'
            ]
        ];

        return response()->json([
            'success' => true,
            'locations' => $locations
        ]);
    }

    /**
     * Search locations by query.
     */
    public function searchLocations(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:100'
            ]);

            $query = strtolower($request->input('query'));
            $locations = $this->getAllLocations();
            $results = [];

            foreach ($locations as $key => $location) {
                if (strpos(strtolower($location['name']), $query) !== false ||
                    strpos(strtolower($key), $query) !== false) {
                    $results[$key] = $location;
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'query' => $query,
                'count' => count($results)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get location data by key.
     */
    private function getLocationData(string $location): ?array
    {
        $locations = $this->getAllLocations();
        return $locations[$location] ?? null;
    }

    /**
     * Get all available locations.
     */
    private function getAllLocations(): array
    {
        return [
            'default' => [
                'name' => 'Makkah',
                'latitude' => 21.4225,
                'longitude' => 39.8262,
                'timezone' => 'Asia/Riyadh'
            ],
            'makkah' => [
                'name' => 'Makkah',
                'latitude' => 21.4225,
                'longitude' => 39.8262,
                'timezone' => 'Asia/Riyadh'
            ],
            'madinah' => [
                'name' => 'Madinah',
                'latitude' => 24.5247,
                'longitude' => 39.5692,
                'timezone' => 'Asia/Riyadh'
            ],
            'istanbul' => [
                'name' => 'Istanbul',
                'latitude' => 41.0082,
                'longitude' => 28.9784,
                'timezone' => 'Europe/Istanbul'
            ],
            'cairo' => [
                'name' => 'Cairo',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'timezone' => 'Africa/Cairo'
            ],
            'jakarta' => [
                'name' => 'Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'timezone' => 'Asia/Jakarta'
            ],
            'london' => [
                'name' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'timezone' => 'Europe/London'
            ],
            'newyork' => [
                'name' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'timezone' => 'America/New_York'
            ],
            'toronto' => [
                'name' => 'Toronto',
                'latitude' => 43.6532,
                'longitude' => -79.3832,
                'timezone' => 'America/Toronto'
            ],
            'sydney' => [
                'name' => 'Sydney',
                'latitude' => -33.8688,
                'longitude' => 151.2093,
                'timezone' => 'Australia/Sydney'
            ]
        ];
    }

    /**
     * Calculate qibla direction.
     */
    private function calculateQiblaDirection(float $lat, float $lng): array
    {
        // Makkah coordinates (Kaaba)
        $makkahLat = 21.4225;
        $makkahLng = 39.8262;

        $latRad = deg2rad($lat);
        $makkahLatRad = deg2rad($makkahLat);
        $lngDiffRad = deg2rad($makkahLng - $lng);

        $y = sin($lngDiffRad);
        $x = cos($latRad) * tan($makkahLatRad) - sin($latRad) * cos($lngDiffRad);

        $qiblaDirection = atan2($y, $x);
        $qiblaDirection = rad2deg($qiblaDirection);

        // Normalize to 0-360 degrees
        if ($qiblaDirection < 0) {
            $qiblaDirection += 360;
        }

        // Calculate distance
        $distance = $this->calculateDistance($lat, $lng, $makkahLat, $makkahLng);

        return [
            'direction' => $qiblaDirection,
            'distance' => $distance,
            'bearing' => $this->getBearingName($qiblaDirection)
        ];
    }

    /**
     * Calculate lunar phase.
     */
    private function calculateLunarPhase(string $date): array
    {
        $dateParts = explode('-', $date);
        $year = (int) $dateParts[0];
        $month = (int) $dateParts[1];
        $day = (int) $dateParts[2];

        $jd = $this->gregorianToJulianDay($year, $month, $day);
        $hijri = $this->julianDayToHijri($jd);

        $phase = $this->calculatePhase($jd);
        $illumination = $this->calculateIllumination($jd);
        $age = $this->calculateAge($jd);

        return [
            'phase' => $phase,
            'illumination' => $illumination,
            'age' => $age,
            'hijri_date' => $this->formatHijriDate($hijri),
            'julian_day' => $jd
        ];
    }

    /**
     * Calculate distance between two points.
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get bearing name from degrees.
     */
    private function getBearingName(float $degrees): string
    {
        $bearings = [
            'North' => [337.5, 22.5],
            'Northeast' => [22.5, 67.5],
            'East' => [67.5, 112.5],
            'Southeast' => [112.5, 157.5],
            'South' => [157.5, 202.5],
            'Southwest' => [202.5, 247.5],
            'West' => [247.5, 292.5],
            'Northwest' => [292.5, 337.5]
        ];

        foreach ($bearings as $bearing => $range) {
            if ($degrees >= $range[0] && $degrees < $range[1]) {
                return $bearing;
            }
        }

        return 'North';
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
            'TEHRAN' => 'Institute of Geophysics, Tehran',
            'JAFARI' => 'Shia Ithna Ashari'
        ];

        return $methods[$method] ?? $method;
    }

    /**
     * Get Hijri date.
     */
    private function getHijriDate(string $gregorianDate): string
    {
        try {
            $date = new \DateTime($gregorianDate);
            $year = (int) $date->format('Y');
            $month = (int) $date->format('m');
            $day = (int) $date->format('d');

            $jd = $this->gregorianToJulianDay($year, $month, $day);
            $hijri = $this->julianDayToHijri($jd);

            return $this->formatHijriDate($hijri);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Convert Gregorian date to Julian Day.
     */
    private function gregorianToJulianDay(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year -= 1;
            $month += 12;
        }

        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    /**
     * Convert Julian Day to Hijri date.
     */
    private function julianDayToHijri(float $jd): array
    {
        $jd = floor($jd) + 0.5;
        $l = $jd + 68569;
        $n = floor((4 * $l) / 146097);
        $l = $l - floor((146097 * $n + 3) / 4);
        $i = floor((4000 * ($l + 1)) / 1461001);
        $l = $l - floor((1461 * $i) / 4) + 31;
        $j = floor((80 * $l) / 2447);
        $k = $l - floor((2447 * $j) / 80);
        $l = floor($j / 11);
        $j = $j + 2 - 12 * $l;
        $i = 100 * ($n - 49) + $i + $l;

        $year = $i;
        $month = $j;
        $day = $k;

        // Convert to Hijri
        $hijriYear = floor(($year - 622) * 1.0307 + 0.5);
        $hijriMonth = $month;
        $hijriDay = $day;

        return [
            'year' => (int) $hijriYear,
            'month' => $hijriMonth,
            'day' => $hijriDay
        ];
    }

    /**
     * Format Hijri date.
     */
    private function formatHijriDate(array $hijri): string
    {
        $months = [
            1 => 'Muharram',
            2 => 'Safar',
            3 => 'Rabi al-Awwal',
            4 => 'Rabi al-Thani',
            5 => 'Jumada al-Awwal',
            6 => 'Jumada al-Thani',
            7 => 'Rajab',
            8 => 'Shaban',
            9 => 'Ramadan',
            10 => 'Shawwal',
            11 => 'Dhu al-Qadah',
            12 => 'Dhu al-Hijjah'
        ];

        $monthName = $months[$hijri['month']] ?? 'Unknown';
        return $hijri['day'] . ' ' . $monthName . ' ' . $hijri['year'] . ' AH';
    }

    /**
     * Calculate lunar phase from Julian Day.
     */
    private function calculatePhase(float $jd): string
    {
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        $phaseAngle = ($daysSinceNewMoon / $lunarMonth) * 360;

        if ($phaseAngle < 45) {
            return 'New Moon';
        } elseif ($phaseAngle < 90) {
            return 'Waxing Crescent';
        } elseif ($phaseAngle < 135) {
            return 'First Quarter';
        } elseif ($phaseAngle < 180) {
            return 'Waxing Gibbous';
        } elseif ($phaseAngle < 225) {
            return 'Full Moon';
        } elseif ($phaseAngle < 270) {
            return 'Waning Gibbous';
        } elseif ($phaseAngle < 315) {
            return 'Last Quarter';
        } else {
            return 'Waning Crescent';
        }
    }

    /**
     * Calculate lunar illumination percentage.
     */
    private function calculateIllumination(float $jd): float
    {
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        $phaseAngle = ($daysSinceNewMoon / $lunarMonth) * 360;

        if ($phaseAngle <= 180) {
            return 50 * (1 + cos(deg2rad(180 - $phaseAngle)));
        } else {
            return 50 * (1 + cos(deg2rad($phaseAngle - 180)));
        }
    }

    /**
     * Calculate lunar age in days.
     */
    private function calculateAge(float $jd): float
    {
        $newMoonRef = 2451550.1;
        $lunarMonth = 29.530588853;

        $daysSinceNewMoon = ($jd - $newMoonRef) % $lunarMonth;
        return $daysSinceNewMoon;
    }
}
