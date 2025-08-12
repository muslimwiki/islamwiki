<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Logging/Shahid.php';
require_once __DIR__ . '/../src/Core/Islamic/SalahTimeCalculator.minimal.php';

use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Islamic\SalahTimeCalculator;

$logger = new Shahid();
$calculator = new SalahTimeCalculator($logger);

// Test with sample data
$times = $calculator->calculateTimes(
    21.3891, // Makkah latitude
    39.8579, // Makkah longitude
    2025,    // Year
    8,       // Month
        15      // Day
);

// Output results
echo "=== Salah Times ===\n";
echo "Date: {$times['date']}\n";
echo "Method: {$times['method']}\n\n";

echo "Fajr:    {$times['fajr']}\n";
echo "Sunrise: {$times['sunrise']}\n";
echo "Dhuhr:   {$times['dhuhr']}\n";
echo "Asr:     {$times['asr']}\n";
echo "Maghrib: {$times['maghrib']}\n";
echo "Isha:    {$times['isha']}\n";
