<?php

/**
 * Advanced Salah Time Calculator
 *
 * Enhanced salah time calculation system with multiple methods,
 * advanced astronomical calculations, and comprehensive Islamic features.
 *
 * @package IslamWiki\Core\Islamic
 * @version 0.0.24
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Islamic;

use Logging;\Logger

class SalahTimeCalculator
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
            'isha_angle' => 18,
            'asr_factor' => 1
        ],
        'ISNA' => [
            'fajr_angle' => 15,
            'maghrib_angle' => 15,
            'isha_angle' => 15,
            'asr_factor' => 1
        ],
        'EGYPT' => [
            'fajr_angle' => 19.5,
            'maghrib_angle' => 17.5,
            'isha_angle' => 17.5,
            'asr_factor' => 1
        ],
        'MAKKAH' => [
            'fajr_angle' => 18.5,
            'maghrib_angle' => 90,
            'isha_angle' => 90,
            'asr_factor' => 1
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
     * Create a new salah time calculator instance.
     */
    public function __construct(Logging $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Calculate prayer times for a given date and location
     */
    public function calculateTimes(
        float $latitude,
        float $longitude,
        int $year,
        int $month,
        int $day,
        string $method = 'MWL',
        float $timezone = 0
    ): array {
        // Convert date to Julian day at 12:00 UTC
        $jd = $this->gregorianToJulianDay($year, $month, $day);
        
        // Calculate solar coordinates
        $solarCoords = $this->calculateSolarCoordinates($jd);
        
        // Get method parameters
        $methodParams = $this->getMethodParameters($method);
        
        // Calculate prayer times (in UTC)
        $fajr = $this->calculateFajr($latitude, $longitude, $solarCoords, $methodParams['fajr']);
        $sunrise = $this->calculateSunrise($latitude, $longitude, $solarCoords);
        $dhuhr = $this->calculateDhuhr($longitude, $solarCoords);
        $asr = $this->calculateAsr($latitude, $longitude, $solarCoords, $methodParams['asr']);
        $maghrib = $this->calculateMaghrib($latitude, $longitude, $solarCoords, $methodParams['maghrib']);
        $isha = $this->calculateIsha($latitude, $longitude, $solarCoords, $methodParams['isha']);
        
        // Format times with timezone adjustment
        $times = [
            'fajr' => $this->formatTime($fajr, $timezone),
            'sunrise' => $this->formatTime($sunrise, $timezone),
            'dhuhr' => $this->formatTime($dhuhr, $timezone),
            'asr' => $this->formatTime($asr, $timezone),
            'maghrib' => $this->formatTime($maghrib, $timezone),
            'isha' => $this->formatTime($isha, $timezone),
            'qibla' => $this->calculateQiblaDirection($latitude, $longitude),
            'lunar_phase' => $this->calculateLunarPhase($jd),
            'method' => $this->getMethodName($method),
            'date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
            'hijri_date' => $this->getHijriDate($year, $month, $day),
            'timezone' => $timezone
        ];
        
        $this->logger->info('Salah times calculated successfully', [
            'location' => "$latitude, $longitude",
            'date' => $times['date'],
            'method' => $method,
            'timezone' => $timezone
        ]);
        
        return $times;
            // Calculate lunar phase
            $salahTimes['lunar_phase'] = $this->calculateLunarPhase($jd);

            $this->logger->info('Salah times calculated successfully', [
                'location' => "{$latitude}, {$longitude}",
                'date' => "{$year}-{$month}-{$day}",
                'method' => $method
            ]);

            return $salahTimes;
        } catch (\Exception $e) {
            $this->logger->error('Salah times calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Gregorian date to Julian Day Number
     */
    private function gregorianToJulianDay(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year--;
            $month += 12;
        }
        
        $a = (int)($year / 100);
        $b = 2 - $a + (int)($a / 4);
        
        return (int)(365.25 * ($year + 4716)) + (int)(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }
    
    /**
     * Calculate solar coordinates for a given Julian Day
     */
    private function calculateSolarCoordinates(float $jd): array
    {
        // Time in Julian centuries from 2000.0
        $T = ($jd - 2451545.0) / 36525.0;
        
        // Mean solar longitude (degrees)
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T * $T;
        
        // Mean solar anomaly (degrees)
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T * $T - 0.00000048 * $T * $T * $T;
        $M_rad = deg2rad($M);
        
        // Eccentricity of Earth's orbit
        $e = 0.016708634 - $T * (0.000042037 + 0.0000001267 * $T);
        
        // Equation of center
        $C = (1.914600 - 0.004817 * $T - 0.000014 * $T * $T) * sin($M_rad)
           + (0.019993 - 0.000101 * $T) * sin(2 * $M_rad)
           + 0.000290 * sin(3 * $M_rad);
        
        // True solar longitude
        $trueLongitude = $L0 + $C;
        
        // Apparent solar longitude (corrected for nutation and aberration)
        $omega = 125.04 - 1934.136 * $T;
        $omega_rad = deg2rad($omega);
        $lambda = $trueLongitude - 0.00569 - 0.00478 * sin($omega_rad);
        
        // Obliquity of the ecliptic
        $epsilon = 23.439296 - 0.0129 * $T - 0.00059 * $T * $T + 0.001813 * $T * $T * $T;
        $epsilon_rad = deg2rad($epsilon);
        
        // Right ascension and declination
        $lambda_rad = deg2rad($lambda);
        $alpha = rad2deg(atan2(cos($epsilon_rad) * sin($lambda_rad), cos($lambda_rad)));
        $delta = rad2deg(asin(sin($epsilon_rad) * sin($lambda_rad)));
        
        // Equation of time (in minutes)
        $y = tan($epsilon_rad / 2);
        $y *= $y;
        
        $L0_rad = deg2rad($L0);
        $eot = $y * sin(2 * $L0_rad) 
             - 2 * $e * sin($M_rad) 
             + 4 * $e * $y * sin($M_rad) * cos(2 * $L0_rad) 
             - 0.5 * $y * $y * sin(4 * $L0_rad) 
             - 1.25 * $e * $e * sin(2 * $M_rad);
             
        $eot = rad2deg($eot) * 4; // Convert to minutes of time
        
        return [
            'declination' => $delta,
            'equation' => $eot,
            'solarNoon' => $this->calculateSolarNoon($jd, 0, $eot) // Default longitude 0 for now
        ];
    }
    
    /**
     * Calculate solar noon for a given Julian Day, longitude, and equation of time
     */
    private function calculateSolarNoon(float $jd, float $longitude, float $eot): float
    {
        // Julian day of the solar noon
        $T = $this->julianCentury($jd);
        $eqTime = $this->calculateEquationOfTime($T);
        
        // Solar noon in minutes from midnight
        $solarNoonMinutes = 720 - ($longitude * 4) - $eqTime;
        
        // Convert to Julian day fraction
        return $jd + ($solarNoonMinutes / 1440.0);
    }
    
    /**
     * Calculate Julian century from Julian Day
     */
    private function julianCentury(float $jd): float
    {
        return ($jd - 2451545.0) / 36525.0;
    }
    
    /**
     * Calculate the equation of time for a given Julian century
     */
    private function calculateEquationOfTime(float $T): float
    {
        $l0 = 280.46646 + $T * (36000.76983 + $T * 0.0003032);
        $m = 357.52911 + $T * (35999.05029 - 0.0001537 * $T);
        $e = 0.016708634 - $T * (0.000042037 + 0.0000001267 * $T);
        
        $y = tan(deg2rad(23.44) / 2);
        $y *= $y;
        
        $sin2l0 = sin(deg2rad(2 * $l0));
        $sinm = sin(deg2rad($m));
        $cos2l0 = cos(deg2rad(2 * $l0));
        $sin4l0 = sin(deg2rad(4 * $l0));
        $sin2m = sin(deg2rad(2 * $m));
        
        $eot = $y * $sin2l0 - 2 * $e * $sinm + 4 * $e * $y * $sinm * $cos2l0 - 0.5 * $y * $y * $sin4l0 - 1.25 * $e * $e * $sin2m;
        return rad2deg($eot) * 4; // Convert to minutes of time
    }
    
    // Core prayer time calculation methods
    private function calculateFajr(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -$angle, false);
    }
    
    private function calculateSunrise(float $latitude, float $longitude, array $solarCoords): string
    {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -0.833, false);
    }
    
    private function calculateDhuhr(float $longitude, array $solarCoords): string
    {
        $time = $solarCoords['solarNoon'] + ($longitude / 360.0);
        return $this->formatTime($time);
    }
    
    private function calculateAsr(float $latitude, float $longitude, array $solarCoords, int $factor = 1): string
    {
        $declination = $solarCoords['declination'];
        $angle = -rad2deg(atan(1 / ($factor + tan(deg2rad(abs($latitude - $declination))))));
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, $angle, true);
    }
    
    private function calculateMaghrib(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -0.833, true);
    }
    
    private function calculateIsha(float $latitude, float $longitude, array $solarCoords, float $angle): string
    {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -$angle, true);
    }
    
    /**
     * Calculate time based on sun angle
     */
    private function calculateTimeAngle(float $latitude, float $longitude, array $solarCoords, float $angle, bool $afterNoon): string
    {
        $declination = $solarCoords['declination'];
        $latRad = deg2rad($latitude);
        $declRad = deg2rad($declination);
        $angleRad = deg2rad($angle);
        
        // Calculate the hour angle
        $numerator = sin($angleRad) - sin($latRad) * sin($declRad);
        $denominator = cos($latRad) * cos($declRad);
        
        // Handle edge cases where the sun doesn't reach the specified angle
        $absNumerator = abs($numerator);
        if ($absNumerator > $denominator) {
            // The sun doesn't reach this angle on this day at this location
            return $afterNoon ? '23:59' : '00:00';
        }
        
        $hourAngle = rad2deg(acos($numerator / $denominator)) / 15.0;
        
        // Calculate the time
        $time = $afterNoon 
            ? $solarCoords['solarNoon'] + ($hourAngle / 24.0)
            : $solarCoords['solarNoon'] - ($hourAngle / 24.0);
        
        return $this->formatTime($time);
    }
    
    /**
     * Format Julian day to time string (HH:MM)
     * 
     * @param float $julianDay Julian day with fraction representing time
     * @param float $timezone Timezone offset in hours
     * @return string Formatted time in 24-hour format (HH:MM)
     */
    private function formatTime(float $julianDay, float $timezone = 0): string
    {
        $time = $julianDay - floor($julianDay);
        $time = $time * 24.0 + $timezone; // Adjust for timezone
        
        // Normalize to 0-24 range
        while ($time < 0) $time += 24;
        while ($time >= 24) $time -= 24;
        
        $hours = (int)$time;
        $minutes = (int)(($time - $hours) * 60);
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    /**
     * Get the display name for a calculation method
     */
    private function getMethodName(string $method): string
    {
        $methodNames = [
            'MWL' => 'Muslim World League',
            'ISNA' => 'Islamic Society of North America',
            'EGYPT' => 'Egyptian General Authority of Survey',
            'MAKKAH' => 'Umm al-Qura, Makkah',
            'KARACHI' => 'University of Islamic Sciences, Karachi',
            'TEHRAN' => 'Institute of Geophysics, University of Tehran',
            'JAFARI' => 'Shia Ithna-Ashari, Leva Institute, Qum'
        ];
        
        return $methodNames[$method] ?? $method;
    }
    
    /**
     * Calculate Qibla direction from a location
     */
    private function calculateQiblaDirection(float $latitude, float $longitude): float
    {
        // Kaaba coordinates
        $kaabaLat = 21.422487;
        $kaabaLng = 39.826206;
        
        $latRad = deg2rad($latitude);
        $lngRad = deg2rad($longitude);
        $kaabaLatRad = deg2rad($kaabaLat);
        $kaabaLngRad = deg2rad($kaabaLng);
        
        $numerator = sin($kaabaLngRad - $lngRad);
        $denominator = cos($latRad) * tan($kaabaLatRad) - sin($latRad) * cos($kaabaLngRad - $lngRad);
        
        $qiblaRad = atan2($numerator, $denominator);
        $qiblaDeg = rad2deg($qiblaRad);
        
        // Normalize to 0-360 degrees
        return fmod(($qiblaDeg + 360.0), 360.0);
    }
    
    /**
     * Calculate lunar phase (0-29.5, where 0/29.5 = new moon, 7.38 = first quarter,
     * 14.77 = full moon, 22.15 = last quarter)
     */
    private function calculateLunarPhase(float $jd): float
    {
        // Days since known new moon (Jan 6, 2000 18:14 UTC)
        $daysSinceNew = $jd - 2451550.26;
        
        // Synodic month is approximately 29.53 days
        $synodicMonth = 29.530588853;
        
        // Calculate phase (0-29.53)
        $phase = fmod($daysSinceNew, $synodicMonth);
        
        // Normalize to 0-29.5
        if ($phase < 0) {
            $phase += $synodicMonth;
        }
        
        return $phase;
    }
    
    /**
     * Get Hijri date for a given Gregorian date
     */
    private function getHijriDate(int $year, int $month, int $day): array
    {
        // Simple approximation - in a real implementation, this would be more accurate
        $jd = $this->gregorianToJulianDay($year, $month, $day);
        $jd = (int)($jd - 1948440 + 10632);
        $n = (int)(($jd - 1) / 10631);
        $jd = $jd - 10631 * $n + 354;
        $j = ((int)((10985 - $jd) / 5316)) * ((int)(50 * $jd / 17719)) + ((int)($jd / 5670)) * ((int)(43 * $jd / 15238));
        $jd = $jd - ((int)((30 - $j) / 15)) * ((int)((17719 * $j) / 50)) - ((int)($j / 16)) * ((int)((15238 * $j) / 43)) + 29;
        
        $hijriMonth = (int)((24 * $jd) / 54189);
        $hijriDay = $jd - (int)(29 * $hijriMonth);
        $hijriMonth = (int)(($hijriMonth + 24) % 12) + 1;
        $hijriYear = 30 * $n + $j - 30;
        
        return [
            'day' => $hijriDay,
            'month' => $hijriMonth,
            'year' => $hijriYear,
            'month_name' => $this->getHijriMonthName($hijriMonth),
            'day_name' => $this->getHijriDayName($jd)
        ];
    }
    
    /**
     * Get Hijri month name
     */
    private function getHijriMonthName(int $month): string
    {
        $months = [
            1 => 'Muharram', 'Safar', 'Rabi al-Awwal', 'Rabi al-Thani',
            'Jumada al-Awwal', 'Jumada al-Thani', 'Rajab', 'Shaban',
            'Ramadan', 'Shawwal', 'Dhu al-Qidah', 'Dhu al-Hijjah'
        ];
        
        return $months[$month] ?? '';
    }
    
    /**
     * Get Hijri day name
     */
    private function getHijriDayName(int $jd): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[($jd + 1) % 7];
    }
}
