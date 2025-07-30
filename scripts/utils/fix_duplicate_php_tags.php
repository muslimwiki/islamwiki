<?php
declare(strict_types=1);
/**
 * Script to fix duplicate PHP opening tags in files
 */

// Function to process files
function processFiles($path) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $filesProcessed = 0;
    $filesFixed = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            
            // Skip files in vendor directory
            if (strpos($filePath, '/vendor/') !== false) {
                continue;
            }
            
            $filesProcessed++;
            
            // Read file content
            $content = file_get_contents($filePath);
            
            // Fix duplicate PHP opening tags
            $newContent = preg_replace('/<\?php\s+<\?php\s+/', '<?php\n', $content);
            
            // Write back to file if content changed
            if ($newContent !== $content) {
                file_put_contents($filePath, $newContent);
                echo "Fixed duplicate PHP tags in: $filePath\n";
                $filesFixed++;
            }
        }
    }
    
    return [$filesProcessed, $filesFixed];
}

// Get the project root directory
$projectRoot = dirname(__DIR__);

// Process files in src and public directories
$totalProcessed = 0;
$totalFixed = 0;

$directories = ['/src', '/public', '/config', '/database', '/tests'];

foreach ($directories as $dir) {
    if (is_dir($projectRoot . $dir)) {
        list($processed, $fixed) = processFiles($projectRoot . $dir);
        $totalProcessed += $processed;
        $totalFixed += $fixed;
    }
}

echo "\nDone! Processed $totalProcessed files, fixed $totalFixed files with duplicate PHP tags.\n";
