<?php

/**
 * Fix and Consolidate Controllers Script
 * 
 * This script systematically fixes syntax errors in controllers and consolidates duplicates.
 * 
 * Version: 0.0.3.0
 * Usage: php scripts/fix-and-consolidate-controllers.php
 */

declare(strict_types=1);

class ControllerFixerAndConsolidator
{
    private string $basePath;
    private array $syntaxErrors = [];
    private array $workingControllers = [];
    private array $brokenControllers = [];
    private array $duplicateGroups = [];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: __DIR__ . '/..';
    }

    /**
     * Run the complete fix and consolidation process.
     */
    public function run(): void
    {
        echo "🔧 Starting Controller Fix and Consolidation Process...\n";
        echo "📁 Base path: {$this->basePath}\n\n";

        $this->scanControllers();
        $this->identifyDuplicates();
        $this->fixSyntaxErrors();
        $this->consolidateDuplicates();
        $this->generateReport();
    }

    /**
     * Scan all controllers for syntax errors and functionality.
     */
    private function scanControllers(): void
    {
        echo "1️⃣  Scanning Controllers...\n";
        
        $controllerDir = $this->basePath . '/src/Http/Controllers';
        $controllers = glob($controllerDir . '/*.php');
        $authControllers = glob($controllerDir . '/Auth/*.php');
        $apiControllers = glob($controllerDir . '/Api/*.php');
        
        $allControllers = array_merge($controllers, $authControllers, $apiControllers);
        
        foreach ($allControllers as $controllerPath) {
            $relativePath = str_replace($this->basePath . '/', '', $controllerPath);
            $controllerName = basename($controllerPath, '.php');
            
            echo "   📄 Checking {$relativePath}... ";
            
            // Check syntax
            $syntaxCheck = shell_exec("php -l {$controllerPath} 2>&1");
            if (strpos($syntaxCheck, 'No syntax errors detected') !== false) {
                echo "✅ Syntax OK\n";
                $this->workingControllers[] = $relativePath;
            } else {
                echo "❌ Syntax Error\n";
                $this->brokenControllers[] = $relativePath;
                $this->syntaxErrors[$relativePath] = $syntaxCheck;
            }
        }
        
        echo "\n   📊 Summary:\n";
        echo "      ✅ Working: " . count($this->workingControllers) . "\n";
        echo "      ❌ Broken: " . count($this->brokenControllers) . "\n";
    }

    /**
     * Identify potential duplicate controllers.
     */
    private function identifyDuplicates(): void
    {
        echo "\n2️⃣  Identifying Duplicates...\n";
        
        $this->duplicateGroups = [
            'Search' => [
                'SearchController.php',
                'SearchApiController.php', 
                'IqraSearchController.php'
            ],
            'Auth' => [
                'Auth/AuthController.php',
                'Auth/IslamicAuthController.php',
                'Auth/RegisterController.php',
                'Auth/ResetPasswordController.php',
                'Auth/ForgotPasswordController.php'
            ],
            'Islamic' => [
                'IslamicContentController.php',
                'IslamicCalendarController.php',
                'QuranController.php',
                'HadithController.php'
            ],
            'Settings' => [
                'SettingsController.php',
                'SkinSettingsController.php',
                'ConfigurationController.php'
            ]
        ];
        
        foreach ($this->duplicateGroups as $group => $controllers) {
            echo "   🔍 {$group} Group:\n";
            foreach ($controllers as $controller) {
                $status = in_array($controller, $this->workingControllers) ? '✅' : '❌';
                echo "      {$status} {$controller}\n";
            }
        }
    }

    /**
     * Fix syntax errors in broken controllers.
     */
    private function fixSyntaxErrors(): void
    {
        echo "\n3️⃣  Fixing Syntax Errors...\n";
        
        foreach ($this->brokenControllers as $controllerPath) {
            echo "   🔧 Fixing {$controllerPath}... ";
            
            try {
                $this->fixControllerSyntax($controllerPath);
                echo "✅ Fixed\n";
            } catch (\Exception $e) {
                echo "❌ Failed: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Fix syntax errors in a specific controller.
     */
    private function fixControllerSyntax(string $controllerPath): void
    {
        $fullPath = $this->basePath . '/' . $controllerPath;
        $content = file_get_contents($fullPath);
        
        // Common syntax fixes
        $fixes = [
            // Fix incomplete use statements
            '/use ([^;]+);\\\\([^;]+)/' => 'use $1;',
            '/use ([^;]+);\\\\([^;]+);/' => 'use $1;',
            
            // Fix incomplete class instantiation
            '/new ([^;]+)\\\\([^;]+)/' => 'new $1;',
            
            // Fix incomplete method calls
            '/->([^(]+)\\\\([^;]+)/' => '->$1;',
            
            // Fix incomplete property access
            '/\$([^;]+)\\\\([^;]+)/' => '$1;',
        ];
        
        foreach ($fixes as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Write fixed content back
        file_put_contents($fullPath, $content);
        
        // Verify fix
        $syntaxCheck = shell_exec("php -l {$fullPath} 2>&1");
        if (strpos($syntaxCheck, 'No syntax errors detected') === false) {
            throw new \Exception("Syntax still broken after fix");
        }
    }

    /**
     * Consolidate duplicate controllers.
     */
    private function consolidateDuplicates(): void
    {
        echo "\n4️⃣  Consolidating Duplicates...\n";
        
        foreach ($this->duplicateGroups as $group => $controllers) {
            echo "   🔄 Consolidating {$group} group...\n";
            
            // Find the best controller to keep (usually the one without syntax errors)
            $bestController = $this->findBestController($controllers);
            
            if ($bestController) {
                echo "      ✅ Keeping: {$bestController}\n";
                
                // Mark others for removal
                foreach ($controllers as $controller) {
                    if ($controller !== $bestController) {
                        echo "      🗑️  Marking for removal: {$controller}\n";
                        // Don't actually delete yet - just mark
                    }
                }
            } else {
                echo "      ❌ No working controller found in {$group} group\n";
            }
        }
    }

    /**
     * Find the best controller to keep from a group.
     */
    private function findBestController(array $controllers): ?string
    {
        // Prefer controllers without syntax errors
        foreach ($controllers as $controller) {
            if (in_array($controller, $this->workingControllers)) {
                return $controller;
            }
        }
        
        // If all have errors, return the first one (will be fixed)
        return $controllers[0] ?? null;
    }

    /**
     * Generate a comprehensive report.
     */
    private function generateReport(): void
    {
        echo "\n📊 Controller Fix and Consolidation Report\n";
        echo "==========================================\n\n";
        
        echo "✅ Working Controllers (" . count($this->workingControllers) . "):\n";
        foreach ($this->workingControllers as $controller) {
            echo "   • {$controller}\n";
        }
        
        echo "\n❌ Previously Broken Controllers (" . count($this->brokenControllers) . "):\n";
        foreach ($this->brokenControllers as $controller) {
            echo "   • {$controller}\n";
        }
        
        echo "\n🔄 Duplicate Groups Identified:\n";
        foreach ($this->duplicateGroups as $group => $controllers) {
            echo "   • {$group}: " . count($controllers) . " controllers\n";
        }
        
        echo "\n🎯 Next Steps:\n";
        echo "   1. Review the consolidated controllers\n";
        echo "   2. Update routes.php to use only the kept controllers\n";
        echo "   3. Remove the duplicate controllers\n";
        echo "   4. Test the web application\n";
    }
}

// Run the fixer if this script is executed directly
if (php_sapi_name() === 'cli') {
    $fixer = new ControllerFixerAndConsolidator();
    $fixer->run();
} 