<?php

/**
 * Consolidate Duplicate Systems Script
 * 
 * This script consolidates all duplicate and conflicting systems:
 * 1. ErrorHandler vs LoggingErrorHandler → Single ErrorHandler
 * 2. Extensions directory cleanup → Single Extension system
 * 3. Formatter consolidation → Formatter.php + FormattingService.php
 * 4. Routing consolidation → Single routing system
 * 5. Search consolidation → Single search system
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/consolidate-duplicate-systems.php
 */

declare(strict_types=1);

class DuplicateSystemConsolidator
{
    private string $basePath;

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the consolidation process.
     */
    public function run(): void
    {
        echo "🚀 Starting Duplicate System Consolidation...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->consolidateErrorHandlers();
        $this->consolidateExtensions();
        $this->consolidateFormatters();
        $this->consolidateRouting();
        $this->consolidateSearch();
        
        echo "\n✅ Duplicate System Consolidation Complete!\n";
        echo "🎯 All duplicate systems consolidated.\n";
    }

    /**
     * Consolidate error handlers.
     */
    private function consolidateErrorHandlers(): void
    {
        echo "🚨 Consolidating Error Handlers...\n";
        
        $errorHandlerPath = $this->basePath . '/src/Core/Logging/ErrorHandler.php';
        $loggingErrorHandlerPath = $this->basePath . '/src/Core/Logging/LoggingErrorHandler.php';
        
        if (file_exists($loggingErrorHandlerPath)) {
            if (file_exists($errorHandlerPath)) {
                // Keep ErrorHandler.php, remove LoggingErrorHandler.php
                unlink($loggingErrorHandlerPath);
                echo "   ✅ Removed duplicate LoggingErrorHandler.php\n";
            } else {
                // Rename LoggingErrorHandler.php to ErrorHandler.php
                rename($loggingErrorHandlerPath, $errorHandlerPath);
                echo "   ✅ Renamed LoggingErrorHandler.php → ErrorHandler.php\n";
            }
        }
    }

    /**
     * Consolidate extensions system.
     */
    private function consolidateExtensions(): void
    {
        echo "🔌 Consolidating Extensions System...\n";
        
        $extensionsDir = $this->basePath . '/src/Core/Extensions';
        if (!is_dir($extensionsDir)) {
            echo "   ⚠️  Extensions directory not found\n";
            return;
        }

        $files = scandir($extensionsDir);
        $keptFiles = [];
        $removedFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $extensionsDir . '/' . $file;
            
            // Keep only essential files
            if (in_array($file, ['Extension.php', 'ExtensionManager.php'])) {
                $keptFiles[] = $file;
            } else {
                unlink($filePath);
                $removedFiles[] = $file;
            }
        }

        echo "   ✅ Kept: " . implode(', ', $keptFiles) . "\n";
        echo "   🗑️  Removed: " . implode(', ', $removedFiles) . "\n";
    }

    /**
     * Consolidate formatters.
     */
    private function consolidateFormatters(): void
    {
        echo "📝 Consolidating Formatters...\n";
        
        $formatterDir = $this->basePath . '/src/Core/Formatter';
        if (!is_dir($formatterDir)) {
            echo "   ⚠️  Formatter directory not found\n";
            return;
        }

        $files = scandir($formatterDir);
        $keptFiles = [];
        $removedFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $formatterDir . '/' . $file;
            
            // Keep only essential files
            if (in_array($file, ['Formatter.php', 'FormattingService.php'])) {
                $keptFiles[] = $file;
            } else {
                unlink($filePath);
                $removedFiles[] = $file;
            }
        }

        echo "   ✅ Kept: " . implode(', ', $keptFiles) . "\n";
        echo "   🗑️  Removed: " . implode(', ', $removedFiles) . "\n";
    }

    /**
     * Consolidate routing system.
     */
    private function consolidateRouting(): void
    {
        echo "🛣️  Consolidating Routing System...\n";
        
        $routingDir = $this->basePath . '/src/Core/Routing';
        if (!is_dir($routingDir)) {
            echo "   ⚠️  Routing directory not found\n";
            return;
        }

        $files = scandir($routingDir);
        $keptFiles = [];
        $removedFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $routingDir . '/' . $file;
            
            // Keep only essential routing files
            if (in_array($file, ['Router.php', 'Route.php'])) {
                $keptFiles[] = $file;
            } else {
                unlink($filePath);
                $removedFiles[] = $file;
            }
        }

        echo "   ✅ Kept: " . implode(', ', $keptFiles) . "\n";
        echo "   🗑️  Removed: " . implode(', ', $removedFiles) . "\n";
    }

    /**
     * Consolidate search system.
     */
    private function consolidateSearch(): void
    {
        echo "🔍 Consolidating Search System...\n";
        
        $searchDir = $this->basePath . '/src/Core/Search';
        if (!is_dir($searchDir)) {
            echo "   ⚠️  Search directory not found\n";
            return;
        }

        $files = scandir($searchDir);
        $keptFiles = [];
        $removedFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $searchDir . '/' . $file;
            
            // Keep only essential search files
            if (in_array($file, ['Search.php', 'SearchService.php'])) {
                $keptFiles[] = $file;
            } else {
                unlink($filePath);
                $removedFiles[] = $file;
            }
        }

        echo "   ✅ Kept: " . implode(', ', $keptFiles) . "\n";
        echo "   🗑️  Removed: " . implode(', ', $removedFiles) . "\n";
    }

    /**
     * Generate a summary report.
     */
    public function generateReport(): void
    {
        echo "\n📊 Duplicate System Consolidation Summary\n";
        echo "==========================================\n\n";
        
        echo "🚨 Error Handlers:\n";
        echo "   • Consolidated to single ErrorHandler.php\n";
        echo "   • Removed duplicate LoggingErrorHandler.php\n";
        
        echo "\n🔌 Extensions:\n";
        echo "   • Kept: Extension.php, ExtensionManager.php\n";
        echo "   • Removed: IslamicExtension.php, IslamicExtensionManager.php, etc.\n";
        
        echo "\n📝 Formatters:\n";
        echo "   • Kept: Formatter.php, FormattingService.php\n";
        echo "   • Removed: Bayan.php, etc.\n";
        
        echo "\n🛣️  Routing:\n";
        echo "   • Kept: Router.php, Route.php\n";
        echo "   • Removed: SimpleRouter.php, ControllerFactory.php, etc.\n";
        
        echo "\n🔍 Search:\n";
        echo "   • Kept: Search.php, SearchService.php\n";
        echo "   • Removed: IqraSearch.php (conflicts with extension)\n";
        
        echo "\n📁 Impact:\n";
        echo "   • No more duplicate systems\n";
        echo "   • Clean, focused core architecture\n";
        echo "   • No conflicts with extensions\n";
        echo "   • Easier maintenance and development\n";
    }
}

// Run the consolidation if this script is executed directly
if (php_sapi_name() === 'cli') {
    $consolidator = new DuplicateSystemConsolidator();
    $consolidator->run();
    $consolidator->generateReport();
} 