<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\MarkdownDocsViewer;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;

class MarkdownDocsViewer extends Extension
{
    protected function onInitialize(): void
    {
        $this->registerHooks();
    }

    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();
        // Provide nav link injection for templates that ask for it
        $hookManager->register('GetNavLinks', [$this, 'onGetNavLinks'], 10);
        // Provide view globals injection to expose extension_nav_links
        $hookManager->register('ComposeViewGlobals', [$this, 'onComposeViewGlobals'], 10);
    }

    public function onGetNavLinks(array $links = []): array
    {
        // Add Docs link
        $links[] = [ 'href' => '/docs', 'label' => 'Docs' ];
        return $links;
    }

    public function onComposeViewGlobals(array $globals = []): array
    {
        // Merge nav links so templates can render them
        $existing = $globals['extension_nav_links'] ?? [];
        $navLinks = $this->getHookManager()->runLast('GetNavLinks', [$existing]);
        if ($navLinks === null) {
            $navLinks = $existing;
            $results = $this->getHookManager()->run('GetNavLinks', [$existing]);
            foreach ($results as $res) {
                if (is_array($res)) {
                    $navLinks = array_merge($navLinks, $res);
                }
            }
        }
        $globals['extension_nav_links'] = $navLinks;
        return $globals;
    }
}
