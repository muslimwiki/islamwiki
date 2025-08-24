<?php

declare(strict_types=1);

/**
 * Fix Service Providers Script
 * 
 * This script fixes all service providers by replacing bind() and singleton()
 * calls with set() calls to match the Container API.
 * 
 * @package IslamWiki\Scripts
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

echo "🔧 **Fixing Service Providers**\n";
echo "==============================\n\n";

$providersDir = __DIR__ . '/../src/Providers';
$files = glob($providersDir . '/*.php');

$totalFiles = count($files);
$fixedFiles = 0;

foreach ($files as $file) {
    $filename = basename($file);
    echo "Processing: {$filename}\n";
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Replace bind() with set()
    $content = preg_replace('/->bind\(/', '->set(', $content);
    
    // Replace singleton() with set()
    $content = preg_replace('/->singleton\(/', '->set(', $content);
    
    // Update comments to reflect the change
    $content = str_replace(
        'Register as a singleton',
        'Register as a service',
        $content
    );
    
    $content = str_replace(
        'Register as singleton',
        'Register as service',
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "  ✅ Fixed: {$filename}\n";
        $fixedFiles++;
    } else {
        echo "  ℹ️  No changes needed: {$filename}\n";
    }
}

echo "\n📊 **Summary**\n";
echo "=============\n";
echo "Total files processed: {$totalFiles}\n";
echo "Files fixed: {$fixedFiles}\n";
echo "Files unchanged: " . ($totalFiles - $fixedFiles) . "\n\n";

if ($fixedFiles > 0) {
    echo "🎉 Service providers have been fixed!\n";
    echo "All bind() and singleton() calls have been replaced with set().\n";
} else {
    echo "ℹ️  No service providers needed fixing.\n";
}

echo "\n"; 