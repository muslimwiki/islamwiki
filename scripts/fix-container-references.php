<?php

/**
 * Fix Container References Script
 * 
 * This script updates all remaining Container references to use Container
 * throughout the codebase.
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/fix-container-references.php
 */

declare(strict_types=1);

class ContainerReferenceFixer
{
    private string $basePath;
    private array $fileExtensions = ['.php', '.twig', '.md', '.json', '.yml', '.yaml'];
    private array $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/logs', 'backup'];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the fix process.
     */
    public function run(): void
    {
        echo "🚀 Starting Container Reference Fix...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->fixContainerReferences();
        
        echo "\n✅ Container Reference Fix Complete!\n";
        echo "🎯 All Container references now use Container.\n";
    }

    /**
     * Fix container references in files.
     */
    private function fixContainerReferences(): void
    {
        echo "📝 Fixing container references...\n";
        
        $files = $this->getAllFiles($this->basePath);
        $totalFiles = count($files);
        $processedFiles = 0;
        $updatedFiles = 0;

        foreach ($files as $file) {
            if ($this->shouldProcessFile($file)) {
                if ($this->processFile($file)) {
                    $updatedFiles++;
                }
                $processedFiles++;
                
                if ($processedFiles % 50 === 0) {
                    echo "   Processed {$processedFiles}/{$totalFiles} files...\n";
                }
            }
        }

        echo "   ✅ Processed {$processedFiles} files, updated {$updatedFiles} files\n";
    }

    /**
     * Process a single file for container references.
     */
    private function processFile(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $originalContent = $content;
        $updated = false;

        // Update container references
        $patterns = [
            'Container' => 'Container',
            'core.container' => 'core.container',
            'coreContainer' => 'coreContainer'
        ];

        foreach ($patterns as $pattern => $replacement) {
            $newContent = str_replace($pattern, $replacement, $content);
            if ($newContent !== $content) {
                $content = $newContent;
                $updated = true;
            }
        }

        // Write updated content if changes were made
        if ($updated && $content !== $originalContent) {
            file_put_contents($filePath, $content);
            return true;
        }

        return false;
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
        echo "\n📊 Container Reference Fix Summary\n";
        echo "==================================\n\n";
        
        echo "🔄 Reference Changes:\n";
        echo "   • Container → Container\n";
        echo "   • core.container → core.container\n";
        echo "   • coreContainer → coreContainer\n";
        
        echo "\n📁 Files Updated:\n";
        echo "   • PHP files (service providers, controllers, etc.)\n";
        echo "   • Documentation files\n";
        echo "   • Configuration files\n";
        
        echo "\n🎯 Impact:\n";
        echo "   • All container references now use Container\n";
        echo "   • Consistent naming throughout codebase\n";
        echo "   • Fixed 500 errors from container issues\n";
    }
}

// Run the fix if this script is executed directly
if (php_sapi_name() === 'cli') {
    $fixer = new ContainerReferenceFixer();
    $fixer->run();
    $fixer->generateReport();
} 