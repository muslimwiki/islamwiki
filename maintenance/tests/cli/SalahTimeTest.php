<?php

require_once __DIR__ . '/../src/Core/Logging/Shahid.php';
require_once __DIR__ . '/../src/Core/Islamic/SalahTimeCalculator.php';

use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Islamic\SalahTimeCalculator;

// Initialize logger and calculator
$logger = new Shahid();
$calculator = new SalahTimeCalculator($logger);

// Test locations with different coordinates
$locations = [
    ['name' => 'Makkah', 'lat' => 21.4225, 'lng' => 39.8262],
    ['name' => 'Madinah', 'lat' => 24.5247, 'lng' => 39.5692],
    ['name' => 'Cairo', 'lat' => 30.0444, 'lng' => 31.2357],
    ['name' => 'Istanbul', 'lat' => 41.0082, 'lng' => 28.9784],
    ['name' => 'Kuala Lumpur', 'lat' => 3.1390, 'lng' => 101.6869],
];

// Test date
$year = 2025;
$month = 8;
$day = 15;

// Test different calculation methods
$methods = ['MWL', 'ISNA', 'EGYPT', 'MAKKAH', 'KARACHI', 'TEHRAN', 'JAFARI'];

foreach ($locations as $location) {
    echo "\n\n=== {$location['name']} ({$location['lat']}, {$location['lng']}) ===\n";
    
    foreach ($methods as $method) {
        echo "\nMethod: $method\n";
        
        try {
            $times = $calculator->calculateTimes(
                $location['lat'],
                $location['lng'],
                $year,
                $month,
                $day,
                $method
            );
            
            // Display prayer times
            echo "Fajr:    " . $times['fajr'] . "\n";
            echo "Sunrise: " . $times['sunrise'] . "\n";
            echo "Dhuhr:   " . $times['dhuhr'] . "\n";
            echo "Asr:     " . $times['asr'] . "\n";
            echo "Maghrib: " . $times['maghrib'] . "\n";
            echo "Isha:    " . $times['isha'] . "\n";
            
            // Display additional info
            echo "Qibla:   " . number_format($times['qibla'], 2) . "°\n";
            echo "Lunar Phase: " . number_format($times['lunar_phase'], 2) . " days\n";
            
        } catch (Exception $e) {
            echo "Error with $method: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
}

echo "\nTest completed. Check the results above for any anomalies.\n";
