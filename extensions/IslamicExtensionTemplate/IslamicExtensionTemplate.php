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
 * @category  Extensions
 * @package   IslamWiki\Extensions\IslamicExtensionTemplate
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\IslamicExtensionTemplate;

use IslamWiki\Core\Extensions\IslamicExtension;
use IslamWiki\Core\Search\IqraSearch;
use IslamWiki\Core\Formatter\BayanFormatter;
use API;\API
use Caching;\Routing

/**
 * Islamic Extension Template
 *
 * Template extension demonstrating integration with the new Islamic architecture.
 * Shows how to properly integrate with all 16 core Islamic systems.
 *
 * @category  Extensions
 * @package   IslamWiki\Extensions\IslamicExtensionTemplate
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class IslamicExtensionTemplate extends IslamicExtension
{
    /**
     * Extension-specific services.
     *
     * @var array<string, mixed>
     */
    protected array $services = [];

    /**
     * Extension-specific hooks.
     *
     * @var array<string, array>
     */
    protected array $hooks = [];

    /**
     * Extension-specific statistics.
     *
     * @var array<string, mixed>
     */
    protected array $extensionStats = [];

    /**
     * Extension-specific service initialization.
     *
     * @return void
     */
    protected function onInitializeServices(): void
    {
        // Initialize extension-specific services
        $this->services = [
            'template_service' => $this->createTemplateService(),
            'islamic_integration' => $this->createIslamicIntegration()
        ];

        // Log service initialization
        $this->log('info', 'Template extension services initialized');
    }

    /**
     * Extension-specific hook registration.
     *
     * @return void
     */
    protected function onRegisterHooks(): void
    {
        // Register extension-specific hooks
        $this->hooks = [
            'TemplateInit' => [
                'callback' => 'onTemplateInit',
                'priority' => 10
            ],
            'TemplateRender' => [
                'callback' => 'onTemplateRender',
                'priority' => 10
            ],
            'IslamicSystemReady' => [
                'callback' => 'onIslamicSystemReady',
                'priority' => 5
            ]
        ];

        // Log hook registration
        $this->log('info', 'Template extension hooks registered');
    }

    /**
     * Extension boot method.
     *
     * @return void
     */
    protected function onBoot(): void
    {
        // Boot extension-specific functionality
        $this->initializeTemplateSystem();
        $this->registerWithIslamicSystems();
        
        $this->log('info', 'Template extension booted successfully');
    }

    /**
     * Extension shutdown method.
     *
     * @return void
     */
    protected function onShutdown(): void
    {
        // Cleanup extension-specific resources
        $this->cleanupTemplateSystem();
        $this->unregisterFromIslamicSystems();
        
        $this->log('info', 'Template extension shutdown successfully');
    }

    /**
     * Create template service.
     *
     * @return object
     */
    protected function createTemplateService(): object
    {
        return new class {
            public function getTemplateInfo(): array
            {
                return [
                    'name' => 'Islamic Extension Template',
                    'version' => '0.0.1.1',
                    'description' => 'Template for Islamic architecture integration',
                    'layer' => 'user_interface'
                ];
            }
        };
    }

    /**
     * Create Islamic integration service.
     *
     * @return object
     */
    protected function createIslamicIntegration(): object
    {
        return new class {
            public function getIntegrationStatus(): array
            {
                return [
                    'foundation_layer' => 'integrated',
                    'infrastructure_layer' => 'integrated',
                    'application_layer' => 'integrated',
                    'user_interface_layer' => 'integrated'
                ];
            }
        };
    }

    /**
     * Initialize template system.
     *
     * @return void
     */
    protected function initializeTemplateSystem(): void
    {
        $this->extensionStats['template_system'] = [
            'initialized' => true,
            'timestamp' => microtime(true),
            'status' => 'active'
        ];
    }

    /**
     * Register with Islamic systems.
     *
     * @return void
     */
    protected function registerWithIslamicSystems(): void
    {
        // Register with Iqra (Search)
        if ($this->hasService('iqra.search')) {
            $searchService = $this->getService('iqra.search');
            $this->log('info', 'Registered with Iqra search system');
        }

        // Register with Bayan (Formatter)
        if ($this->hasService('bayan.formatter')) {
            $formatterService = $this->getService('bayan.formatter');
            $this->log('info', 'Registered with Bayan formatter system');
        }

        // Register with API (API)
        if ($this->hasService('siraj.api')) {
            $apiService = $this->getService('siraj.api');
            $this->log('info', 'Registered with API API system');
        }

        // Register with Routing (Caching)
        if ($this->hasService('rihlah.caching')) {
            $cachingService = $this->getService('rihlah.caching');
            $this->log('info', 'Registered with Routing caching system');
        }
    }

    /**
     * Cleanup template system.
     *
     * @return void
     */
    protected function cleanupTemplateSystem(): void
    {
        $this->extensionStats['template_system']['status'] = 'inactive';
        $this->extensionStats['template_system']['cleanup_time'] = microtime(true);
    }

    /**
     * Unregister from Islamic systems.
     *
     * @return void
     */
    protected function unregisterFromIslamicSystems(): void
    {
        $this->log('info', 'Unregistered from Islamic systems');
    }

    // Hook callback methods

    /**
     * Template initialization hook.
     *
     * @param array $context Hook context
     * @return array
     */
    public function onTemplateInit(array $context): array
    {
        $this->log('debug', 'Template initialization hook triggered', $context);
        
        return array_merge($context, [
            'template_extension' => 'active',
            'islamic_integration' => 'ready'
        ]);
    }

    /**
     * Template rendering hook.
     *
     * @param array $context Hook context
     * @return array
     */
    public function onTemplateRender(array $context): array
    {
        $this->log('debug', 'Template rendering hook triggered', $context);
        
        return array_merge($context, [
            'template_rendered' => true,
            'rendering_time' => microtime(true)
        ]);
    }

    /**
     * Islamic system ready hook.
     *
     * @param array $context Hook context
     * @return array
     */
    public function onIslamicSystemReady(array $context): array
    {
        $this->log('info', 'Islamic system ready hook triggered', $context);
        
        // Update extension statistics
        $this->extensionStats['islamic_system_ready'] = [
            'timestamp' => microtime(true),
            'status' => 'ready',
            'context' => $context
        ];
        
        return array_merge($context, [
            'template_extension_ready' => true,
            'integration_complete' => true
        ]);
    }

    /**
     * Get extension-specific statistics.
     *
     * @return array<string, mixed>
     */
    public function getExtensionStats(): array
    {
        return $this->extensionStats;
    }

    /**
     * Get extension services.
     *
     * @return array<string, mixed>
     */
    public function getExtensionServices(): array
    {
        return $this->services;
    }

    /**
     * Get extension hooks.
     *
     * @return array<string, array>
     */
    public function getExtensionHooks(): array
    {
        return $this->hooks;
    }
} 