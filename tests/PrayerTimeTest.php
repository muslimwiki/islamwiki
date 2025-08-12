<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Islamic\SalahTimeCalculator;
use IslamWiki\Core\Logging\Shahid;

class PrayerTimeTest
{
    private SalahTimeCalculator $calculator;
    private Shahid $logger;

    public function __construct()
    {
        $this->logger = new Shahid();
        require_once __DIR__ . '/../src/Core/Islamic/SalahTimeCalculator.php';
        $this->calculator = new SalahTimeCalculator($this->logger);
    }

    public function testLocations()
    {
        $locations = [
            'Mecca' => [21.3891, 39.8579],
            'Medina' => [24.5247, 39.5692],
            'Dubai' => [25.2048, 55.2708],
            'Istanbul' => [41.0082, 28.9784],
            'Kuala Lumpur' => [3.1390, 101.6869],
            'New York' => [40.7128, -74.0060],
            'London' => [51.5074, -0.1278]
        ];

        $methods = ['MWL', 'ISNA', 'EGYPT', 'MAKKAH', 'KARACHI'];
        $date = new DateTime('2025-08-11');
        
        foreach ($locations as $city => $coords) {
            echo "\n=== $city ({$coords[0]}, {$coords[1]}) ===\n";
            
            foreach ($methods as $method) {
                echo "\nMethod: $method\n";
                
                try {
                    $times = $this->calculator->calculateTimes(
                        $coords[0],
                        $coords[1],
                        (int)$date->format('Y'),
                        (int)$date->format('m'),
                        (int)$date->format('d'),
                        $method
                    );
                    
                    echo "Fajr:    " . $times['fajr'] . "\n";
                    echo "Sunrise: " . $times['sunrise'] . "\n";
                    echo "Dhuhr:   " . $times['dhuhr'] . "\n";
                    echo "Asr:     " . $times['asr'] . "\n";
                    echo "Maghrib: " . $times['maghrib'] . "\n";
                    echo "Isha:    " . $times['isha'] . "\n";
                    
                } catch (Exception $e) {
                    echo "Error with $method: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

// Run the test
$test = new PrayerTimeTest();
$test->testLocations();
