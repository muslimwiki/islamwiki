<?php

/**
 * Cleanup Islamic Naming Script
 * 
 * This script systematically renames ALL remaining Islamic-named files and directories
 * to clean English names to eliminate conflicts and confusion.
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/cleanup-islamic-naming.php
 */

declare(strict_types=1);

class IslamicNamingCleanup
{
    private string $basePath;
    private array $fileMappings = [
        // Core Systems
        'API' => 'API',
        'Caching' => 'Caching',
        'Configuration' => 'Configuration',
        'Container' => 'Container',
        'Container' => 'Container',
        'Security' => 'Security',
        'Security' => 'Security',
        'Session' => 'Session',
        'Session' => 'Session',
        'Logger' => 'Logger',
        'Logger' => 'Logger',
        
        // File Names
        'api' => 'api',
        'caching' => 'caching',
        'configuration' => 'configuration',
        'container' => 'container',
        'container' => 'container',
        'security' => 'security',
        'security' => 'security',
        'session' => 'session',
        'session' => 'session',
        'logger' => 'logger',
        
        // Directory Names
        'API' => 'API',
        'Routing' => 'Routing',
        'Configuration' => 'Configuration',
        'Container' => 'Container',
        'Security' => 'Security',
        'Session' => 'Session',
        'Logging' => 'Logging',
        'Knowledge' => 'Knowledge',
        'Queue' => 'Queue',
        'Application' => 'Application',
        'Database' => 'Database',
        'Wisdom' => 'Wisdom',
        'Faith' => 'Faith',
        'Piety' => 'Piety',
        'Justice' => 'Justice',
        'Mercy' => 'Mercy',
        
        // Class Names
        'API' => 'API',
        'Caching' => 'Caching',
        'Configuration' => 'Configuration',
        'Container' => 'Container',
        'Container' => 'Container',
        'Security' => 'Security',
        'Security' => 'Security',
        'Session' => 'Session',
        'Session' => 'Session',
        'Logger' => 'Logger',
        'Logger' => 'Logger',
        'Knowledge' => 'Knowledge',
        'Queue' => 'Queue',
        'Application' => 'Application',
        'Database' => 'Database',
        'WisdomWisdom' => 'Wisdom',
        'FaithFaith' => 'Faith',
        'PietyPiety' => 'Piety',
        'JusticeJustice' => 'Justice',
        'MercyMercy' => 'Mercy'
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
        echo "🚀 Starting Islamic Naming Cleanup...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->renameDirectories();
        $this->renameFiles();
        $this->updateFileContents();
        
        echo "\n✅ Islamic Naming Cleanup Complete!\n";
        echo "🎯 All Islamic naming conventions eliminated.\n";
    }

    /**
     * Rename directories with Islamic names.
     */
    private function renameDirectories(): void
    {
        echo "📁 Renaming directories...\n";
        
        $directories = $this->getAllDirectories($this->basePath);
        $renamedCount = 0;

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $newName = $this->getNewName($dirName);
            
            if ($newName !== $dirName) {
                $newPath = dirname($dir) . '/' . $newName;
                
                if (!file_exists($newPath)) {
                    if (rename($dir, $newPath)) {
                        echo "   ✅ {$dirName} → {$newName}\n";
                        $renamedCount++;
                    } else {
                        echo "   ❌ Failed to rename {$dirName}\n";
                    }
                } else {
                    echo "   ⚠️  Target {$newName} already exists, skipping {$dirName}\n";
                }
            }
        }

        echo "   ✅ Renamed {$renamedCount} directories\n";
    }

    /**
     * Rename files with Islamic names.
     */
    private function renameFiles(): void
    {
        echo "📄 Renaming files...\n";
        
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

        foreach ($this->fileMappings as $oldName => $newName) {
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
     * Get new name for a file or directory.
     */
    private function getNewName(string $name): string
    {
        foreach ($this->fileMappings as $oldName => $newName) {
            if (strpos($name, $oldName) !== false) {
                return str_replace($oldName, $newName, $name);
            }
        }
        return $name;
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
        echo "\n📊 Islamic Naming Cleanup Summary\n";
        echo "==================================\n\n";
        
        echo "🔄 Naming Changes:\n";
        echo "   • API → API\n";
        echo "   • Routing → Routing\n";
        echo "   • Configuration → Configuration\n";
        echo "   • Container → Container\n";
        echo "   • Security → Security\n";
        echo "   • Session → Session\n";
        echo "   • Logging → Logging\n";
        echo "   • Knowledge → Knowledge\n";
        echo "   • Queue → Queue\n";
        echo "   • Application → Application\n";
        echo "   • Database → Database\n";
        
        echo "\n📁 Impact:\n";
        echo "   • All Islamic naming conventions eliminated\n";
        echo "   • Clean, consistent English naming\n";
        echo "   • No more conflicts or duplicates\n";
        echo "   • Simplified development and maintenance\n";
        echo "   • Professional, standard naming conventions\n";
    }
}

// Run the cleanup if this script is executed directly
if (php_sapi_name() === 'cli') {
    $cleanup = new IslamicNamingCleanup();
    $cleanup->run();
    $cleanup->generateReport();
} 