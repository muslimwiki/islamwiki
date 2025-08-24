<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core;

use Container;\Container
use Logger;\Logger
use IslamWiki\Core\Configuration\Configuration;
use Database;\Database
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Routing\RouteImplementationService;
use IslamWiki\Core\Extensions\IslamicExtensionManager;
use Application;\Application
use Exception;

/**
 * ContainerBootstrap (أساس) - Application Bootstrap System
 *
 * Container provides "Container" in Arabic. This is the bootstrap system that
 * initializes all Islamic systems and prepares the application for operation.
 *
 * @category  Core
 * @package   IslamWiki\Core
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class ContainerBootstrap
{
    /**
     * Application container.
     */
    protected Container $container;

    /**
     * Bootstrap status.
     */
    protected bool $bootstrapped = false;

    /**
     * Bootstrap start time.
     */
    protected float $startTime;

    /**
     * Constructor.
     *
     * @param Container $container Application container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->startTime = microtime(true);
    }

    /**
     * Bootstrap the application.
     *
     * @return bool
     */
    public function bootstrap(): bool
    {
        try {
            // echo "🚀 Starting IslamWiki Islamic Architecture Bootstrap...\n\n";

            // Step 1: Initialize Container Layer
            $this->initializeContainerLayer();
            // echo "✅ Container Layer initialized\n";

            // Step 2: Initialize Infrastructure Layer
            $this->initializeInfrastructureLayer();
            // echo "✅ Infrastructure Layer initialized\n";

            // Step 3: Initialize Application Layer
            $this->initializeApplicationLayer();
            // echo "✅ Application Layer initialized\n";

            // Step 4: Initialize User Interface Layer
            $this->initializeUserInterfaceLayer();
            // echo "✅ User Interface Layer initialized\n";

            // Step 5: Initialize Extensions
            $this->initializeExtensions();
            // echo "✅ Extensions initialized\n";

            // Step 6: Implement Routes
            $this->implementRoutes();
            // echo "✅ Routes implemented\n";

            // Step 7: Finalize Bootstrap
            $this->finalizeBootstrap();
            // echo "✅ Bootstrap finalized\n";

            $this->bootstrapped = true;
            $bootstrapTime = microtime(true) - $this->startTime;
            
            // echo "\n🎉 IslamWiki Islamic Architecture Bootstrap Complete!\n";
            // echo "⏱️  Bootstrap time: " . number_format($bootstrapTime * 1000, 2) . "ms\n";
            // echo "🏗️  All 16 Islamic systems are now operational\n\n";

            return true;

        } catch (Exception $e) {
            // echo "❌ Bootstrap failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Initialize Container Layer (أساس).
     *
     * @return void
     */
    protected function initializeContainerLayer(): void
    {
        // echo "🏗️  Initializing Container Layer (أساس)...\n";

        $basePath = __DIR__ . '/../..';
        
        // Register base path service
        $this->container->set('base_path', $basePath);
        // echo "   ✅ Base path service registered\n";

        // Initialize Logger with proper directory
        $logDir = $basePath . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logger = new LoggerLogger($logDir);
        $this->container->set('logger', $logger);
        
        // Initialize Configuration
        $config = new Configuration($this->container);
        $this->container->set('tadbir.config', $config);
        $this->container->set('config', $config);
        $this->container->set('IslamWiki\Core\Configuration\Configuration', $config);

        // echo "   ✅ Logging Logger initialized\n";
        // echo "   ✅ Configuration Configuration initialized\n";
    }

    /**
     * Initialize Infrastructure Layer (سبيل, نظام, ميزان, تدبير).
     *
     * @return void
     */
    protected function initializeInfrastructureLayer(): void
    {
        // echo "🏗️  Initializing Infrastructure Layer...\n";

        $logger = $this->container->get('logger');

        // Initialize Database (Balance/Database)
        $database = new Database($logger, []);
        $this->container->set('mizan.database', $database);
        $this->container->set('database', $database);

        // Initialize SabilRouting (Path/Routing)
        $routing = new SabilRouting($this->container, $logger);
        $this->container->set('sabil.routing', $routing);
        $this->container->set('routing', $routing);
        $this->container->set('IslamWiki\Core\Routing\SabilRouting', $routing);

        // Set the application instance in the container for ViewServiceProvider
        $this->container->set('app', $this);

        // Initialize Application (Order/Application)
        $basePath = __DIR__ . '/../../';
        $nizam = new Application($basePath, $this->container);
        $this->container->set('nizam.application', $nizam);
        $this->container->set('application', $nizam);

        // echo "   ✅ Database (Database) initialized\n";
        // echo "   ✅ SabilRouting (Routing) initialized\n";
        // echo "   ✅ Application (Application) initialized\n";
    }

    /**
     * Initialize Application Layer (أمان, وصل, صبر, أصول).
     *
     * @return void
     */
    protected function initializeApplicationLayer(): void
    {
        // echo "🏗️  Initializing Application Layer...\n";

        $logger = $this->container->get('logger');

        // Note: These classes need to be created or imported
        // For now, we'll create placeholder instances
        $this->container->set('aman.security', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('wisal.session', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('sabr.queue', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('usul.knowledge', new class($logger) {
            public function __construct($logger) {}
        });

        // echo "   ✅ Security (Security) initialized\n";
        // echo "   ✅ Session (Session) initialized\n";
        // echo "   ✅ Queue (Queue) initialized\n";
        // echo "   ✅ Knowledge (Knowledge) initialized\n";
    }

    /**
     * Initialize User Interface Layer (إقرأ, بيان, سراج, رحلة).
     *
     * @return void
     */
    protected function initializeUserInterfaceLayer(): void
    {
        // echo "🏗️  Initializing User Interface Layer...\n";

        $logger = $this->container->get('logger');

        // Note: These classes need to be created or imported
        // For now, we'll create placeholder instances
        $this->container->set('iqra.search', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('bayan.formatter', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('siraj.api', new class($logger) {
            public function __construct($logger) {}
        });
        $this->container->set('rihlah.caching', new class($logger) {
            public function __construct($logger) {}
        });

        // echo "   ✅ IqraSearch (Search) initialized\n";
        // echo "   ✅ BayanFormatter (Formatter) initialized\n";
        // echo "   ✅ API (API) initialized\n";
        // echo "   ✅ Caching (Caching) initialized\n";
    }

    /**
     * Initialize Extensions.
     *
     * @return void
     */
    protected function initializeExtensions(): void
    {
        // echo "🏗️  Initializing Extensions...\n";

        $logger = $this->container->get('logger');

        // Initialize HookManager first (required by some extensions)
        $hookManager = new \IslamWiki\Core\Extensions\Hooks\HookManager();
        $this->container->set('IslamWiki\Core\Extensions\Hooks\HookManager', $hookManager);
        // echo "   ✅ HookManager service registered\n";

        // Use the correct ExtensionManager (not IslamicExtensionManager)
        $extensionManager = new \IslamWiki\Core\Extensions\ExtensionManager($this->container);
        $this->container->set('extension.manager', $extensionManager);

        // Register additional service names for compatibility
        $config = $this->container->get('config');
        $this->container->set('IslamWiki\Core\Configuration\Configuration', $config);
        $this->container->set('IslamWiki\Core\Logging\Logger $logger);

        // Now that the ExtensionManager is available, load extensions from configuration
        if (method_exists($config, 'loadExtensionsWhenReady')) {
            $config->loadExtensionsWhenReady();
        }

        // echo "   ✅ Extension Manager initialized\n";
        // echo "   ✅ Extensions discovered and loaded\n";
    }

    /**
     * Implement all routes.
     *
     * @return void
     */
    protected function implementRoutes(): void
    {
        // echo "🏗️  Implementing Routes...\n";

        // Temporarily skip route implementation to get basic functionality working
        // echo "   ⏭️  Route implementation skipped for now\n";
        
        // TODO: Fix route implementation service
        // Initialize RouteImplementationService
        // $routeService = new RouteImplementationService($this->container);
        // $this->container->set('route.service', $routeService);

        // Implement all routes
        // $routeService->implementAllRoutes();

        // echo "   ✅ Route Implementation Service initialized\n";
        // echo "   ✅ All Islamic routes implemented\n";
    }

    /**
     * Finalize bootstrap process.
     *
     * @return void
     */
    protected function finalizeBootstrap(): void
    {
        // echo "🏗️  Finalizing Bootstrap...\n";

        // Boot extensions
        $extensionManager = $this->container->get('extension.manager');
        $extensionManager->bootExtensions();

        // Set application as ready
        $this->container->set('app.ready', true);

        // echo "   ✅ Extensions booted\n";
        // echo "   ✅ Application marked as ready\n";
    }

    /**
     * Check if application is bootstrapped.
     *
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }

    /**
     * Get bootstrap statistics.
     *
     * @return array<string, mixed>
     */
    public function getBootstrapStats(): array
    {
        return [
            'bootstrapped' => $this->bootstrapped,
            'start_time' => $this->startTime,
            'bootstrap_time' => $this->bootstrapped ? microtime(true) - $this->startTime : 0,
            'container_services' => $this->container->keys()
        ];
    }

    /**
     * Get container instance.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
} 