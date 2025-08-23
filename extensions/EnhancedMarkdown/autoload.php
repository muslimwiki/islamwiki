<?php
/**
 * Autoloader for EnhancedMarkdown Extension
 * 
 * Handles loading of all classes within the extension
 */

spl_autoload_register(function ($class) {
    // Only handle classes in our namespace
    if (strpos($class, 'IslamWiki\\Extensions\\EnhancedMarkdown\\') !== 0) {
        return;
    }
    
    // Remove namespace prefix
    $relativeClass = str_replace('IslamWiki\\Extensions\\EnhancedMarkdown\\', '', $class);
    
    // Handle main class in root directory
    if ($relativeClass === 'EnhancedMarkdown') {
        $file = __DIR__ . '/EnhancedMarkdown.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Handle classes in src/ directory
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return;
    }
}); 