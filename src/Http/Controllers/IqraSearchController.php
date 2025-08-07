<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;
use IslamWiki\Core\Search\IqraSearchEngine;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;
use Exception;

/**
 * Iqra Search Controller
 *
 * Advanced search controller using the Iqra search engine with:
 * - Enhanced relevance scoring
 * - Islamic content optimization
 * - Multi-language support
 * - Advanced filtering and sorting
 * - Search analytics and insights
 */
class IqraSearchController extends Controller
{
    protected IqraSearchEngine $searchEngine;

    public function __construct(Connection $db, \IslamWiki\Core\Container\Asas $container)
    {
        parent::__construct($db, $container);
        $this->searchEngine = new IqraSearchEngine($this->db);
    }

    /**
     * Display the Iqra search interface
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
                'pages' => 'Wiki Pages',
                'quran' => 'Quran Verses',
                'hadith' => 'Hadith',
                'calendar' => 'Calendar Events',
                'prayer' => 'Prayer Times',
                'scholars' => 'Islamic Scholars'
            ],
            'sortOptions' => [
                'relevance' => 'Relevance',
                'date' => 'Date',
                'title' => 'Title',
                'type' => 'Content Type'
            ],
            'orderOptions' => [
                'desc' => 'Descending',
                'asc' => 'Ascending'
            ]
        ]);
    }

    /**
     * API endpoint for Iqra search
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
     * Get search suggestions with Iqra engine
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
     * Get search analytics and insights
     */
    public function apiAnalytics(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';

        if (empty($query)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Query parameter is required',
                'code' => 'MISSING_QUERY'
            ]));
        }

        try {
            $analytics = $this->getSearchAnalytics($query);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'analytics' => $analytics,
                'query' => $query
            ]));
        } catch (Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Failed to get analytics: ' . $e->getMessage(),
                'code' => 'ANALYTICS_ERROR'
            ]));
        }
    }

    /**
     * Get search statistics
     */
    protected function getSearchStatistics(string $query): array
    {
        $stats = [
            'total_pages' => $this->getContentTypeCount($query, 'pages'),
            'total_quran' => $this->getContentTypeCount($query, 'quran'),
            'total_hadith' => $this->getContentTypeCount($query, 'hadith'),
            'total_calendar' => $this->getContentTypeCount($query, 'calendar'),
            'total_prayer' => $this->getContentTypeCount($query, 'prayer'),
            'total_scholars' => $this->getContentTypeCount($query, 'scholars'),
            'total_all' => $this->getContentTypeCount($query, 'all')
        ];

        return $stats;
    }

    /**
     * Get content type count
     */
    protected function getContentTypeCount(string $query, string $type): int
    {
        $words = $this->searchEngine->tokenizeQuery($query);

        switch ($type) {
            case 'pages':
                return $this->searchEngine->getPageCount($words);
            case 'quran':
                return $this->searchEngine->getQuranCount($words);
            case 'hadith':
                return $this->searchEngine->getHadithCount($words);
            case 'calendar':
                return $this->searchEngine->getCalendarCount($words);
            case 'prayer':
                return $this->searchEngine->getPrayerCount($words);
            case 'scholars':
                return $this->searchEngine->getScholarCount($words);
            default:
                return $this->searchEngine->getAllCount($words);
        }
    }

    /**
     * Get search analytics
     */
    protected function getSearchAnalytics(string $query): array
    {
        return $this->searchEngine->getSearchAnalytics($query);
    }
}
