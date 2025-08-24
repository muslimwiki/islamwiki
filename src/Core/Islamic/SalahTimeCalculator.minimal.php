<?php
declare(strict_types=1);

namespace IslamWiki\Core\Islamic;

use Logging;\Logger
use const M_PI_2;

class SalahTimeCalculator
{
    private Logging $logger;
    private array $methods = [];

    public function __construct(Logging $logger)
    {
        $this->logger = $logger;
        $this->initializeMethods();
    }

    private function initializeMethods(): void
    {
        $this->methods = [
            'MWL' => ['fajr' => 18, 'maghrib' => 17, 'isha' => 18, 'asr_factor' => 1],
            'ISNA' => ['fajr' => 15, 'maghrib' => 15, 'isha' => 15, 'asr_factor' => 1],
            'MAKKAH' => ['fajr' => 18.5, 'maghrib' => 90, 'isha' => 90, 'asr_factor' => 1],
        ];
    }

    public function calculateTimes(
        float $latitude,
        float $longitude,
        int $year,
        int $month,
        int $day,
        string $method = 'MWL',
        float $timezone = 0
    ): array {
        if (!isset($this->methods[$method])) {
            $method = 'MWL';
        }
        
        // Convert date to Julian day at 12:00 UTC
        $jd = $this->gregorianToJulianDay($year, $month, $day);
        
        // Calculate solar coordinates for today
        $solarCoords = $this->calculateSolarCoordinates($jd);
        $params = $this->methods[$method];
        
        // Calculate prayer times (in Julian days)
        $fajr = $this->calculateFajr($latitude, $longitude, $solarCoords, $params['fajr']);
        $sunrise = $this->calculateSunrise($latitude, $longitude, $solarCoords);
        $dhuhr = $this->calculateDhuhr($longitude, $solarCoords);
        $asr = $this->calculateAsr($latitude, $longitude, $solarCoords, $params['asr_factor']);
        $maghrib = $this->calculateMaghrib($latitude, $longitude, $solarCoords, $params['maghrib']);
        $isha = $this->calculateIsha($latitude, $longitude, $solarCoords, $params['isha']);
        
        // Ensure all times are within the same day
        $base = floor($jd + 0.5);
        $fajr = $base + ($fajr - floor($fajr));
        $sunrise = $base + ($sunrise - floor($sunrise));
        $dhuhr = $base + ($dhuhr - floor($dhuhr));
        $asr = $base + ($asr - floor($asr));
        $maghrib = $base + ($maghrib - floor($maghrib));
        $isha = $base + ($isha - floor($isha));
        
        // Adjust times that might be on the next day
        $times = [
            'fajr' => $fajr,
            'sunrise' => $sunrise,
            'dhuhr' => $dhuhr,
            'asr' => $asr,
            'maghrib' => $maghrib,
            'isha' => $isha
        ];
        
        // Sort times to ensure proper order
        asort($times);
        
        // Format times with timezone adjustment
        return [
            'fajr' => $this->formatTime($times['fajr'], $timezone),
            'sunrise' => $this->formatTime($times['sunrise'], $timezone),
            'dhuhr' => $this->formatTime($times['dhuhr'], $timezone),
            'asr' => $this->formatTime($times['asr'], $timezone),
            'maghrib' => $this->formatTime($times['maghrib'], $timezone),
            'isha' => $this->formatTime($times['isha'], $timezone),
            'method' => $method,
            'date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
            'timezone' => $timezone
        ];
    }

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
     * Calculate solar coordinates (declination and equation of time) for a given Julian Day
     */
    private function calculateSolarCoordinates(float $jd): array
    {
        // Julian centuries from J2000.0
        $T = ($jd - 2451545.0) / 36525.0;
        
        // Mean solar longitude (degrees)
        $L0 = 280.46646 + $T * (36000.76983 + $T * 0.0003032);
        $L0 = fmod($L0, 360);
        if ($L0 < 0) $L0 += 360;
        
        // Mean solar anomaly (degrees)
        $M = 357.52911 + $T * (35999.05029 - 0.0001537 * $T);
        $M_rad = deg2rad($M);
        
        // Sun's equation of center (degrees)
        $C = (1.914602 - $T * 0.004817) * sin($M_rad) 
           + 0.019993 * sin(2 * $M_rad) 
           + 0.000289 * sin(3 * $M_rad);
        
        // Sun's true longitude (degrees)
        $O = $L0 + $C;
        
        // Mean obliquity of the ecliptic (degrees)
        $epsilon0 = 23 + (26 + 21.448/60) - (46.815/3600) * $T;
        
        // Sun's right ascension and declination
        $lambda_rad = deg2rad($O);
        $epsilon_rad = deg2rad($epsilon0);
        
        $alpha = atan2(
            cos($epsilon_rad) * sin($lambda_rad),
            cos($lambda_rad)
        );
        
        $delta = asin(sin($epsilon_rad) * sin($lambda_rad));
        
        // Equation of time (minutes)
        $eot = rad2deg($L0 - $alpha * 180/M_PI - 0.0057183) * 4;
        
        return [
            'declination' => rad2deg($delta),
            'equationOfTime' => $eot,
            'solarNoon' => $jd - $eot / 1440.0,
            'declination_rad' => $delta,
            'rightAscension' => $alpha,
            'meanAnomaly' => $M
        ];
    }
    
    /**
     * Calculate time for a given sun angle
     */
    private function calculateTimeAngle(
        float $latitude,
        float $longitude,
        array $solarCoords,
        float $angle,
        bool $afterNoon = false
    ): float {
        $lat_rad = deg2rad($latitude);
        $delta_rad = $solarCoords['declination_rad'];
        
        // Calculate the hour angle
        $cos_hour_angle = (sin(deg2rad(-$angle)) - sin($lat_rad) * sin($delta_rad)) 
                         / (cos($lat_rad) * cos($delta_rad));
        
        // Handle edge cases where the sun doesn't reach the angle
        if ($cos_hour_angle < -1.0) {
            return $afterNoon ? 1.0 : 0.0; // Sun doesn't reach this angle today
        }
        if ($cos_hour_angle > 1.0) {
            return $afterNoon ? 1.0 : 0.0; // Sun is always above/below this angle
        }
        
        $hour_angle = acos($cos_hour_angle);
        $hour_angle = rad2deg($hour_angle) / 15.0; // Convert to hours
        
        $time = $solarCoords['solarNoon'] + ($afterNoon ? 1 : -1) * ($hour_angle / 24.0);
        
        // Ensure the time is within the same day
        if ($time < 0) $time += 1.0;
        if ($time >= 1.0) $time -= 1.0;
        
        return $time;
    }
    
    /**
     * Calculate Fajr time
     */
    private function calculateFajr(
        float $latitude,
        float $longitude,
        array $solarCoords,
        float $angle
    ): float {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, $angle, false);
    }
    
    /**
     * Calculate Sunrise time
     */
    private function calculateSunrise(
        float $latitude,
        float $longitude,
        array $solarCoords
    ): float {
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -0.833, false);
    }
    
    /**
     * Calculate Dhuhr time
     */
    private function calculateDhuhr(
        float $longitude,
        array $solarCoords
    ): float {
        // Dhuhr is just the solar noon adjusted for longitude
        return $solarCoords['solarNoon'];
    }
    
    /**
     * Calculate Asr time
     */
    private function calculateAsr(
        float $latitude,
        float $longitude,
        array $solarCoords,
        int $factor = 1
    ): float {
        // Shadow factor: 1 for standard Shafi'i, 2 for Hanafi
        $angle = -rad2deg(atan(1 / ($factor + tan(
            abs($solarCoords['declination_rad'] - deg2rad($latitude))
        ))));
        
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, $angle, true);
    }
    
    /**
     * Calculate Maghrib time
     */
    private function calculateMaghrib(
        float $latitude,
        float $longitude,
        array $solarCoords,
        float $angle = 0.0
    ): float {
        if ($angle > 0) {
            // If angle is positive, treat as minutes after sunset
            return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -0.833, true) + ($angle / 1440.0);
        }
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, -0.833, true);
    }
    
    /**
     * Calculate Isha time
     */
    private function calculateIsha(
        float $latitude,
        float $longitude,
        array $solarCoords,
        float $angle
    ): float {
        if ($angle > 80) {
            // If angle is > 80, treat as minutes after Maghrib
            $maghrib = $this->calculateMaghrib($latitude, $longitude, $solarCoords, 0);
            return $maghrib + ($angle / 1440.0);
        }
        return $this->calculateTimeAngle($latitude, $longitude, $solarCoords, $angle, true);
    }
    
    /**
     * Calculate arccotangent (inverse cotangent)
     */
    private function acot(float $x): float
    {
        // Handle division by zero
        if ($x == 0) {
            return M_PI_2;
        }
        return atan(1 / $x);
    }
    
    /**
     * Format Julian day to time string (HH:MM)
     * @param float $julianDay Julian day
     * @param float $timezone Timezone offset in hours
     * @return string Formatted time in 24-hour format (HH:MM)
     */
    private function formatTime(float $julianDay, float $timezone = 0): string
    {
        // Get the fractional part of the Julian day (time of day)
        $time = $julianDay - floor($julianDay);
        
        // Convert to hours and add timezone offset
        $hours = $time * 24.0 + $timezone;
        
        // Normalize to 0-24 range
        $hours = fmod($hours + 24.0, 24.0);
        
        $hour = (int)$hours;
        $minute = (int)(($hours - $hour) * 60 + 0.5);
        
        // Handle minute overflow
        if ($minute >= 60) {
            $minute -= 60;
            $hour++;
            if ($hour >= 24) {
                $hour -= 24;
            }
        }
        
        return sprintf('%02d:%02d', $hour, $minute);
    }
}
