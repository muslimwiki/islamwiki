<?php

/**
 * Update Home to Home Script
 * 
 * This script systematically updates all references to Home
 * throughout the codebase to use Home instead.
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/update-home-page-to-home.php
 */

declare(strict_types=1);

class MainPageUpdater
{
    private string $basePath;
    private array $fileExtensions = ['.php', '.twig', '.md', '.json', '.html'];
    private array $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/logs', 'backup'];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the update process.
     */
    public function run(): void
    {
        echo "🚀 Starting Home to Home Update...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->updateFiles();
        $this->updateComments();
        
        echo "\n✅ Home to Home Update Complete!\n";
        echo "🎯 All references now use 'Home' instead of 'Home'.\n";
    }

    /**
     * Update Home references in files.
     */
    private function updateFiles(): void
    {
        echo "📝 Updating files...\n";
        
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
     * Update comments and documentation.
     */
    private function updateComments(): void
    {
        echo "💬 Updating comments and documentation...\n";
        
        // Update specific comment patterns
        $commentPatterns = [
            'Home' => 'Home',
            'home page' => 'home page',
            'Home Page' => 'Home Page',
            'home_page' => 'home_page'
        ];

        $files = $this->getAllFiles($this->basePath);
        $updatedFiles = 0;

        foreach ($files as $file) {
            if ($this->shouldProcessFile($file)) {
                if ($this->updateCommentsInFile($file, $commentPatterns)) {
                    $updatedFiles++;
                }
            }
        }

        echo "   ✅ Updated comments in {$updatedFiles} files\n";
    }

    /**
     * Process a single file for Home references.
     */
    private function processFile(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $originalContent = $content;
        $updated = false;

        // Update Home references
        $patterns = [
            '/wiki/Home' => '/wiki/Home',
            'Home' => 'Home',
            'home_page' => 'home_page',
            'home-page' => 'home-page'
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
     * Update comments in a file.
     */
    private function updateCommentsInFile(string $filePath, array $patterns): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $originalContent = $content;
        $updated = false;

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
        echo "\n📊 Home to Home Update Summary\n";
        echo "==================================\n\n";
        
        echo "🔄 Reference Changes:\n";
        echo "   • /wiki/Home → /wiki/Home\n";
        echo "   • Home → Home\n";
        echo "   • home_page → home_page\n";
        echo "   • home-page → home-page\n";
        
        echo "\n📁 Files Updated:\n";
        echo "   • PHP files (routes, controllers, etc.)\n";
        echo "   • Twig templates (views, layouts)\n";
        echo "   • Markdown files (documentation)\n";
        echo "   • HTML files (test files)\n";
        echo "   • Configuration files\n";
        
        echo "\n🎯 Impact:\n";
        echo "   • Root domain now redirects to /wiki/Home\n";
        echo "   • All internal links updated to use Home\n";
        echo "   • Documentation reflects new naming\n";
        echo "   • Simpler, cleaner URL structure\n";
    }
}

// Run the update if this script is executed directly
if (php_sapi_name() === 'cli') {
    $updater = new MainPageUpdater();
    $updater->run();
    $updater->generateReport();
} 