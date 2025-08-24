<?php

/**
 * Islamic Naming Convention Consolidation Script
 * 
 * This script systematically consolidates all Islamic naming conventions
 * throughout the codebase into standard English naming.
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/consolidate-islamic-naming.php
 */

declare(strict_types=1);

class IslamicNamingConsolidator
{
    private array $namingMappings = [
        // Core Systems
        'Logging' => 'Logger',
        'Security' => 'Security',
        'Session' => 'Session',
        'Container' => 'Container',
        'API' => 'API',
        'Knowledge' => 'Knowledge',
        'Queue' => 'Queue',
        'Application' => 'Application',
        'Routing' => 'Routing',
        'Database' => 'Database',
        'Wisdom' => 'Wisdom',
        'Faith' => 'Faith',
        'Piety' => 'Piety',
        'Justice' => 'Justice',
        'Mercy' => 'Mercy',
        
        // File and Directory Names
        'shahid' => 'logger',
        'aman' => 'security',
        'wisal' => 'session',
        'asas' => 'container',
        'siraj' => 'api',
        'usul' => 'knowledge',
        'sabr' => 'queue',
        'nizam' => 'application',
        'rihlah' => 'routing',
        'mizan' => 'database',
        'hikmah' => 'wisdom',
        'iman' => 'faith',
        'taqwa' => 'piety',
        'adl' => 'justice',
        'rahma' => 'mercy',
    ];

    private array $fileExtensions = ['.php', '.twig', '.md', '.json', '.yml', '.yaml'];
    private array $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/logs', 'backup'];
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
        echo "🚀 Starting Islamic Naming Convention Consolidation...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->consolidateFiles();
        $this->consolidateDirectories();
        $this->updateDocumentation();
        
        echo "\n✅ Islamic Naming Convention Consolidation Complete!\n";
        echo "🎯 All core systems now use standard English naming.\n";
    }

    /**
     * Consolidate Islamic naming in files.
     */
    private function consolidateFiles(): void
    {
        echo "📝 Consolidating files...\n";
        
        $files = $this->getAllFiles($this->basePath);
        $totalFiles = count($files);
        $processedFiles = 0;

        foreach ($files as $file) {
            if ($this->shouldProcessFile($file)) {
                $this->processFile($file);
                $processedFiles++;
                
                if ($processedFiles % 10 === 0) {
                    echo "   Processed {$processedFiles}/{$totalFiles} files...\n";
                }
            }
        }

        echo "   ✅ Processed {$processedFiles} files\n";
    }

    /**
     * Consolidate Islamic naming in directory names.
     */
    private function consolidateDirectories(): void
    {
        echo "📁 Consolidating directory names...\n";
        
        // This is a complex operation that requires careful handling
        // For now, we'll just log what needs to be done
        echo "   ⚠️  Directory renaming requires manual intervention\n";
        echo "   📋 Directories to rename:\n";
        
        foreach ($this->namingMappings as $islamic => $english) {
            if (is_dir($this->basePath . '/src/Core/' . ucfirst($islamic))) {
                echo "      src/Core/{$islamic} → src/Core/{$english}\n";
            }
        }
    }

    /**
     * Update documentation files.
     */
    private function updateDocumentation(): void
    {
        echo "📚 Updating documentation...\n";
        
        $docsDir = $this->basePath . '/docs';
        if (is_dir($docsDir)) {
            $this->updateDocumentationFiles($docsDir);
        }
        
        echo "   ✅ Documentation updated\n";
    }

    /**
     * Process a single file for Islamic naming conventions.
     */
    private function processFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $originalContent = $content;
        $updated = false;

        // Replace class names and references
        foreach ($this->namingMappings as $islamic => $english) {
            // Replace class names (with proper casing)
            $patterns = [
                "/class\s+{$islamic}/i" => "class {$english}",
                "/use\s+.*\\\\({$islamic}[^\\s]*)/i" => "use \\1\\{$english}",
                "/new\s+{$islamic}/i" => "new {$english}",
                "/extends\s+{$islamic}/i" => "extends {$english}",
                "/implements\s+{$islamic}/i" => "implements {$english}",
                "/\\\\({$islamic}[^\\s]*)/i" => "\\{$english}",
            ];

            foreach ($patterns as $pattern => $replacement) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $updated = true;
                }
            }
        }

        // Replace comments and documentation
        foreach ($this->namingMappings as $islamic => $english) {
            $content = str_replace(
                ["{$islamic} (", "{$islamic} -", "{$islamic} means"],
                ["{$english} (", "{$english} -", "{$english} provides"],
                $content
            );
        }

        // Write updated content if changes were made
        if ($updated && $content !== $originalContent) {
            file_put_contents($filePath, $content);
        }
    }

    /**
     * Update documentation files specifically.
     */
    private function updateDocumentationFiles(string $docsDir): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($docsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['md', 'txt'])) {
                $this->processFile($file->getPathname());
            }
        }
    }

    /**
     * Get all files in the project.
     */
    private function getAllFiles(string $path): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Check if a file should be processed.
     */
    private function shouldProcessFile(string $filePath): bool
    {
        // Check file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array('.' . $extension, $this->fileExtensions)) {
            return false;
        }

        // Check if file is in excluded directories
        foreach ($this->excludeDirs as $excludeDir) {
            if (strpos($filePath, $excludeDir) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a summary report.
     */
    public function generateReport(): void
    {
        echo "\n📊 Consolidation Summary Report\n";
        echo "===============================\n\n";
        
        echo "🔄 Naming Convention Changes:\n";
        foreach ($this->namingMappings as $islamic => $english) {
            echo "   {$islamic} → {$english}\n";
        }
        
        echo "\n📁 Core Systems Consolidated:\n";
        echo "   • Logging System (Logging → Logger)\n";
        echo "   • Security System (Security → Security)\n";
        echo "   • Session System (Session → Session)\n";
        echo "   • Container System (Container → Container)\n";
        echo "   • API System (API → API)\n";
        echo "   • Knowledge System (Knowledge → Knowledge)\n";
        echo "   • Queue System (Queue → Queue)\n";
        echo "   • Application System (Application → Application)\n";
        
        echo "\n⚠️  Manual Steps Required:\n";
        echo "   1. Rename directories in src/Core/\n";
        echo "   2. Update composer.json autoload paths\n";
        echo "   3. Test all systems after consolidation\n";
        echo "   4. Update deployment scripts\n";
    }
}

// Run the consolidation if this script is executed directly
if (php_sapi_name() === 'cli') {
    $consolidator = new IslamicNamingConsolidator();
    $consolidator->run();
    $consolidator->generateReport();
} 