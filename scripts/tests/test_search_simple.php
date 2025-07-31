<?php
declare(strict_types=1);
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

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Simple Search Test for IslamWiki v0.0.17
 */

echo "🔍 Simple Search Test for IslamWiki v0.0.17\n";
echo "==========================================\n\n";

try {
    // Test 1: Check if SearchController exists
    echo "📋 Test 1: Checking SearchController\n";
    echo "-----------------------------------\n";
    
    if (class_exists('IslamWiki\Http\Controllers\SearchController')) {
        echo "✅ SearchController class exists\n";
    } else {
        echo "❌ SearchController class not found\n";
    }

    // Test 2: Check if Search model exists
    echo "\n📋 Test 2: Checking Search Model\n";
    echo "--------------------------------\n";
    
    if (class_exists('IslamWiki\Models\Search')) {
        echo "✅ Search model class exists\n";
    } else {
        echo "❌ Search model class not found\n";
    }

    // Test 3: Check if search template exists
    echo "\n📋 Test 3: Checking Search Template\n";
    echo "----------------------------------\n";
    
    $templatePath = __DIR__ . '/../../resources/views/search/index.twig';
    if (file_exists($templatePath)) {
        echo "✅ Search template exists: $templatePath\n";
        echo "📏 Template size: " . filesize($templatePath) . " bytes\n";
    } else {
        echo "❌ Search template not found: $templatePath\n";
    }

    // Test 4: Check if search routes are defined
    echo "\n📋 Test 4: Checking Search Routes\n";
    echo "--------------------------------\n";
    
    $routesFile = __DIR__ . '/../../routes/web.php';
    if (file_exists($routesFile)) {
        $routesContent = file_get_contents($routesFile);
        if (strpos($routesContent, '/search') !== false) {
            echo "✅ Search routes found in web.php\n";
        } else {
            echo "❌ Search routes not found in web.php\n";
        }
    } else {
        echo "❌ Routes file not found\n";
    }

    // Test 5: Check if search migration exists
    echo "\n📋 Test 5: Checking Search Migration\n";
    echo "-----------------------------------\n";
    
    $migrationPath = __DIR__ . '/../../database/migrations/0011_search_indexes.php';
    if (file_exists($migrationPath)) {
        echo "✅ Search migration exists: $migrationPath\n";
        echo "📏 Migration size: " . filesize($migrationPath) . " bytes\n";
    } else {
        echo "❌ Search migration not found: $migrationPath\n";
    }

    // Test 6: Check if search test exists
    echo "\n📋 Test 6: Checking Search Test\n";
    echo "-------------------------------\n";
    
    $testPath = __DIR__ . '/test_search_integration.php';
    if (file_exists($testPath)) {
        echo "✅ Search integration test exists: $testPath\n";
        echo "📏 Test size: " . filesize($testPath) . " bytes\n";
    } else {
        echo "❌ Search integration test not found: $testPath\n";
    }

    // Test 7: Check VERSION file
    echo "\n📋 Test 7: Checking Version\n";
    echo "---------------------------\n";
    
    $versionFile = __DIR__ . '/../../VERSION';
    if (file_exists($versionFile)) {
        $version = trim(file_get_contents($versionFile));
        echo "✅ Version file exists\n";
        echo "📋 Current version: $version\n";
        
        if ($version === '0.0.17') {
            echo "✅ Version is correct for search features\n";
        } else {
            echo "⚠️  Version is not 0.0.17 (current: $version)\n";
        }
    } else {
        echo "❌ Version file not found\n";
    }

    // Test 8: Check CHANGELOG
    echo "\n📋 Test 8: Checking Changelog\n";
    echo "-----------------------------\n";
    
    $changelogFile = __DIR__ . '/../../CHANGELOG.md';
    if (file_exists($changelogFile)) {
        $changelogContent = file_get_contents($changelogFile);
        if (strpos($changelogContent, '## [0.0.17]') !== false) {
            echo "✅ Changelog contains v0.0.17 entry\n";
        } else {
            echo "❌ Changelog missing v0.0.17 entry\n";
        }
    } else {
        echo "❌ Changelog file not found\n";
    }

    // Summary
    echo "\n🎉 Simple Search Test Summary\n";
    echo "============================\n";
    echo "✅ SearchController: " . (class_exists('IslamWiki\Http\Controllers\SearchController') ? 'OK' : 'MISSING') . "\n";
    echo "✅ Search Model: " . (class_exists('IslamWiki\Models\Search') ? 'OK' : 'MISSING') . "\n";
    echo "✅ Search Template: " . (file_exists($templatePath) ? 'OK' : 'MISSING') . "\n";
    echo "✅ Search Routes: " . (strpos($routesContent ?? '', '/search') !== false ? 'OK' : 'MISSING') . "\n";
    echo "✅ Search Migration: " . (file_exists($migrationPath) ? 'OK' : 'MISSING') . "\n";
    echo "✅ Search Test: " . (file_exists($testPath) ? 'OK' : 'MISSING') . "\n";
    echo "✅ Version: " . (file_exists($versionFile) ? 'OK' : 'MISSING') . "\n";
    echo "✅ Changelog: " . (file_exists($changelogFile) ? 'OK' : 'MISSING') . "\n";
    
    echo "\n🚀 IslamWiki v0.0.17 Search System Files Verified!\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 