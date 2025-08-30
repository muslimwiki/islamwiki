<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "Autoloader is working!\n";

try {
    // Test if we can load a class from our autoloaded namespace
    if (class_exists('App\\Http\\SimpleRouter')) {
        echo "App classes are autoloading correctly!\n";
    } else {
        echo "Warning: App classes are not autoloading correctly.\n";
    }
    
    // Test if we can load a PSR-7 class
    if (class_exists('Nyholm\\Psr7\\Response')) {
        echo "PSR-7 classes are available!\n";
    } else {
        echo "Warning: PSR-7 classes are not available.\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
