<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helpers.php';

/**
 * Clean existing translations in the database by removing footnote tags and other HTML-like elements
 */

// Initialize database connection
try {
    $container = new \IslamWiki\Core\Container\Container
    $container->bind('config', function () {
        return require __DIR__ . '/../../config/config.php';
    });
    
    $container->bind('database', function () use ($container) {
        $config = $container->get('config');
        return new Connection($config['database']);
    });
    
    $pdo = $container->get('database')->getPdo();
} catch (Exception $e) {
    fwrite(STDERR, 'Database connection failed: ' . $e->getMessage() . "\n");
    exit(1);
}

/**
 * Clean translation text by removing footnote tags and other HTML-like elements
 */
function cleanTranslationText(string $text): string
{
    // Remove footnote tags like <sup foot_note=195932>
    $text = preg_replace('/<sup[^>]*>.*?<\/sup>/s', '', $text);
    
    // Remove other HTML-like tags that might be present
    $text = preg_replace('/<[^>]+>/', '', $text);
    
    // Clean up extra whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Trim whitespace
    $text = trim($text);
    
    return $text;
}

try {
    echo "Cleaning existing translations...\n";
    
    // Get all verse translations
    $stmt = $pdo->prepare("SELECT id, text FROM verse_translations");
    $stmt->execute();
    $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $cleaned = 0;
    $unchanged = 0;
    
    foreach ($translations as $translation) {
        $originalText = $translation['text'];
        $cleanedText = cleanTranslationText($originalText);
        
        if ($cleanedText !== $originalText) {
            // Update the cleaned text
            $updateStmt = $pdo->prepare("UPDATE verse_translations SET text = ? WHERE id = ?");
            $updateStmt->execute([$cleanedText, $translation['id']]);
            $cleaned++;
            
            echo "Cleaned translation ID {$translation['id']}\n";
            echo "  Before: " . substr($originalText, 0, 100) . "...\n";
            echo "  After:  " . substr($cleanedText, 0, 100) . "...\n";
        } else {
            $unchanged++;
        }
    }
    
    echo "\nCleaning completed!\n";
    echo "Cleaned: $cleaned translations\n";
    echo "Unchanged: $unchanged translations\n";
    
} catch (Exception $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
