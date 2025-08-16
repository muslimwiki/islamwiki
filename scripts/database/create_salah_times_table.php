<?php

/**
 * Create Salah Times Table Script
 *
 * This script manually creates the missing salah_times table that the Iqra search system
 * requires. The table structure matches what the SalahTime model expects.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

echo "Creating salah_times table...\n";

try {
    // Get database connection
    $connection = new Connection();
    
    // Check if table already exists
    $stmt = $connection->query("SHOW TABLES LIKE 'salah_times'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'salah_times' already exists\n";
        exit(0);
    }
    
    // Create the table
    $sql = "CREATE TABLE `salah_times` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `date` date NOT NULL,
        `location_name` varchar(255) DEFAULT NULL,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `timezone` varchar(50) DEFAULT 'UTC',
        `fajr` time DEFAULT NULL,
        `sunrise` time DEFAULT NULL,
        `dhuhr` time DEFAULT NULL,
        `asr` time DEFAULT NULL,
        `maghrib` time DEFAULT NULL,
        `isha` time DEFAULT NULL,
        `calculation_method` varchar(50) DEFAULT 'MWL',
        `asr_juristic` varchar(20) DEFAULT 'Standard',
        `adjust_high_lats` tinyint(1) DEFAULT 1,
        `minutes_offset` int(11) DEFAULT 0,
        `location_id` bigint(20) unsigned DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `date` (`date`),
        KEY `location_name` (`location_name`),
        KEY `latitude_longitude` (`latitude`,`longitude`),
        KEY `calculation_method` (`calculation_method`),
        KEY `location_id` (`location_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $connection->query($sql);
    echo "✅ Table 'salah_times' created successfully\n";
    
    // Insert some sample data for testing
    $sampleData = [
        [
            'date' => date('Y-m-d'),
            'location_name' => 'Mecca, Saudi Arabia',
            'latitude' => 21.4225,
            'longitude' => 39.8262,
            'timezone' => 'Asia/Riyadh',
            'fajr' => '05:30:00',
            'sunrise' => '06:45:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:30:00',
            'maghrib' => '18:15:00',
            'isha' => '19:45:00',
            'calculation_method' => 'MAKKAH',
            'asr_juristic' => 'Standard',
            'adjust_high_lats' => 1,
            'minutes_offset' => 0
        ],
        [
            'date' => date('Y-m-d'),
            'location_name' => 'Medina, Saudi Arabia',
            'latitude' => 24.5247,
            'longitude' => 39.5692,
            'timezone' => 'Asia/Riyadh',
            'fajr' => '05:25:00',
            'sunrise' => '06:40:00',
            'dhuhr' => '12:10:00',
            'asr' => '15:25:00',
            'maghrib' => '18:10:00',
            'isha' => '19:40:00',
            'calculation_method' => 'MAKKAH',
            'asr_juristic' => 'Standard',
            'adjust_high_lats' => 1,
            'minutes_offset' => 0
        ]
    ];
    
    foreach ($sampleData as $data) {
        $sql = "INSERT INTO salah_times (
            date, location_name, latitude, longitude, timezone,
            fajr, sunrise, dhuhr, asr, maghrib, isha,
            calculation_method, asr_juristic, adjust_high_lats, minutes_offset,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            $data['date'], $data['location_name'], $data['latitude'], $data['longitude'], $data['timezone'],
            $data['fajr'], $data['sunrise'], $data['dhuhr'], $data['asr'], $data['maghrib'], $data['isha'],
            $data['calculation_method'], $data['asr_juristic'], $data['adjust_high_lats'], $data['minutes_offset']
        ]);
    }
    
    echo "✅ Sample data inserted successfully\n";
    echo "✅ Table 'salah_times' is ready for use\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 