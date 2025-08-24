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
 * @category  Configuration
 * @package   IslamWiki\Config
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

/**
 * Islamic Architecture Route Configuration
 *
 * This file defines all routes organized by Islamic systems and layers.
 * Routes are grouped by functionality and follow Islamic naming conventions.
 *
 * @category  Configuration
 * @package   IslamWiki\Config
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

return [
    // Container Layer (أساس) - Core system routes
    'asas' => [
        'name' => 'Container Layer',
        'description' => 'Core system and foundation routes',
        'prefix' => '/asas',
        'middleware' => ['asas.foundation'],
        'routes' => [
            'GET' => [
                '/health' => [
                    'controller' => 'ContainerHealthController',
                    'action' => 'check',
                    'name' => 'asas.health',
                    'description' => 'System health check'
                ],
                '/status' => [
                    'controller' => 'ContainerStatusController',
                    'action' => 'status',
                    'name' => 'asas.status',
                    'description' => 'System status overview'
                ],
                '/bootstrap' => [
                    'controller' => 'ContainerBootstrapController',
                    'action' => 'bootstrap',
                    'name' => 'asas.bootstrap',
                    'description' => 'Application bootstrap status'
                ]
            ]
        ]
    ],

    // Infrastructure Layer (سبيل, نظام, ميزان, تدبير)
    'sabil' => [
        'name' => 'Routing System',
        'description' => 'Route management and navigation',
        'prefix' => '/sabil',
        'middleware' => ['sabil.routing'],
        'routes' => [
            'GET' => [
                '/routes' => [
                    'controller' => 'SabilRouteController',
                    'action' => 'list',
                    'name' => 'sabil.routes.list',
                    'description' => 'List all registered routes'
                ],
                '/routes/{group}' => [
                    'controller' => 'SabilRouteController',
                    'action' => 'group',
                    'name' => 'sabil.routes.group',
                    'description' => 'List routes by group'
                ]
            ],
            'POST' => [
                '/routes/cache/clear' => [
                    'controller' => 'SabilRouteController',
                    'action' => 'clearCache',
                    'name' => 'sabil.routes.clear-cache',
                    'description' => 'Clear route cache'
                ]
            ]
        ]
    ],

    'nizam' => [
        'name' => 'System Management',
        'description' => 'System orchestration and management',
        'prefix' => '/nizam',
        'middleware' => ['nizam.system'],
        'routes' => [
            'GET' => [
                '/systems' => [
                    'controller' => 'ApplicationSystemController',
                    'action' => 'list',
                    'name' => 'nizam.systems.list',
                    'description' => 'List all Islamic systems'
                ],
                '/systems/{system}' => [
                    'controller' => 'ApplicationSystemController',
                    'action' => 'status',
                    'name' => 'nizam.systems.status',
                    'description' => 'Get system status'
                ],
                '/metrics' => [
                    'controller' => 'ApplicationSystemController',
                    'action' => 'metrics',
                    'name' => 'nizam.systems.metrics',
                    'description' => 'Get system metrics'
                ]
            ],
            'POST' => [
                '/systems/{system}/restart' => [
                    'controller' => 'ApplicationSystemController',
                    'action' => 'restart',
                    'name' => 'nizam.systems.restart',
                    'description' => 'Restart a system'
                ]
            ]
        ]
    ],

    'mizan' => [
        'name' => 'Database Management',
        'description' => 'Database operations and management',
        'prefix' => '/mizan',
        'middleware' => ['mizan.database'],
        'routes' => [
            'GET' => [
                '/status' => [
                    'controller' => 'DatabaseController',
                    'action' => 'status',
                    'name' => 'mizan.database.status',
                    'description' => 'Database connection status'
                ],
                '/tables' => [
                    'controller' => 'DatabaseController',
                    'action' => 'tables',
                    'name' => 'mizan.database.tables',
                    'description' => 'List database tables'
                ],
                '/queries' => [
                    'controller' => 'DatabaseController',
                    'action' => 'queries',
                    'name' => 'mizan.database.queries',
                    'description' => 'Get query statistics'
                ]
            ],
            'POST' => [
                '/migrate' => [
                    'controller' => 'DatabaseController',
                    'action' => 'migrate',
                    'name' => 'mizan.database.migrate',
                    'description' => 'Run database migrations'
                ]
            ]
        ]
    ],

    'tadbir' => [
        'name' => 'Configuration Management',
        'description' => 'System configuration and settings',
        'prefix' => '/tadbir',
        'middleware' => ['tadbir.config'],
        'routes' => [
            'GET' => [
                '/config' => [
                    'controller' => 'ConfigurationConfigController',
                    'action' => 'list',
                    'name' => 'tadbir.config.list',
                    'description' => 'List all configurations'
                ],
                '/config/{key}' => [
                    'controller' => 'ConfigurationConfigController',
                    'action' => 'get',
                    'name' => 'tadbir.config.get',
                    'description' => 'Get configuration value'
                ]
            ],
            'POST' => [
                '/config' => [
                    'controller' => 'ConfigurationConfigController',
                    'action' => 'set',
                    'name' => 'tadbir.config.set',
                    'description' => 'Set configuration value'
                ]
            ],
            'PUT' => [
                '/config/{key}' => [
                    'controller' => 'ConfigurationConfigController',
                    'action' => 'update',
                    'name' => 'tadbir.config.update',
                    'description' => 'Update configuration value'
                ]
            ]
        ]
    ],

    // Application Layer (أمان, وصل, صبر, أصول)
    'aman' => [
        'name' => 'Security System',
        'description' => 'Authentication, authorization, and security',
        'prefix' => '/aman',
        'middleware' => ['aman.security'],
        'routes' => [
            'GET' => [
                '/login' => [
                    'controller' => 'SecurityAuthController',
                    'action' => 'showLogin',
                    'name' => 'aman.auth.login',
                    'description' => 'Show login form'
                ],
                '/logout' => [
                    'controller' => 'SecurityAuthController',
                    'action' => 'logout',
                    'name' => 'aman.auth.logout',
                    'description' => 'User logout'
                ],
                '/profile' => [
                    'controller' => 'SecurityUserController',
                    'action' => 'profile',
                    'name' => 'aman.user.profile',
                    'description' => 'User profile'
                ],
                '/users' => [
                    'controller' => 'SecurityUserController',
                    'action' => 'list',
                    'name' => 'aman.users.list',
                    'description' => 'List users'
                ]
            ],
            'POST' => [
                '/login' => [
                    'controller' => 'SecurityAuthController',
                    'action' => 'login',
                    'name' => 'aman.auth.authenticate',
                    'description' => 'Authenticate user'
                ],
                '/register' => [
                    'controller' => 'SecurityAuthController',
                    'action' => 'register',
                    'name' => 'aman.auth.register',
                    'description' => 'User registration'
                ]
            ]
        ]
    ],

    'wisal' => [
        'name' => 'Session Management',
        'description' => 'User session and connection management',
        'prefix' => '/wisal',
        'middleware' => ['wisal.session'],
        'routes' => [
            'GET' => [
                '/sessions' => [
                    'controller' => 'SessionController',
                    'action' => 'list',
                    'name' => 'wisal.sessions.list',
                    'description' => 'List active sessions'
                ],
                '/sessions/{id}' => [
                    'controller' => 'SessionController',
                    'action' => 'show',
                    'name' => 'wisal.sessions.show',
                    'description' => 'Show session details'
                ]
            ],
            'POST' => [
                '/sessions/refresh' => [
                    'controller' => 'SessionController',
                    'action' => 'refresh',
                    'name' => 'wisal.sessions.refresh',
                    'description' => 'Refresh session'
                ]
            ],
            'DELETE' => [
                '/sessions/{id}' => [
                    'controller' => 'SessionController',
                    'action' => 'destroy',
                    'name' => 'wisal.sessions.destroy',
                    'description' => 'Destroy session'
                ]
            ]
        ]
    ],

    'sabr' => [
        'name' => 'Queue Management',
        'description' => 'Background job processing and queues',
        'prefix' => '/sabr',
        'middleware' => ['sabr.queue'],
        'routes' => [
            'GET' => [
                '/jobs' => [
                    'controller' => 'QueueController',
                    'action' => 'list',
                    'name' => 'sabr.jobs.list',
                    'description' => 'List queued jobs'
                ],
                '/jobs/{id}' => [
                    'controller' => 'QueueController',
                    'action' => 'show',
                    'name' => 'sabr.jobs.show',
                    'description' => 'Show job details'
                ],
                '/queues' => [
                    'controller' => 'QueueController',
                    'action' => 'queues',
                    'name' => 'sabr.queues.list',
                    'description' => 'List all queues'
                ]
            ],
            'POST' => [
                '/jobs' => [
                    'controller' => 'QueueController',
                    'action' => 'dispatch',
                    'name' => 'sabr.jobs.dispatch',
                    'description' => 'Dispatch new job'
                ]
            ]
        ]
    ],

    'usul' => [
        'name' => 'Knowledge Management',
        'description' => 'Business rules and knowledge base',
        'prefix' => '/usul',
        'middleware' => ['usul.knowledge'],
        'routes' => [
            'GET' => [
                '/rules' => [
                    'controller' => 'KnowledgeController',
                    'action' => 'list',
                    'name' => 'usul.rules.list',
                    'description' => 'List business rules'
                ],
                '/rules/{id}' => [
                    'controller' => 'KnowledgeController',
                    'action' => 'show',
                    'name' => 'usul.rules.show',
                    'description' => 'Show business rule'
                ],
                '/validation' => [
                    'controller' => 'KnowledgeController',
                    'action' => 'validate',
                    'name' => 'usul.validation.validate',
                    'description' => 'Validate data against rules'
                ]
            ]
        ]
    ],

    // User Interface Layer (إقرأ, بيان, سراج, رحلة)
    'iqra' => [
        'name' => 'Search System',
        'description' => 'Content search and discovery',
        'prefix' => '/iqra',
        'middleware' => ['iqra.search'],
        'routes' => [
            'GET' => [
                '/search' => [
                    'controller' => 'IqraSearchController',
                    'action' => 'search',
                    'name' => 'iqra.search.query',
                    'description' => 'Perform search query'
                ],
                '/suggestions' => [
                    'controller' => 'IqraSearchController',
                    'action' => 'suggestions',
                    'name' => 'iqra.search.suggestions',
                    'description' => 'Get search suggestions'
                ],
                '/filters' => [
                    'controller' => 'IqraSearchController',
                    'action' => 'filters',
                    'name' => 'iqra.search.filters',
                    'description' => 'Get available search filters'
                ]
            ]
        ]
    ],

    'bayan' => [
        'name' => 'Content Formatting',
        'description' => 'Content presentation and formatting',
        'prefix' => '/bayan',
        'middleware' => ['bayan.formatter'],
        'routes' => [
            'GET' => [
                '/format' => [
                    'controller' => 'BayanFormatterController',
                    'action' => 'format',
                    'name' => 'bayan.format.content',
                    'description' => 'Format content'
                ],
                '/templates' => [
                    'controller' => 'BayanFormatterController',
                    'action' => 'templates',
                    'name' => 'bayan.templates.list',
                    'description' => 'List available templates'
                ]
            ],
            'POST' => [
                '/format' => [
                    'controller' => 'BayanFormatterController',
                    'action' => 'formatPost',
                    'name' => 'bayan.format.post',
                    'description' => 'Format content via POST'
                ]
            ]
        ]
    ],

    'siraj' => [
        'name' => 'API Management',
        'description' => 'RESTful API endpoints and management',
        'prefix' => '/api',
        'middleware' => ['siraj.api'],
        'routes' => [
            'GET' => [
                '/docs' => [
                    'controller' => 'APIApiController',
                    'action' => 'documentation',
                    'name' => 'siraj.api.docs',
                    'description' => 'API documentation'
                ],
                '/endpoints' => [
                    'controller' => 'APIApiController',
                    'action' => 'endpoints',
                    'name' => 'siraj.api.endpoints',
                    'description' => 'List API endpoints'
                ],
                '/stats' => [
                    'controller' => 'APIApiController',
                    'action' => 'statistics',
                    'name' => 'siraj.api.stats',
                    'description' => 'API usage statistics'
                ]
            ]
        ]
    ],

    'rihlah' => [
        'name' => 'Caching System',
        'description' => 'Cache management and optimization',
        'prefix' => '/rihlah',
        'middleware' => ['rihlah.caching'],
        'routes' => [
            'GET' => [
                '/cache/status' => [
                    'controller' => 'RoutingCacheController',
                    'action' => 'status',
                    'name' => 'rihlah.cache.status',
                    'description' => 'Cache system status'
                ],
                '/cache/stats' => [
                    'controller' => 'RoutingCacheController',
                    'action' => 'statistics',
                    'name' => 'rihlah.cache.stats',
                    'description' => 'Cache statistics'
                ]
            ],
            'POST' => [
                '/cache/clear' => [
                    'controller' => 'RoutingCacheController',
                    'action' => 'clear',
                    'name' => 'rihlah.cache.clear',
                    'description' => 'Clear all caches'
                ],
                '/cache/clear/{store}' => [
                    'controller' => 'RoutingCacheController',
                    'action' => 'clearStore',
                    'name' => 'rihlah.cache.clear-store',
                    'description' => 'Clear specific cache store'
                ]
            ]
        ]
    ],

    // Content Routes (Quran, Hadith, etc.)
    'quran' => [
        'name' => 'Quran Management',
        'description' => 'Quran text, translations, and commentary',
        'prefix' => '/quran',
        'middleware' => ['quran.access'],
        'routes' => [
            'GET' => [
                '/' => [
                    'controller' => 'QuranController',
                    'action' => 'index',
                    'name' => 'quran.index',
                    'description' => 'Quran homepage'
                ],
                '/{surah}' => [
                    'controller' => 'QuranController',
                    'action' => 'surah',
                    'name' => 'quran.surah',
                    'description' => 'Show surah'
                ],
                '/{surah}/{ayah}' => [
                    'controller' => 'QuranController',
                    'action' => 'ayah',
                    'name' => 'quran.ayah',
                    'description' => 'Show specific ayah'
                ],
                '/search' => [
                    'controller' => 'QuranController',
                    'action' => 'search',
                    'name' => 'quran.search',
                    'description' => 'Search Quran text'
                ]
            ]
        ]
    ],

    'hadith' => [
        'name' => 'Hadith Management',
        'description' => 'Hadith collections and authentication',
        'prefix' => '/hadith',
        'middleware' => ['hadith.access'],
        'routes' => [
            'GET' => [
                '/' => [
                    'controller' => 'HadithController',
                    'action' => 'index',
                    'name' => 'hadith.index',
                    'description' => 'Hadith homepage'
                ],
                '/{collection}' => [
                    'controller' => 'HadithController',
                    'action' => 'collection',
                    'name' => 'hadith.collection',
                    'description' => 'Show hadith collection'
                ],
                '/{collection}/{id}' => [
                    'controller' => 'HadithController',
                    'action' => 'show',
                    'name' => 'hadith.show',
                    'description' => 'Show specific hadith'
                ],
                '/search' => [
                    'controller' => 'HadithController',
                    'action' => 'search',
                    'name' => 'hadith.search',
                    'description' => 'Search hadith'
                ]
            ]
        ]
    ],

    // User Interface Routes
    'dashboard' => [
        'name' => 'User Dashboard',
        'description' => 'User dashboard and management',
        'prefix' => '/dashboard',
        'middleware' => ['auth', 'dashboard.access'],
        'routes' => [
            'GET' => [
                '/' => [
                    'controller' => 'DashboardController',
                    'action' => 'index',
                    'name' => 'dashboard.index',
                    'description' => 'User dashboard'
                ],
                '/profile' => [
                    'controller' => 'DashboardController',
                    'action' => 'profile',
                    'name' => 'dashboard.profile',
                    'description' => 'User profile management'
                ],
                '/settings' => [
                    'controller' => 'DashboardController',
                    'action' => 'settings',
                    'name' => 'dashboard.settings',
                    'description' => 'User settings'
                ]
            ]
        ]
    ],

    // Public Routes
    'public' => [
        'name' => 'Public Pages',
        'description' => 'Public-facing pages and content',
        'prefix' => '',
        'middleware' => ['public.access'],
        'routes' => [
            'GET' => [
                '/' => [
                    'controller' => 'PublicController',
                    'action' => 'home',
                    'name' => 'public.home',
                    'description' => 'Homepage'
                ],
                '/about' => [
                    'controller' => 'PublicController',
                    'action' => 'about',
                    'name' => 'public.about',
                    'description' => 'About page'
                ],
                '/contact' => [
                    'controller' => 'PublicController',
                    'action' => 'contact',
                    'name' => 'public.contact',
                    'description' => 'Contact page'
                ]
            ]
        ]
    ]
]; 