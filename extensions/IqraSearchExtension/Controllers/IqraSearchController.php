<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;
use IslamWiki\Core\Search\IqraSearch;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;
use Exception;

/**
 * IqraSearchController - Main search controller for unified search
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class IqraSearchController extends Controller
{
    protected IqraSearch $searchEngine;

    public function __construct(Connection $db, AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->searchEngine = new IqraSearch($this->db);
    }

    /**
     * Display the main search interface
     */
    public function index(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';
        $type = $request->getQueryParams()['type'] ?? 'all';
        $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
        $sort = $request->getQueryParams()['sort'] ?? 'relevance';
        $order = $request->getQueryParams()['order'] ?? 'desc';
        $limit = 20;

        $results = [];
        $totalResults = 0;
        $searchStats = [];
        $searchTime = 0;

        if (!empty($query)) {
            $startTime = microtime(true);

            $searchOptions = [
                'type' => $type,
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort,
                'order' => $order
            ];

            $searchResult = $this->searchEngine->search($query, $searchOptions);

            $results = $searchResult['results'];
            $totalResults = $searchResult['total'];
            $searchTime = microtime(true) - $startTime;

            $searchStats = $this->getSearchStatistics($query);
        }

        return $this->view('iqra-search/index', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'totalResults' => $totalResults,
            'currentPage' => $page,
            'totalPages' => ceil($totalResults / $limit),
            'searchStats' => $searchStats,
            'searchTime' => round($searchTime, 3),
            'searchTypes' => [
                'all' => 'All Content',
                'wiki' => 'Wiki Pages',
                'quran' => 'Quran',
                'hadith' => 'Hadith',
                'articles' => 'Articles',
                'scholars' => 'Scholars'
            ],
            'sortOptions' => [
                'relevance' => 'Relevance',
                'date' => 'Date',
                'title' => 'Title',
                'popularity' => 'Popularity'
            ]
        ]);
    }

    /**
     * API endpoint for search
     */
    public function apiSearch(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';
        $type = $request->getQueryParams()['type'] ?? 'all';
        $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
        $limit = (int)($request->getQueryParams()['limit'] ?? 20);
        $sort = $request->getQueryParams()['sort'] ?? 'relevance';
        $order = $request->getQueryParams()['order'] ?? 'desc';

        if (empty($query)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Query parameter is required',
                'code' => 'MISSING_QUERY'
            ]));
        }

        try {
            $startTime = microtime(true);

            $searchOptions = [
                'type' => $type,
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort,
                'order' => $order
            ];

            $searchResult = $this->searchEngine->search($query, $searchOptions);
            $searchTime = microtime(true) - $startTime;

            $searchStats = $this->getSearchStatistics($query);

            $response = [
                'success' => true,
                'query' => $query,
                'type' => $type,
                'results' => $searchResult['results'],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($searchResult['total'] / $limit),
                    'total_results' => $searchResult['total'],
                    'per_page' => $limit
                ],
                'statistics' => $searchStats,
                'search_time' => round($searchTime, 3),
                'engine' => 'Iqra Search Engine v1.0'
            ];

            return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
        } catch (Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Search failed: ' . $e->getMessage(),
                'code' => 'SEARCH_ERROR'
            ]));
        }
    }

    /**
     * API endpoint for search suggestions
     */
    public function apiSuggestions(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';

        if (empty($query) || strlen($query) < 2) {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'suggestions' => []
            ]));
        }

        try {
            $suggestions = $this->searchEngine->getSuggestions($query);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'suggestions' => $suggestions,
                'query' => $query,
                'count' => count($suggestions)
            ]));
        } catch (Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Failed to get suggestions: ' . $e->getMessage(),
                'code' => 'SUGGESTIONS_ERROR'
            ]));
        }
    }

    /**
     * Get search statistics
     */
    private function getSearchStatistics(string $query): array
    {
        // TODO: Implement actual search statistics
        return [
            'total_searches' => rand(100, 1000),
            'popular_queries' => [
                'islamic principles',
                'quran recitation',
                'hadith authenticity'
            ],
            'trending_topics' => [
                'ramadan 2025',
                'eid al-fitr',
                'hajj guide'
            ]
        ];
    }
} 