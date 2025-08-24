<?php

/**
 * Cleanup Redundant Names Script
 * 
 * This script cleans up redundant names that were created during the Islamic naming cleanup.
 * Examples: Database.php → Database.php, QueueService.php → QueueService.php
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/cleanup-redundant-names.php
 */

declare(strict_types=1);

class RedundantNameCleanup
{
    private string $basePath;
    private array $redundantMappings = [
        'Database' => 'Database',
        'Queue' => 'Queue',
        'QueueService' => 'QueueService',
        'Application' => 'Application',
        'Container' => 'Container',
        'Knowledge' => 'Knowledge',
        'Logging' => 'Logging',
        'LoggingService' => 'LoggingService',
        'AuthenticationController' => 'AuthenticationController',
        'AuthService' => 'AuthService'
    ];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the cleanup process.
     */
    public function run(): void
    {
        echo "🚀 Starting Redundant Name Cleanup...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->renameRedundantFiles();
        $this->updateFileContents();
        
        echo "\n✅ Redundant Name Cleanup Complete!\n";
        echo "🎯 All redundant names eliminated.\n";
    }

    /**
     * Rename files with redundant names.
     */
    private function renameRedundantFiles(): void
    {
        echo "📄 Renaming redundant files...\n";
        
        $files = $this->getAllFiles($this->basePath);
        $renamedCount = 0;

        foreach ($files as $file) {
            $fileName = basename($file);
            $newName = $this->getNewName($fileName);
            
            if ($newName !== $fileName) {
                $newPath = dirname($file) . '/' . $newName;
                
                if (!file_exists($newPath)) {
                    if (rename($file, $newPath)) {
                        echo "   ✅ {$fileName} → {$newName}\n";
                        $renamedCount++;
                    } else {
                        echo "   ❌ Failed to rename {$fileName}\n";
                    }
                } else {
                    echo "   ⚠️  Target {$newName} already exists, skipping {$fileName}\n";
                }
            }
        }

        echo "   ✅ Renamed {$renamedCount} files\n";
    }

    /**
     * Update file contents to use new names.
     */
    private function updateFileContents(): void
    {
        echo "📝 Updating file contents...\n";
        
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
     * Process a single file for content updates.
     */
    private function processFile(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $originalContent = $content;
        $updated = false;

        foreach ($this->redundantMappings as $oldName => $newName) {
            $newContent = str_replace($oldName, $newName, $content);
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
     * Get new name for a file.
     */
    private function getNewName(string $name): string
    {
        foreach ($this->redundantMappings as $oldName => $newName) {
            if (strpos($name, $oldName) !== false) {
                return str_replace($oldName, $newName, $name);
            }
        }
        return $name;
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
     * Check if a file should be processed for content updates.
     */
    private function shouldProcessFile(string $filePath): bool
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/logs', 'backup'];
        
        // Check file extension
        if (!in_array('.' . $extension, ['.php', '.twig', '.md', '.json', '.yml', '.yaml', '.html'])) {
            return false;
        }

        // Check if file is in excluded directories
        foreach ($excludeDirs as $excludeDir) {
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
        echo "\n📊 Redundant Name Cleanup Summary\n";
        echo "==================================\n\n";
        
        echo "🔄 Name Cleanup:\n";
        echo "   • Database → Database\n";
        echo "   • Queue → Queue\n";
        echo "   • QueueService → QueueService\n";
        echo "   • Application → Application\n";
        echo "   • Container → Container\n";
        echo "   • Knowledge → Knowledge\n";
        echo "   • Logging → Logging\n";
        echo "   • AuthenticationController → AuthenticationController\n";
        echo "   • AuthService → AuthService\n";
        
        echo "\n📁 Impact:\n";
        echo "   • Clean, professional file names\n";
        echo "   • No more redundant naming\n";
        echo "   • Consistent naming conventions\n";
        echo "   • Easier to understand and maintain\n";
    }
}

// Run the cleanup if this script is executed directly
if (php_sapi_name() === 'cli') {
    $cleanup = new RedundantNameCleanup();
    $cleanup->run();
    $cleanup->generateReport();
} 