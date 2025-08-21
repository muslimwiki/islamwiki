<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Container\AsasContainer;

/**
 * Wiki Markup Service Provider
 * 
 * @package IslamWiki\Extensions\WikiMarkupExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiMarkupServiceProvider
{
    /**
     * Register services with the container
     */
    public function register(AsasContainer $container): void
    {
        // Register the parser
        $container->set(WikiMarkupParser::class, function () {
            return new WikiMarkupParser([
                'enable_wiki_markup' => true,
                'parse_internal_links' => true,
                'parse_templates' => true,
                'parse_headers' => true,
                'parse_lists' => true,
                'enable_caching' => true,
                'cache_ttl' => 3600
            ]);
        });

        // Register the extension
        $container->set(WikiMarkupExtension::class, function (AsasContainer $container) {
            return new WikiMarkupExtension($container);
        });
    }
} 