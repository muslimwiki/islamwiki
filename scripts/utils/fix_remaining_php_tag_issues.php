<?php

/**
 * Script to fix remaining PHP tag and license header issues
 */

declare(strict_types=1);

// Function to process files
function processFiles($path)
{
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

            // Check if there's a PHP tag after the license header
            $pattern = '/<\?php\/\*\*[\s\S]*?\*\/\s*<\?php\s*declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/';
            $replacement = '<?php\n/**
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

';

            $newContent = preg_replace($pattern, $replacement, $content, -1, $count);

            // If no replacement was done, try a different pattern
            if ($count === 0) {
                $pattern2 = '/<\?php\/\*\*[\s\S]*?\*\/\s*<\?php\s*\n/';
                $replacement2 = '<?php\n/**
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

';
                $newContent = preg_replace($pattern2, $replacement2, $content, -1, $count);
            }

            // Write back to file if content changed
            if ($count > 0) {
                file_put_contents($filePath, $newContent);
                echo "Fixed PHP tags and license header in: $filePath\n";
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

echo "\nDone! Processed $totalProcessed files, fixed $totalFixed files with PHP tag and license header issues.\n";
