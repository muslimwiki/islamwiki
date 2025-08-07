<?php

/**
 * Script to update PHP file headers to include AGPL-3.0 license information
 */

declare(strict_types=1);

$licenseHeader = <<<EOT
<?php
/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
    $filesUpdated = 0;

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

            // Check if file already has the complete AGPL header
            if (strpos($content, 'GNU Affero General Public License') !== false) {
                echo "Skipping (already has AGPL notice): $filePath\n";
                continue;
            }

            // Remove existing header if it exists
            $pattern = '/<\?php\s*\/\*\*[\s\S]*?\*\/\s*/';
            $newContent = preg_replace($pattern, '', $content, 1, $count);

            if ($count === 0) {
                // No existing header found, remove just the opening PHP tag if it exists
                $newContent = preg_replace('/^<\?php\s*/', '', $content, 1);
            }

            // Add the new header
            $newContent = "<?php\n" . $licenseHeader . $newContent;

            // Write back to file if content changed
            if ($newContent !== $content) {
                file_put_contents($filePath, $newContent);
                echo "Updated license header in: $filePath\n";
                $filesUpdated++;
            }
        }
    }

    return [$filesProcessed, $filesUpdated];
}

// Get the project root directory
$projectRoot = dirname(__DIR__);

// Process files in src and public directories
$totalProcessed = 0;
$totalUpdated = 0;

$directories = ['/src', '/public', '/config', '/database', '/tests'];

foreach ($directories as $dir) {
    if (is_dir($projectRoot . $dir)) {
        list($processed, $updated) = processFiles($projectRoot . $dir);
        $totalProcessed += $processed;
        $totalUpdated += $updated;
    }
}

echo "\nDone! Processed $totalProcessed files, updated $totalUpdated files with AGPL-3.0 license headers.\n";
