<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Wiki\NamespaceManager;

class SpecialController extends Controller
{
    public function index(Request $request): Response
    {
        // Default Special:SpecialPages
        return $this->specialPages($request);
    }

    // Route handler for /special/{page}
    public function handle(Request $request, string $specialPage): Response
    {
        $map = [
            'SpecialPages' => 'specialPages',
            'AllPages' => 'allPages',
            'PrefixIndex' => 'prefixIndex',
            'Categories' => 'categories',
            'CategoryTree' => 'categoryTree',
            'DisambiguationPages' => 'disambiguationPages',
            'LinkSearch' => 'linkSearch',
            'ListRedirects' => 'listRedirects',
            'DisambiguationPageLinks' => 'disambiguationPageLinks',
            'PagesWithProp' => 'pagesWithProp',
            'Search' => 'specialSearch',
            'TrackingCategories' => 'trackingCategories',
            // Maintenance reports
            'BrokenRedirects' => 'brokenRedirects',
            'DeadendPages' => 'deadendPages',
            'DoubleRedirects' => 'doubleRedirects',
            'LintErrors' => 'lintErrors',
            'LongPages' => 'longPages',
            'OldestPages' => 'oldestPages',
            'OrphanedPages' => 'orphanedPages',
            'FewestRevisions' => 'fewestRevisions',
            'WithoutLanguageLinks' => 'withoutLanguageLinks',
            'ProtectedPages' => 'protectedPages',
            'ProtectedTitles' => 'protectedTitles',
            'ShortPages' => 'shortPages',
            'UncategorizedCategories' => 'uncategorizedCategories',
            'UncategorizedFiles' => 'uncategorizedFiles',
            'UncategorizedPages' => 'uncategorizedPages',
            'UncategorizedTemplates' => 'uncategorizedTemplates',
            'UnusedCategories' => 'unusedCategories',
            'UnusedFiles' => 'unusedFiles',
            'UnusedTemplates' => 'unusedTemplates',
            'WantedCategories' => 'wantedCategories',
            'WantedFiles' => 'wantedFiles',
            'WantedPages' => 'wantedPages',
            'WantedTemplates' => 'wantedTemplates',
        ];
        $key = preg_replace('/[^A-Za-z0-9]+/', '', $specialPage);
        $method = $map[$key] ?? null;
        if ($method && method_exists($this, $method)) {
            return $this->{$method}($request);
        }
        return new Response(404, ['Content-Type' => 'text/html'], '<h1>Special page not found</h1>');
    }

    public function specialPages(Request $request): Response
    {
        $namespaces = NamespaceManager::listNamespaces();
        return $this->view('special/index', [
            'title' => 'Special:SpecialPages',
            'namespaces' => $namespaces,
        ]);
    }

    public function allPages(Request $request): Response
    {
        // Optional namespace filter (e.g., ?namespace=Quran or ?namespace=Main)
        $nsFilter = trim((string) $request->getQueryParam('namespace', ''));

        $query = $this->db->table('pages')
            ->select(['id', 'title', 'slug', 'namespace'])
            ->orderBy('namespace', 'asc')
            ->orderBy('title', 'asc');

        if ($nsFilter !== '') {
            if (strcasecmp($nsFilter, 'Main') === 0) {
                // Main namespace can be stored as '' or 'Main'
                $query->where(function ($q) {
                    $q->where('namespace', '=', 'Main')
                      ->orWhere('namespace', '=', '');
                });
            } else {
                $query->where('namespace', '=', $nsFilter);
            }
        }

        $rows = $query->get();

        $pagesByNs = [];
        foreach ($rows as $row) {
            $ns = $row['namespace'] ?: 'Main';
            $pagesByNs[$ns][] = $row;
        }

        return $this->view('special/all_pages', [
            'title' => 'Special:AllPages',
            'pagesByNs' => $pagesByNs,
            'namespace' => $nsFilter,
        ]);
    }

    public function prefixIndex(Request $request): Response
    {
        $prefix = trim((string) $request->getQueryParam('prefix', ''));
        $ns = trim((string) $request->getQueryParam('namespace', ''));
        $query = $this->db->table('pages')->select(['id', 'title', 'slug', 'namespace']);
        if ($prefix !== '') {
            $query->where('slug', 'LIKE', ($ns ? $ns . ':' : '') . $prefix . '%');
        }
        $results = $query->orderBy('title', 'asc')->limit(200)->get();
        return $this->view('special/prefix_index', [
            'title' => 'Special:PrefixIndex',
            'prefix' => $prefix,
            'namespace' => $ns,
            'results' => $results,
        ]);
    }

    public function categories(Request $request): Response
    {
        return $this->view('special/categories', [ 'title' => 'Special:Categories' ]);
    }

    public function categoryTree(Request $request): Response
    {
        return $this->view('special/category_tree', [ 'title' => 'Special:CategoryTree' ]);
    }

    public function disambiguationPages(Request $request): Response
    {
        return $this->view('special/disambiguation_pages', [ 'title' => 'Special:DisambiguationPages' ]);
    }

    public function linkSearch(Request $request): Response
    {
        $q = trim((string) $request->getQueryParam('q', ''));
        return $this->view('special/link_search', [ 'title' => 'Special:LinkSearch', 'q' => $q ]);
    }

    public function listRedirects(Request $request): Response
    {
        return $this->view('special/list_redirects', [ 'title' => 'Special:ListRedirects' ]);
    }

    public function disambiguationPageLinks(Request $request): Response
    {
        return $this->view('special/disambiguation_page_links', [ 'title' => 'Special:DisambiguationPageLinks' ]);
    }

    public function pagesWithProp(Request $request): Response
    {
        $prop = trim((string) $request->getQueryParam('prop', ''));
        return $this->view('special/pages_with_prop', [ 'title' => 'Special:PagesWithProp', 'prop' => $prop ]);
    }

    public function specialSearch(Request $request): Response
    {
        $q = trim((string) $request->getQueryParam('q', ''));
        return $this->view('special/search', [ 'title' => 'Special:Search', 'q' => $q ]);
    }

    public function trackingCategories(Request $request): Response
    {
        return $this->view('special/tracking_categories', [ 'title' => 'Special:TrackingCategories' ]);
    }

    // Maintenance report placeholders
    public function brokenRedirects(Request $request): Response
    {
        return $this->view('special/maintenance/broken_redirects', ['title' => 'Special:BrokenRedirects']);
    }

    public function deadendPages(Request $request): Response
    {
        return $this->view('special/maintenance/deadend_pages', ['title' => 'Special:DeadendPages']);
    }

    public function doubleRedirects(Request $request): Response
    {
        return $this->view('special/maintenance/double_redirects', ['title' => 'Special:DoubleRedirects']);
    }

    public function lintErrors(Request $request): Response
    {
        return $this->view('special/maintenance/lint_errors', ['title' => 'Special:LintErrors']);
    }

    public function longPages(Request $request): Response
    {
        return $this->view('special/maintenance/long_pages', ['title' => 'Special:LongPages']);
    }

    public function oldestPages(Request $request): Response
    {
        return $this->view('special/maintenance/oldest_pages', ['title' => 'Special:OldestPages']);
    }

    public function orphanedPages(Request $request): Response
    {
        return $this->view('special/maintenance/orphaned_pages', ['title' => 'Special:OrphanedPages']);
    }

    public function fewestRevisions(Request $request): Response
    {
        return $this->view(
            'special/maintenance/fewest_revisions',
            ['title' => 'Special:PagesWithTheFewestRevisions']
        );
    }

    public function withoutLanguageLinks(Request $request): Response
    {
        return $this->view(
            'special/maintenance/without_language_links',
            ['title' => 'Special:PagesWithoutLanguageLinks']
        );
    }

    public function protectedPages(Request $request): Response
    {
        return $this->view('special/maintenance/protected_pages', ['title' => 'Special:ProtectedPages']);
    }

    public function protectedTitles(Request $request): Response
    {
        return $this->view('special/maintenance/protected_titles', ['title' => 'Special:ProtectedTitles']);
    }

    public function shortPages(Request $request): Response
    {
        return $this->view('special/maintenance/short_pages', ['title' => 'Special:ShortPages']);
    }

    public function uncategorizedCategories(Request $request): Response
    {
        return $this->view('special/maintenance/uncategorized_categories', ['title' => 'Special:UncategorizedCategories']);
    }

    public function uncategorizedFiles(Request $request): Response
    {
        return $this->view('special/maintenance/uncategorized_files', ['title' => 'Special:UncategorizedFiles']);
    }

    public function uncategorizedPages(Request $request): Response
    {
        return $this->view('special/maintenance/uncategorized_pages', ['title' => 'Special:UncategorizedPages']);
    }

    public function uncategorizedTemplates(Request $request): Response
    {
        return $this->view('special/maintenance/uncategorized_templates', ['title' => 'Special:UncategorizedTemplates']);
    }

    public function unusedCategories(Request $request): Response
    {
        return $this->view('special/maintenance/unused_categories', ['title' => 'Special:UnusedCategories']);
    }

    public function unusedFiles(Request $request): Response
    {
        return $this->view('special/maintenance/unused_files', ['title' => 'Special:UnusedFiles']);
    }

    public function unusedTemplates(Request $request): Response
    {
        return $this->view('special/maintenance/unused_templates', ['title' => 'Special:UnusedTemplates']);
    }

    public function wantedCategories(Request $request): Response
    {
        return $this->view('special/maintenance/wanted_categories', ['title' => 'Special:WantedCategories']);
    }

    public function wantedFiles(Request $request): Response
    {
        return $this->view('special/maintenance/wanted_files', ['title' => 'Special:WantedFiles']);
    }

    public function wantedPages(Request $request): Response
    {
        return $this->view('special/maintenance/wanted_pages', ['title' => 'Special:WantedPages']);
    }

    public function wantedTemplates(Request $request): Response
    {
        return $this->view('special/maintenance/wanted_templates', ['title' => 'Special:WantedTemplates']);
    }
}
