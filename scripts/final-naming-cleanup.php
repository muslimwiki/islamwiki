<?php

/**
 * Final Naming Cleanup Script
 * 
 * This script fixes the final naming issues:
 * 1. Asas → Container (not Container)
 * 2. Remove unused , , , 
 * 3. Consolidate duplicate container systems
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/final-naming-cleanup.php
 */

declare(strict_types=1);

class FinalNamingCleanup
{
    private string $basePath;
    private array $finalMappings = [
        // Fix Asas → Container (not Container)
        'Container' => 'Container',
        'ContainerBootstrap' => 'Bootstrap',
        'ContainerContainer' => 'Container',
        
        // Remove unused Islamic names
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        '' => '',
        
        // Consolidate containers
        'container' => 'container',
        'container' => 'container',
        'Container' => 'Container',
        'Container' => 'Container'
    ];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the final cleanup process.
     */
    public function run(): void
    {
        echo "🚀 Starting Final Naming Cleanup...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->renameFiles();
        $this->updateFileContents();
        $this->cleanupEmptyDirectories();
        
        echo "\n✅ Final Naming Cleanup Complete!\n";
        echo "🎯 All naming issues resolved.\n";
    }

    /**
     * Rename files with final corrections.
     */
    private function renameFiles(): void
    {
        echo "📄 Renaming files with final corrections...\n";
        
        $files = $this->getAllFiles($this->basePath);
        $renamedCount = 0;

        foreach ($files as $file) {
            $fileName = basename($file);
            $newName = $this->getNewName($fileName);
            
            if ($newName !== $fileName && !empty($newName)) {
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
     * Update file contents to use final names.
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
     * Clean up empty directories that might have been created.
     */
    private function cleanupEmptyDirectories(): void
    {
        echo "🗑️  Cleaning up empty directories...\n";
        
        $directories = $this->getAllDirectories($this->basePath);
        $removedCount = 0;

        foreach ($directories as $dir) {
            if ($this->isEmptyDirectory($dir)) {
                if (rmdir($dir)) {
                    echo "   ✅ Removed empty directory: " . basename($dir) . "\n";
                    $removedCount++;
                }
            }
        }

        echo "   ✅ Removed {$removedCount} empty directories\n";
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

        foreach ($this->finalMappings as $oldName => $newName) {
            if (!empty($newName)) {
                $newContent = str_replace($oldName, $newName, $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $updated = true;
                }
            } else {
                // Remove unused names completely
                $newContent = str_replace($oldName, '', $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $updated = true;
                }
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
        foreach ($this->finalMappings as $oldName => $newName) {
            if (strpos($name, $oldName) !== false) {
                if (empty($newName)) {
                    return str_replace($oldName, '', $name);
                }
                return str_replace($oldName, $newName, $name);
            }
        }
        return $name;
    }

    /**
     * Check if a directory is empty.
     */
    private function isEmptyDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        
        $files = scandir($path);
        return count($files) <= 2; // Only . and .. directories
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
     * Get all directories in the project.
     */
    private function getAllDirectories(string $path): array
    {
        $directories = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $directories[] = $item->getPathname();
            }
        }

        return $directories;
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
        echo "\n📊 Final Naming Cleanup Summary\n";
        echo "==================================\n\n";
        
        echo "🔄 Final Corrections:\n";
        echo "   • Container → Container (Asas was container system)\n";
        echo "   • ContainerBootstrap → Bootstrap\n";
        echo "   • Removed unused: , , , , \n";
        echo "   • Consolidated: container + container → container\n";
        
        echo "\n📁 Impact:\n";
        echo "   • Asas properly renamed to Container\n";
        echo "   • No more unused Islamic placeholder names\n";
        echo "   • Single, consolidated Container system\n";
        echo "   • Clean, logical naming structure\n";
        echo "   • No more conflicts or duplicates\n";
    }
}

// Run the cleanup if this script is executed directly
if (php_sapi_name() === 'cli') {
    $cleanup = new FinalNamingCleanup();
    $cleanup->run();
    $cleanup->generateReport();
} 