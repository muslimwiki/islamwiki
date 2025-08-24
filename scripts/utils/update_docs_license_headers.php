<?php

/**
 * Script to add/update AGPL-3.0 license headers in documentation files
 */

declare(strict_types=1);

// License header template
$licenseHeader = <<<EOT
<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Container, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->

EOT;

// Function to process files
function processFiles($path, $licenseHeader)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $filesProcessed = 0;
    $filesUpdated = 0;

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $filePath = $file->getRealPath();

            // Skip files in vendor directory
            if (strpos($filePath, '/vendor/') !== false) {
                continue;
            }

            $filesProcessed++;

            // Read file content
            $content = file_get_contents($filePath);

            // Check if file already has a license header
            $hasLicense = strpos($content, 'This file is part of IslamWiki') !== false;

            if (!$hasLicense) {
                // Add license header at the beginning of the file
                $newContent = $licenseHeader . $content;

                // Write back to file
                file_put_contents($filePath, $newContent);
                echo "Added license header to: $filePath\n";
                $filesUpdated++;
            } else {
                // Check if the license is at the top
                $lines = file($filePath, FILE_IGNORE_NEW_LINES);
                $firstLine = trim($lines[0] ?? '');

                if ($firstLine !== '<!--' && strpos($content, 'This file is part of IslamWiki') !== false) {
                    // License exists but not at the top, move it to the top
                    $content = preg_replace('/<!--\s*This file is part of IslamWiki.*?-->\s*/s', '', $content);
                    $newContent = $licenseHeader . ltrim($content);

                    // Write back to file
                    file_put_contents($filePath, $newContent);
                    echo "Moved license header to top in: $filePath\n";
                    $filesUpdated++;
                }
            }
        }
    }

    return [$filesProcessed, $filesUpdated];
}

// Get the project root directory
$projectRoot = dirname(__DIR__);
$docsDir = $projectRoot . '/docs';

// Process files in docs directory
if (is_dir($docsDir)) {
    list($processed, $updated) = processFiles($docsDir, $licenseHeader);
    echo "\nDone! Processed $processed documentation files, updated $updated files.\n";
} else {
    echo "Error: Documentation directory not found at $docsDir\n";
    exit(1);
}
