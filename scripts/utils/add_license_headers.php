<?php

/**
 * Script to add AGPL-3.0 license headers to PHP files
 */

declare(strict_types=1);

$licenseHeader = <<<EOT
<?php
/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

EOT;

// Function to process files
function processFiles($path)
{
    global $licenseHeader;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $filesProcessed = 0;

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();

            // Skip files in vendor directory
            if (strpos($filePath, '/vendor/') !== false) {
                continue;
            }

            // Read file content
            $content = file_get_contents($filePath);

            // Check if file already has a license header
            if (strpos($content, 'This file is part of IslamWiki') !== false) {
                echo "Skipping (already has license): $filePath\n";
                continue;
            }

            // Remove opening <?php if it exists
            if (strpos(trim($content), '<?php') === 0) {
                $content = substr($content, 5);
            } elseif (strpos(trim($content), '<?') === 0) {
                $content = substr($content, 2);
            }

            // Add license header
            $newContent = '<?php' . "\n" . $licenseHeader . $content;

            // Write back to file
            file_put_contents($filePath, $newContent);
            echo "Added license to: $filePath\n";
            $filesProcessed++;
        }
    }

    return $filesProcessed;
}

// Get the project root directory
$projectRoot = dirname(__DIR__);

// Process files in src and public directories
$totalProcessed = 0;
$totalProcessed += processFiles($projectRoot . '/src');
$totalProcessed += processFiles($projectRoot . '/public');
$totalProcessed += processFiles($projectRoot . '/config');
$totalProcessed += processFiles($projectRoot . '/database');

echo "\nDone! Added license headers to $totalProcessed files.\n";
