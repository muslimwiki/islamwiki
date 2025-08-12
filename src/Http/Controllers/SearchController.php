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
use IslamWiki\Models\Page;
use IslamWiki\Models\QuranAyah;
use IslamWiki\Models\Hadith;
use IslamWiki\Models\HijriCalendar;
use IslamWiki\Models\PrayerTime;
use IslamWiki\Core\Database\Connection;
use Exception;
use IslamWiki\Core\Container\AsasContainer;

class SearchController extends Controller
{
    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
    }

    /**
     * Display the main search page
     */
    public function index(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';
        $type = $request->getQueryParams()['type'] ?? 'all';
        $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
        $limit = 20;

        $results = [];
        $totalResults = 0;
        $searchStats = [];

        if (!empty($query)) {
            $results = $this->performSearch($query, $type, $page, $limit);
            $totalResults = $this->getTotalResults($query, $type);
            $searchStats = $this->getSearchStatistics($query);
        }

        return $this->view('search/index', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'totalResults' => $totalResults,
            'currentPage' => $page,
            'totalPages' => ceil($totalResults / $limit),
            'searchStats' => $searchStats,
            'searchTypes' => [
                'all' => 'All Content',
                'pages' => 'Wiki Pages',
                'quran' => 'Quran Ayahs',
                'hadith' => 'Hadith',
                'calendar' => 'Calendar Events',
                'prayer' => 'Prayer Times'
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
        $limit = 20;

        if (empty($query)) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Query parameter is required'
            ]));
        }

        try {
            $results = $this->performSearch($query, $type, $page, $limit);
            $totalResults = $this->getTotalResults($query, $type);
            $searchStats = $this->getSearchStatistics($query);

            $response = [
                'success' => true,
                'query' => $query,
                'type' => $type,
                'results' => $results,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalResults / $limit),
                    'total_results' => $totalResults,
                    'per_page' => $limit
                ],
                'statistics' => $searchStats,
                'search_time' => microtime(true)
            ];

            return new Response(200, ['Content-Type' => 'application/json'], json_encode($response));
        } catch (Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Search failed: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Perform search across all content types
     */
    protected function performSearch(string $query, string $type, int $page, int $limit): array
    {
        $results = [];
        $offset = ($page - 1) * $limit;

        switch ($type) {
            case 'pages':
                $results = $this->searchPages($query, $offset, $limit);
                break;
            case 'quran':
                $results = $this->searchQuran($query, $offset, $limit);
                break;
            case 'hadith':
                $results = $this->searchHadith($query, $offset, $limit);
                break;
            case 'calendar':
                $results = $this->searchCalendar($query, $offset, $limit);
                break;
            case 'salah':
                $results = $this->searchSalah($query, $offset, $limit);
                break;
            default:
                $results = $this->searchAll($query, $offset, $limit);
        }

        return $results;
    }

    /**
     * Search wiki pages
     */
    protected function searchPages(string $query, int $offset, int $limit): array
    {
        $sql = "SELECT p.*, u.username as author_name, 
                       MATCH(p.title, p.content) AGAINST(? IN BOOLEAN MODE) as relevance
                FROM pages p 
                LEFT JOIN users u ON p.created_by = u.id
                WHERE MATCH(p.title, p.content) AGAINST(? IN BOOLEAN MODE)
                ORDER BY relevance DESC, p.updated_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'page',
                'id' => $row->id,
                'title' => $row->title,
                'slug' => $row->slug,
                'excerpt' => $this->createExcerpt($row->content, $query),
                'author' => $row->author_name,
                'updated_at' => $row->updated_at,
                'relevance' => $row->relevance,
                'url' => '/' . $row->slug
            ];
        }

        return $results;
    }

    /**
     * Search Quran ayahs
     */
    protected function searchQuran(string $query, int $offset, int $limit): array
    {
        $sql = "SELECT v.*, s.name_english as surah_name, s.name_translation as english_name,
                       MATCH(v.text_arabic, v.text_uthmani) AGAINST(? IN BOOLEAN MODE) as relevance
                FROM ayahs v
                JOIN surahs s ON v.surah_number = s.number
                WHERE MATCH(v.text_arabic, v.text_uthmani) AGAINST(? IN BOOLEAN MODE)
                ORDER BY relevance DESC, v.surah_number ASC, v.ayah_number ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'quran',
                'id' => $row->id,
                'title' => "{$row->surah_name} ({$row->surah_number}:{$row->ayah_number})",
                'surah_name' => $row->surah_name,
                'english_name' => $row->english_name,
                'ayah_number' => $row->ayah_number,
                'surah_number' => $row->surah_number,
                'text_arabic' => $row->text_arabic,
                'translation_text' => $row->text_uthmani,
                'excerpt' => $this->createExcerpt($row->text_uthmani, $query),
                'relevance' => $row->relevance,
                'url' => "/quran/{$row->surah_number}/{$row->ayah_number}"
            ];
        }

        return $results;
    }

    /**
     * Search Hadith
     */
    protected function searchHadith(string $query, int $offset, int $limit): array
    {
        $sql = "SELECT h.*, c.name as collection_name,
                       MATCH(h.arabic_text, h.english_text) AGAINST(? IN BOOLEAN MODE) as relevance
                FROM hadiths h
                JOIN hadith_collections c ON h.collection_id = c.id
                WHERE MATCH(h.arabic_text, h.english_text) AGAINST(? IN BOOLEAN MODE)
                ORDER BY relevance DESC, h.collection_id ASC, h.hadith_number ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'hadith',
                'id' => $row->id,
                'title' => "{$row->collection_name} #{$row->hadith_number}",
                'collection_name' => $row->collection_name,
                'hadith_number' => $row->hadith_number,
                'arabic_text' => $row->arabic_text,
                'translation' => $row->english_text,
                'narrator' => $row->narrator ?? null,
                'authenticity' => $row->grade,
                'excerpt' => $this->createExcerpt($row->english_text, $query),
                'relevance' => $row->relevance,
                'url' => "/hadith/{$row->collection_id}/{$row->hadith_number}"
            ];
        }

        return $results;
    }

    /**
     * Search calendar events
     */
    protected function searchCalendar(string $query, int $offset, int $limit): array
    {
        $sql = "SELECT e.*, c.name as category_name,
                       MATCH(e.title, e.title_arabic, e.description, e.description_arabic) 
                       AGAINST(? IN BOOLEAN MODE) as relevance
                FROM islamic_events e
                LEFT JOIN event_categories c ON e.category_id = c.id
                WHERE MATCH(e.title, e.title_arabic, e.description, e.description_arabic) 
                      AGAINST(? IN BOOLEAN MODE)
                ORDER BY relevance DESC, e.gregorian_date ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'calendar',
                'id' => $row->id,
                'title' => $row->title,
                'arabic_title' => $row->title_arabic,
                'description' => $row->description,
                'category_name' => $row->category_name,
                'event_date' => $row->gregorian_date,
                'hijri_date' => $row->hijri_date,
                'excerpt' => $this->createExcerpt($row->description, $query),
                'relevance' => $row->relevance,
                'url' => "/calendar/event/{$row->id}"
            ];
        }

        return $results;
    }

    /**
     * Search salah times
     */
    protected function searchSalah(string $query, int $offset, int $limit): array
    {
        $sql = "SELECT ul.*,
                       MATCH(ul.name, ul.city, ul.country) AGAINST(? IN BOOLEAN MODE) as relevance
                FROM user_locations ul
                WHERE MATCH(ul.name, ul.city, ul.country) AGAINST(? IN BOOLEAN MODE)
                ORDER BY relevance DESC, ul.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'salah',
                'id' => $row->id,
                'title' => "Salah Location - {$row->city}, {$row->country}",
                'city' => $row->city,
                'country' => $row->country,
                'location_name' => $row->name,
                'salah_date' => date('Y-m-d'),
                'fajr' => 'N/A',
                'dhuhr' => 'N/A',
                'asr' => 'N/A',
                'maghrib' => 'N/A',
                'isha' => 'N/A',
                'relevance' => $row->relevance,
                'url' => "/salah/show/" . date('Y-m-d') . "/{$row->id}"
            ];
        }

        return $results;
    }

    /**
     * Search across all content types
     */
    protected function searchAll(string $query, int $offset, int $limit): array
    {
        $allResults = [];

        // Search each type
        $pages = $this->searchPages($query, 0, $limit);
        $quran = $this->searchQuran($query, 0, $limit);
        $hadith = $this->searchHadith($query, 0, $limit);
        $calendar = $this->searchCalendar($query, 0, $limit);
        $salah = $this->searchSalah($query, 0, $limit);

        // Combine and sort by relevance
        $allResults = array_merge($pages, $quran, $hadith, $calendar, $salah);
        usort($allResults, function ($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        // Apply pagination
        return array_slice($allResults, $offset, $limit);
    }

    /**
     * Get total results count
     */
    protected function getTotalResults(string $query, string $type): int
    {
        $count = 0;

        switch ($type) {
            case 'pages':
                $sql = "SELECT COUNT(*) FROM pages WHERE MATCH(title, content) AGAINST(?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$query]);
                $count = $stmt->fetchColumn();
                break;
            case 'quran':
                $sql = "SELECT COUNT(*) FROM ayahs WHERE MATCH(text_arabic, text_uthmani) AGAINST(?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$query]);
                $count = $stmt->fetchColumn();
                break;
            case 'hadith':
                $sql = "SELECT COUNT(*) FROM hadiths WHERE MATCH(arabic_text, english_text) AGAINST(?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$query]);
                $count = $stmt->fetchColumn();
                break;
            case 'calendar':
                $sql = "SELECT COUNT(*) FROM islamic_events 
                        WHERE MATCH(title, title_arabic, description, description_arabic) AGAINST(?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$query]);
                $count = $stmt->fetchColumn();
                break;
            case 'prayer':
                $sql = "SELECT COUNT(*) FROM user_locations WHERE MATCH(name, city, country) AGAINST(?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$query]);
                $count = $stmt->fetchColumn();
                break;
            default:
                // Count all types
                $count = 0;
                $types = ['pages', 'quran', 'hadith', 'calendar', 'prayer'];
                foreach ($types as $searchType) {
                    $count += $this->getTotalResults($query, $searchType);
                }
        }

        return $count;
    }

    /**
     * Get search statistics
     */
    protected function getSearchStatistics(string $query): array
    {
        $stats = [
            'total_pages' => $this->getTotalResults($query, 'pages'),
            'total_quran' => $this->getTotalResults($query, 'quran'),
            'total_hadith' => $this->getTotalResults($query, 'hadith'),
            'total_calendar' => $this->getTotalResults($query, 'calendar'),
            'total_prayer' => $this->getTotalResults($query, 'prayer'),
            'total_all' => $this->getTotalResults($query, 'all')
        ];

        return $stats;
    }

    /**
     * Create excerpt from content highlighting search terms
     */
    protected function createExcerpt(string $content, string $query, int $length = 200): string
    {
        // Remove HTML tags
        $content = strip_tags($content);

        // Find the position of the first search term
        $words = explode(' ', strtolower($query));
        $position = -1;

        foreach ($words as $word) {
            $pos = stripos($content, $word);
            if ($pos !== false && ($position === -1 || $pos < $position)) {
                $position = $pos;
            }
        }

        if ($position === -1) {
            // No search terms found, return beginning
            $position = 0;
        }

        // Extract excerpt around the found position
        $start = max(0, $position - $length / 2);
        $excerpt = substr($content, $start, $length);

        // Add ellipsis if needed
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }
        if (strlen($content) > $start + $length) {
            $excerpt .= '...';
        }

        // Highlight search terms
        foreach ($words as $word) {
            $excerpt = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<mark>$1</mark>', $excerpt);
        }

        return $excerpt;
    }

    /**
     * Get search suggestions
     */
    public function apiSuggestions(Request $request): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';

        if (empty($query) || strlen($query) < 2) {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([]));
        }

        try {
            $suggestions = $this->getSearchSuggestions($query);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'suggestions' => $suggestions
            ]));
        } catch (Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Failed to get suggestions: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Get search suggestions from various content types
     */
    protected function getSearchSuggestions(string $query): array
    {
        $suggestions = [];

        // Get page title suggestions
        $sql = "SELECT title, slug FROM pages 
                WHERE title LIKE ? OR slug LIKE ? 
                ORDER BY updated_at DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);

        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'page',
                'text' => $row->title,
                'url' => '/' . $row->slug
            ];
        }

        // Get Quran suggestions
        $sql = "SELECT s.name_english as surah_name, v.ayah_number, v.text_uthmani, v.surah_number 
                FROM ayahs v 
                JOIN surahs s ON v.surah_number = s.number 
                WHERE v.text_arabic LIKE ? OR s.name_english LIKE ?
                ORDER BY v.surah_number ASC, v.ayah_number ASC LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);

        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'quran',
                'text' => "{$row->surah_name} {$row->ayah_number}",
                'url' => "/quran/{$row->surah_number}/{$row->ayah_number}"
            ];
        }

        // Get Hadith suggestions
        $sql = "SELECT h.hadith_number, c.name as collection_name, h.english_text, h.collection_id 
                FROM hadiths h 
                JOIN hadith_collections c ON h.collection_id = c.id 
                WHERE h.english_text LIKE ? 
                ORDER BY h.collection_id ASC, h.hadith_number ASC LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%"]);

        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'hadith',
                'text' => "{$row->collection_name} #{$row->hadith_number}",
                'url' => "/hadith/{$row->collection_id}/{$row->hadith_number}"
            ];
        }

        return $suggestions;
    }
}
