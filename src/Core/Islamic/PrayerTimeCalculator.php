<?php

/**
 * Advanced Prayer Time Calculator
 *
 * Enhanced prayer time calculation system with multiple methods,
 * advanced astronomical calculations, and comprehensive Islamic features.
 *
 * @package IslamWiki\Core\Islamic
 * @version 0.0.22
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Islamic;

use Logging;\Logger

class PrayerTimeCalculator
{
    /**
     * The logger instance.
     */
    private Logging $logger;

    /**
     * Calculation methods.
     */
    private array $methods = [
        'MWL' => [
            'fajr_angle' => 18,
            'maghrib_angle' => 17,
            'isha_angle' => 18
        ],
        'ISNA' => [
            'fajr_angle' => 15,
            'maghrib_angle' => 15,
            'isha_angle' => 15
        ],
        'EGYPT' => [
            'fajr_angle' => 19.5,
            'maghrib_angle' => 17.5,
            'isha_angle' => 17.5
        ],
        'MAKKAH' => [
            'fajr_angle' => 18.5,
            'maghrib_angle' => 90,
            'isha_angle' => 90
        ],
        'KARACHI' => [
            'fajr_angle' => 18,
            'maghrib_angle' => 18,
            'isha_angle' => 18
        ],
        'TEHRAN' => [
            'fajr_angle' => 17.7,
            'maghrib_angle' => 14,
            'isha_angle' => 14
        ],
        'JAFARI' => [
            'fajr_angle' => 16,
            'maghrib_angle' => 4,
            'isha_angle' => 14
        ]
    ];

    /**
     * Create a new prayer time calculator instance.
     */
    public function __construct(Logging $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Calculate prayer times for a specific location and date.
     */
    public function calculateTimes(float $latitude, float $longitude, int $year, int $month, int $day, string $method = 'MWL'): array
    {
        try {
            if (!isset($this->methods[$method])) {
                $method = 'MWL';
            }

            $methodConfig = $this->methods[$method];

            // Calculate Julian Day Number
            $jd = $this->gregorianToJulianDay($year, $month, $day);

            // Calculate solar coordinates
            $solarCoords = $this->calculateSolarCoordinates($jd);

            // Calculate prayer times
            $prayerTimes = [
                'fajr' => $this->calculateFajr($latitude, $longitude, $solarCoords, $methodConfig['fajr_angle']),
                'sunrise' => $this->calculateSunrise($latitude, $longitude, $solarCoords),
                'dhuhr' => $this->calculateDhuhr($longitude, $solarCoords),
                'asr' => $this->calculateAsr($latitude, $longitude, $solarCoords),
                'maghrib' => $this->calculateMaghrib($latitude, $longitude, $solarCoords, $methodConfig['maghrib_angle']),
                'isha' => $this->calculateIsha($latitude, $longitude, $solarCoords, $methodConfig['isha_angle'])
            ];

            // Add additional information
            $prayerTimes['date'] = [
                'gregorian' => "{$year}-{$month}-{$day}",
                'hijri' => $this->getHijriDate($year, $month, $day)
            ];

            $prayerTimes['location'] = [
                'latitude' => $latitude,
                'longitude' => $longitude
            ];

            $prayerTimes['method'] = $method;
            $prayerTimes['method_name'] = $this->getMethodName($method);

            // Calculate Qibla direction
            $prayerTimes['qibla'] = $this->calculateQiblaDirection($latitude, $longitude);

            // Calculate lunar phase
            $prayerTimes['lunar_phase'] = $this->calculateLunarPhase($jd);

            $this->logger->info('Prayer times calculated successfully', [
                'location' => "{$latitude}, {$longitude}",
                'date' => "{$year}-{$month}-{$day}",
                'method' => $method
            ]);

            return $prayerTimes;
        } catch (\Exception $e) {
            $this->logger->error('Prayer times calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate Fajr prayer time.
     */
    private function calculateFajr(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        $declination = $solarCoords['declination'];
        $equation = $solarCoords['equation'];

        $hourAngle = $this->calculateHourAngle($latitude, $declination, $angle);
        $time = 12 - $hourAngle - $equation;

        return $this->formatTime($time);
    }

    /**
     * Calculate Sunrise time.
     */
    private function calculateSunrise(float $latitude, float $longitude, array $solarCoords): string
    {
        $declination = $solarCoords['declination'];
        $equation = $solarCoords['equation'];

        $hourAngle = $this->calculateHourAngle($latitude, $declination, -0.833);
        $time = 12 - $hourAngle - $equation;

        return $this->formatTime($time);
    }

    /**
     * Calculate Dhuhr prayer time.
     */
    private function calculateDhuhr(float $longitude, array $solarCoords): string
    {
        $equation = $solarCoords['equation'];
        $time = 12 - $equation + ($longitude / 15);

        return $this->formatTime($time);
    }

    /**
     * Calculate Asr prayer time.
     */
    private function calculateAsr(float $latitude, float $longitude, array $solarCoords): string
    {
        $declination = $solarCoords['declination'];
        $equation = $solarCoords['equation'];

        // Calculate shadow factor (1 for Shafi'i, 2 for Hanafi)
        $shadowFactor = 1;

        $hourAngle = $this->calculateHourAngle($latitude, $declination, $this->calculateAsrAngle($latitude, $declination, $shadowFactor));
        $time = 12 - $hourAngle - $equation;

        return $this->formatTime($time);
    }

    /**
     * Calculate Maghrib prayer time.
     */
    private function calculateMaghrib(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        $declination = $solarCoords['declination'];
        $equation = $solarCoords['equation'];

        $hourAngle = $this->calculateHourAngle($latitude, $declination, $angle);
        $time = 12 + $hourAngle - $equation;

        return $this->formatTime($time);
    }

    /**
     * Calculate Isha prayer time.
     */
    private function calculateIsha(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        $declination = $solarCoords['declination'];
        $equation = $solarCoords['equation'];

        $hourAngle = $this->calculateHourAngle($latitude, $declination, $angle);
        $time = 12 + $hourAngle - $equation;

        return $this->formatTime($time);
    }

    /**
     * Calculate solar coordinates.
     */
    private function calculateSolarCoordinates(float $jd): array
    {
        $T = ($jd - 2451545.0) / 36525;
        $T2 = $T * $T;
        $T3 = $T2 * $T;

        // Mean longitude of the sun
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2;

        // Mean anomaly of the sun
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T3;

        // Eccentricity of the earth's orbit
        $e = 0.016708617 - 0.000042037 * $T - 0.0000001236 * $T2;

        // Sun's equation of center
        $C = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin(deg2rad($M)) +
             (0.019993 - 0.000101 * $T) * sin(deg2rad(2 * $M)) +
             0.000290 * sin(deg2rad(3 * $M));

        // Sun's true longitude
        $L = $L0 + $C;

        // Sun's true anomaly
        $v = $M + $C;

        // Sun's radius vector
        $R = (1.000001018 * (1 - $e * $e)) / (1 + $e * cos(deg2rad($v)));

        // Sun's declination
        $epsilon = 23.439 - 0.0000004 * $T;
        $delta = rad2deg(asin(sin(deg2rad($epsilon)) * sin(deg2rad($L))));

        // Sun's equation of time
        $y = tan(deg2rad($epsilon / 2)) * tan(deg2rad($epsilon / 2));
        $equation = $y * sin(2 * deg2rad($L0)) - 2 * $e * sin(deg2rad($M)) +
                   4 * $e * $y * sin(deg2rad($M)) * cos(2 * deg2rad($L0)) -
                   0.5 * $y * $y * sin(4 * deg2rad($L0)) -
                   1.25 * $e * $e * sin(2 * deg2rad($M));

        return [
            'declination' => $delta,
            'equation' => $equation / 4
        ];
    }

    /**
     * Calculate hour angle.
     */
    private function calculateHourAngle(float $latitude, float $declination, float $angle): float
    {
        $latRad = deg2rad($latitude);
        $decRad = deg2rad($declination);
        $angleRad = deg2rad($angle);

        $cosH = (sin($angleRad) - sin($latRad) * sin($decRad)) / (cos($latRad) * cos($decRad));

        if ($cosH > 1) {
            return 0;
        }

        if ($cosH < -1) {
            return 180;
        }

        return rad2deg(acos($cosH));
    }

    /**
     * Calculate Asr angle.
     */
    private function calculateAsrAngle(float $latitude, float $declination, float $shadowFactor): float
    {
        $latRad = deg2rad($latitude);
        $decRad = deg2rad($declination);

        $cotA = $shadowFactor + tan(abs($latRad - $decRad));

        return rad2deg(atan(1 / $cotA));
    }

    /**
     * Calculate Qibla direction.
     */
    private function calculateQiblaDirection(float $latitude, float $longitude): array
    {
        $kaabaLat = 21.4225;
        $kaabaLng = 39.8262;

        $latRad = deg2rad($latitude);
        $lngRad = deg2rad($longitude);
        $kaabaLatRad = deg2rad($kaabaLat);
        $kaabaLngRad = deg2rad($kaabaLng);

        $y = sin($kaabaLngRad - $lngRad);
        $x = cos($latRad) * tan($kaabaLatRad) - sin($latRad) * cos($kaabaLngRad - $lngRad);

        $qiblaAngle = atan2($y, $x);

        return [
            'angle' => rad2deg($qiblaAngle),
            'direction' => $this->getDirectionName(rad2deg($qiblaAngle))
        ];
    }

    /**
     * Calculate lunar phase.
     */
    private function calculateLunarPhase(float $jd): array
    {
        $phase = ($jd - 2451550.1) / 29.530588853;
        $phase = $phase - floor($phase);

        $age = $phase * 29.530588853;
        $illumination = (1 - cos(2 * M_PI * $phase)) / 2;

        return [
            'phase' => $phase,
            'illumination' => $illumination,
            'age' => $age,
            'phase_name' => $this->getPhaseName($phase)
        ];
    }

    /**
     * Convert Gregorian date to Julian Day Number.
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
     * Get Hijri date.
     */
    private function getHijriDate(int $year, int $month, int $day): array
    {
        $jd = $this->gregorianToJulianDay($year, $month, $day);

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

        return [
            'year' => (int) $i,
            'month' => (int) $j,
            'day' => (int) $k
        ];
    }

    /**
     * Format time as HH:MM.
     */
    private function formatTime(float $time): string
    {
        $hours = floor($time);
        $minutes = round(($time - $hours) * 60);

        if ($minutes >= 60) {
            $hours += 1;
            $minutes -= 60;
        }

        if ($hours >= 24) {
            $hours -= 24;
        }

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Get method name.
     */
    private function getMethodName(string $method): string
    {
        $methodNames = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority',
            'MAKKAH' => 'Umm al-Qura University, Makkah',
            'KARACHI' => 'University of Islamic Sciences, Karachi',
            'TEHRAN' => 'Institute of Geophysics, Tehran',
            'JAFARI' => 'Shia Ithna Ashari'
        ];

        return $methodNames[$method] ?? $method;
    }

    /**
     * Get direction name.
     */
    private function getDirectionName(float $angle): string
    {
        $angle = fmod($angle + 360, 360);

        if ($angle >= 337.5 || $angle < 22.5) {
            return 'North';
        }
        if ($angle >= 22.5 && $angle < 67.5) {
            return 'Northeast';
        }
        if ($angle >= 67.5 && $angle < 112.5) {
            return 'East';
        }
        if ($angle >= 112.5 && $angle < 157.5) {
            return 'Southeast';
        }
        if ($angle >= 157.5 && $angle < 202.5) {
            return 'South';
        }
        if ($angle >= 202.5 && $angle < 247.5) {
            return 'Southwest';
        }
        if ($angle >= 247.5 && $angle < 292.5) {
            return 'West';
        }
        if ($angle >= 292.5 && $angle < 337.5) {
            return 'Northwest';
        }

        return 'North';
    }

    /**
     * Get phase name.
     */
    private function getPhaseName(float $phase): string
    {
        if ($phase < 0.0625) {
            return 'New Moon';
        }
        if ($phase < 0.1875) {
            return 'Waxing Crescent';
        }
        if ($phase < 0.3125) {
            return 'First Quarter';
        }
        if ($phase < 0.4375) {
            return 'Waxing Gibbous';
        }
        if ($phase < 0.5625) {
            return 'Full Moon';
        }
        if ($phase < 0.6875) {
            return 'Waning Gibbous';
        }
        if ($phase < 0.8125) {
            return 'Last Quarter';
        }
        if ($phase < 0.9375) {
            return 'Waning Crescent';
        }
        return 'New Moon';
    }
}
