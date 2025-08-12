<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Logging/Shahid.php';
require_once __DIR__ . '/../src/Core/Islamic/SalahTimeCalculator.minimal.php';

use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Islamic\SalahTimeCalculator;

/**
 * Test class for Salah time calculations
 */
class SalahTimeTest
{
    private SalahTimeCalculator $calculator;
    private Shahid $logger;

    public function __construct()
    {
        $this->logger = new Shahid();
        $this->calculator = new SalahTimeCalculator($this->logger);
    }
    
    /**
     * Get timezone offset in hours based on longitude
     */
    private function getTimezoneOffset(float $longitude): float
    {
        // Simple timezone offset based on longitude (1 hour per 15 degrees)
        return round($longitude / 15.0);
    }
    
    /**
     * Convert time string (HH:MM) to seconds since midnight
     */
    private function timeToSeconds(string $time): int
    {
        list($hours, $minutes) = explode(':', $time);
        return (int)$hours * 3600 + (int)$minutes * 60;
    }

    /**
     * Test prayer times for a specific date
     */
    private function testDate(DateTime $date, string $description = ''): void
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');
        $day = (int)$date->format('d');
        
        if ($description) {
            echo "\n\n=== $description ===\n";
            echo "Date: " . $date->format('Y-m-d (l)') . "\n";
            echo str_repeat("=", 60) . "\n";
        }
        $locations = [
            'Makkah' => [
                'lat' => 21.3891,
                'lng' => 39.8579,
                'method' => 'MAKKAH',
                'timezone' => 3 // Fixed timezone for Makkah
            ],
            'Cairo' => [
                'lat' => 30.0444,
                'lng' => 31.2357,
                'method' => 'EGYPT',
                'timezone' => 2 // Fixed timezone for Cairo
            ],
            'Istanbul' => [
                'lat' => 41.0082,
                'lng' => 28.9784,
                'method' => 'MWL',
                'timezone' => 3 // Fixed timezone for Istanbul
            ],
            'Kuala Lumpur' => [
                'lat' => 3.1390,
                'lng' => 101.6869,
                'method' => 'MWL',
                'timezone' => 8 // Fixed timezone for Kuala Lumpur
            ],
            'New York' => [
                'lat' => 40.7128,
                'lng' => -74.0060,
                'method' => 'ISNA',
                'timezone' => -5 // Fixed timezone for New York
            ]
        ];

        $date = new DateTime('tomorrow');
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');
        $day = (int)$date->format('d');

        foreach ($locations as $name => $loc) {
            echo "\n=== $name ===\n";
            echo "Date: " . $date->format('Y-m-d') . "\n";
            echo "Location: {$loc['lat']}°N, {$loc['lng']}°E\n";
            echo "Method: {$loc['method']} (TZ: {$loc['timezone']}h)\n";
            echo str_repeat("-", 40) . "\n";
            
            try {
                $times = $this->calculator->calculateTimes(
                    $loc['lat'],
                    $loc['lng'],
                    $year,
                    $month,
                    $day,
                    $loc['method'],
                    $loc['timezone']
                );
                
                // Output prayer times
                $prayerTimes = [
                    'Fajr'    => $times['fajr'],
                    'Sunrise' => $times['sunrise'],
                    'Dhuhr'   => $times['dhuhr'],
                    'Asr'     => $times['asr'],
                    'Maghrib' => $times['maghrib'],
                    'Isha'    => $times['isha']
                ];
                
                // Output prayer times
                foreach ($prayerTimes as $prayer => $time) {
                    echo str_pad("$prayer:", 9) . str_pad($time, 8) . "\n";
                }
                
                // Validate prayer times order
                $prevTime = null;
                $prevPrayer = null;
                $hasError = false;
                
                foreach ($prayerTimes as $prayer => $time) {
                    if ($prevTime !== null) {
                        $time1 = $this->timeToSeconds($time);
                        $time2 = $this->timeToSeconds($prevTime);
                        
                        // Handle day wrap-around (e.g., 23:30 -> 00:15)
                        if ($time1 <= $time2) {
                            $time1 += 86400; // Add a day
                        }
                        
                        if ($time1 <= $time2) {
                            echo "[ERROR] $prayer ($time) is before $prevPrayer ($prevTime)\n";
                            $hasError = true;
                        }
                    }
                    $prevTime = $time;
                    $prevPrayer = $prayer;
                }
                
                if (!$hasError) {
                    echo "[OK] All prayer times are in correct order\n";
                }
                
                $this->logger->info("Successfully calculated times for $name");
                
            } catch (Exception $e) {
                $this->logger->error("Error calculating times for $name: " . $e->getMessage());
                echo "[ERROR] " . $e->getMessage() . "\n";
            }
            
            echo str_repeat("=", 40) . "\n";
        }
    }
    
    /**
     * Test prayer times for various dates including edge cases
     */
    public function testLocations(): void
    {
        // Test tomorrow's date
        $tomorrow = new DateTime('tomorrow');
        $this->testDate($tomorrow, 'Tomorrow');
        
        // Test summer solstice (longest day in northern hemisphere)
        $summerSolstice = new DateTime('2025-06-20');
        if ($summerSolstice < $tomorrow) {
            $summerSolstice->modify('+1 year');
        }
        $this->testDate($summerSolstice, 'Summer Solstice');
        
        // Test winter solstice (shortest day in northern hemisphere)
        $winterSolstice = new DateTime('2025-12-21');
        if ($winterSolstice < $tomorrow) {
            $winterSolstice->modify('+1 year');
        }
        $this->testDate($winterSolstice, 'Winter Solstice');
        
        // Test spring equinox
        $springEquinox = new DateTime('2025-03-20');
        if ($springEquinox < $tomorrow) {
            $springEquinox->modify('+1 year');
        }
        $this->testDate($springEquinox, 'Spring Equinox');
        
        // Test fall equinox
        $fallEquinox = new DateTime('2025-09-22');
        if ($fallEquinox < $tomorrow) {
            $fallEquinox->modify('+1 year');
        }
        $this->testDate($fallEquinox, 'Fall Equinox');
    }
}

// Run the test
$test = new SalahTimeTest();
$test->testLocations();
