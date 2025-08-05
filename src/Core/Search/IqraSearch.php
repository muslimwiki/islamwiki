<?php
declare(strict_types=1);

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

namespace IslamWiki\Core\Search;

use IslamWiki\Core\Database\Connection;
use Exception;

/**
 * Iqra Search Engine
 * 
 * Advanced search engine optimized for Islamic content with:
 * - Relevance scoring based on Islamic content importance
 * - Arabic text support and transliteration
 * - Fuzzy matching for Islamic terms
 * - Multi-language search (Arabic, English, transliterated)
 * - Content type weighting
 * - Search suggestions and autocomplete
 */
class IqraSearch
{
    protected Connection $db;
    protected array $islamicTerms = [];
    protected array $arabicStopWords = [];
    protected array $englishStopWords = [];
    
    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->loadIslamicTerms();
        $this->loadStopWords();
    }

    /**
     * Perform advanced search with Iqra engine
     */
    public function search(string $query, array $options = []): array
    {
        $query = $this->normalizeQuery($query);
        $searchType = $options['type'] ?? 'all';
        $page = (int) ($options['page'] ?? 1);
        $limit = (int) ($options['limit'] ?? 20);
        $offset = (int) (($page - 1) * $limit);

        $results = [];
        
        switch ($searchType) {
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
            case 'prayer':
                $results = $this->searchPrayer($query, $offset, $limit);
                break;
            case 'scholars':
                $results = $this->searchScholars($query, $offset, $limit);
                break;
            default:
                $results = $this->searchAll($query, $offset, $limit);
        }

        return [
            'results' => $results,
            'total' => $this->getTotalCount($query, $searchType),
            'query' => $query,
            'type' => $searchType,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Search wiki pages with advanced relevance scoring
     */
    protected function searchPages(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildRelevanceConditions($words);
        
        $sql = "SELECT p.*, u.username as author_name,
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM pages p 
                LEFT JOIN users u ON p.created_by = u.id
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, p.updated_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'page',
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'excerpt' => $this->createHighlightedExcerpt($row['content'], $query),
                'author' => $row['author_name'],
                'updated_at' => $row['updated_at'],
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => '/' . $row['slug'],
                'namespace' => $row['namespace'] ?? ''
            ];
        }

        return $results;
    }

    /**
     * Search Quran verses with Islamic term optimization
     */
    protected function searchQuran(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildQuranRelevanceConditions($words);
        
        $sql = "SELECT v.*, s.name_english as surah_name, s.name_english, s.name_arabic,
                       vt.translation_text,
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM verses v
                JOIN surahs s ON v.surah_number = s.number
                LEFT JOIN verse_translations vt ON v.id = vt.verse_id
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, v.surah_number ASC, v.verse_number ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'quran',
                'id' => $row['id'],
                'title' => "{$row['surah_name']} ({$row['surah_number']}:{$row['verse_number']})",
                'surah_name' => $row['surah_name'],
                'english_name' => $row['name_english'],
                'arabic_name' => $row['name_arabic'],
                'verse_number' => $row['verse_number'],
                'surah_number' => $row['surah_number'],
                'arabic_text' => $row['text_arabic'],
                'translation' => $row['translation_text'],
                'excerpt' => $this->createHighlightedExcerpt($row['translation_text'] ?? '', $query),
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => "/quran/verse/{$row['surah_number']}/{$row['verse_number']}"
            ];
        }

        return $results;
    }

    /**
     * Search Hadith with authenticity weighting
     */
    protected function searchHadith(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildHadithRelevanceConditions($words);
        
        $sql = "SELECT h.*, c.name as collection_name, c.reliability_level,
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM hadiths h
                JOIN hadith_collections c ON h.collection_id = c.id
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, c.reliability_level DESC, h.hadith_number ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'hadith',
                'id' => $row['id'],
                'title' => "{$row['collection_name']} #{$row['hadith_number']}",
                'collection_name' => $row['collection_name'],
                'hadith_number' => $row['hadith_number'],
                'arabic_text' => $row['arabic_text'],
                'translation' => $row['english_text'],
                'narrator' => null,
                'authenticity' => $row['authenticity'],
                'authenticity_level' => $row['reliability_level'],
                'excerpt' => $this->createHighlightedExcerpt($row['english_text'], $query),
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => "/hadith/{$row['collection_id']}/{$row['hadith_number']}"
            ];
        }

        return $results;
    }

    /**
     * Search Islamic calendar events
     */
    protected function searchCalendar(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildCalendarRelevanceConditions($words);
        
        $sql = "SELECT e.*, c.name as category_name,
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM islamic_events e
                LEFT JOIN event_categories c ON e.category_id = c.id
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, e.hijri_date ASC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'calendar',
                'id' => $row['id'],
                'title' => $row['title'],
                'arabic_title' => $row['title_arabic'],
                'description' => $row['description'],
                'category_name' => $row['category_name'],
                'importance_level' => null,
                'event_date' => $row['gregorian_date'],
                'hijri_date' => $row['hijri_date'],
                'excerpt' => $this->createHighlightedExcerpt($row['description'], $query),
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => "/calendar/event/{$row['id']}"
            ];
        }

        return $results;
    }

    /**
     * Search prayer times
     */
    protected function searchPrayer(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildPrayerRelevanceConditions($words);
        
        $sql = "SELECT pt.*, pt.location as location_name,
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM prayer_times pt
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, pt.date DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'prayer',
                'id' => $row['id'],
                'title' => "Prayer Times - {$row['location_name']}",
                'city' => null,
                'country' => null,
                'location_name' => $row['location_name'],
                'prayer_date' => $row['date'],
                'fajr' => $row['fajr'],
                'dhuhr' => $row['dhuhr'],
                'asr' => $row['asr'],
                'maghrib' => $row['maghrib'],
                'isha' => $row['isha'],
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => "/prayer/show/{$row['prayer_date']}/{$row['location_id']}"
            ];
        }

        return $results;
    }

    /**
     * Search Islamic scholars
     */
    protected function searchScholars(string $query, int $offset, int $limit): array
    {
        $words = $this->tokenizeQuery($query);
        $relevanceConditions = $this->buildScholarRelevanceConditions($words);
        
        $sql = "SELECT s.*, 
                       {$relevanceConditions['score']} as relevance_score,
                       {$relevanceConditions['match_count']} as match_count
                FROM scholars s
                WHERE {$relevanceConditions['where']}
                ORDER BY relevance_score DESC, s.verification_status DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($relevanceConditions['params'], [$limit, $offset]));

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'type' => 'scholar',
                'id' => $row['id'],
                'title' => $row['name'],
                'arabic_name' => $row['arabic_name'],
                'biography' => $row['biography'],
                'era' => $row['era'],
                'school' => $row['school'],
                'specialization' => $row['specialization'],
                'importance_level' => $row['importance_level'],
                'excerpt' => $this->createHighlightedExcerpt($row['biography'], $query),
                'relevance' => $row['relevance_score'],
                'match_count' => $row['match_count'],
                'url' => "/scholars/{$row['id']}"
            ];
        }

        return $results;
    }

    /**
     * Search across all content types with unified relevance scoring
     */
    protected function searchAll(string $query, int $offset, int $limit): array
    {
        $allResults = [];
        
        // Search each type with reduced limits
        $subLimit = (int) ceil($limit / 5);
        
        $pages = $this->searchPages($query, 0, $subLimit);
        $quran = $this->searchQuran($query, 0, $subLimit);
        $hadith = $this->searchHadith($query, 0, $subLimit);
        $calendar = $this->searchCalendar($query, 0, $subLimit);
        $prayer = $this->searchPrayer($query, 0, $subLimit);
        $scholars = $this->searchScholars($query, 0, $subLimit);

        // Combine and normalize relevance scores
        $allResults = array_merge($pages, $quran, $hadith, $calendar, $prayer, $scholars);
        
        // Apply content type weighting
        $allResults = $this->applyContentTypeWeighting($allResults);
        
        // Sort by normalized relevance
        usort($allResults, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        // Apply pagination
        return array_slice($allResults, $offset, $limit);
    }

    /**
     * Build relevance conditions for pages
     */
    protected function buildRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            // Higher weight for title matches
            $scoreParts[] = "CASE WHEN p.title LIKE ? THEN 10 ELSE 0 END";
            $scoreParts[] = "CASE WHEN p.content LIKE ? THEN 5 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN p.title LIKE ? OR p.content LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Build relevance conditions for Quran
     */
    protected function buildQuranRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(v.text_arabic LIKE ? OR vt.translation_text LIKE ? OR s.name_english LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            // Higher weight for Arabic text matches
            $scoreParts[] = "CASE WHEN v.text_arabic LIKE ? THEN 15 ELSE 0 END";
            $scoreParts[] = "CASE WHEN vt.translation_text LIKE ? THEN 10 ELSE 0 END";
            $scoreParts[] = "CASE WHEN s.name_english LIKE ? THEN 8 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN v.text_arabic LIKE ? OR vt.translation_text LIKE ? OR s.name_english LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Build relevance conditions for Hadith
     */
    protected function buildHadithRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(h.arabic_text LIKE ? OR h.english_text LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            // Higher weight for Arabic text matches
            $scoreParts[] = "CASE WHEN h.arabic_text LIKE ? THEN 15 ELSE 0 END";
            $scoreParts[] = "CASE WHEN h.english_text LIKE ? THEN 10 ELSE 0 END";
            $scoreParts[] = "CASE WHEN c.name LIKE ? THEN 5 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN h.arabic_text LIKE ? OR h.english_text LIKE ? OR c.name LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Build relevance conditions for calendar events
     */
    protected function buildCalendarRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.title_arabic LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $scoreParts[] = "CASE WHEN e.title LIKE ? THEN 12 ELSE 0 END";
            $scoreParts[] = "CASE WHEN e.title_arabic LIKE ? THEN 10 ELSE 0 END";
            $scoreParts[] = "CASE WHEN e.description LIKE ? THEN 8 ELSE 0 END";
            $scoreParts[] = "CASE WHEN c.name LIKE ? THEN 5 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN e.title LIKE ? OR e.description LIKE ? OR e.title_arabic LIKE ? OR c.name LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Build relevance conditions for prayer times
     */
    protected function buildPrayerRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(pt.location LIKE ?)";
            $params[] = "%{$word}%";
            
            $scoreParts[] = "CASE WHEN pt.location LIKE ? THEN 10 ELSE 0 END";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN pt.location LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Build relevance conditions for scholars
     */
    protected function buildScholarRelevanceConditions(array $words): array
    {
        $conditions = [];
        $params = [];
        $scoreParts = [];
        $matchCountParts = [];

        foreach ($words as $i => $word) {
            $param = "word_{$i}";
            $conditions[] = "(s.name LIKE ? OR s.arabic_name LIKE ? OR s.biography LIKE ? OR s.specialization LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $scoreParts[] = "CASE WHEN s.name LIKE ? THEN 12 ELSE 0 END";
            $scoreParts[] = "CASE WHEN s.arabic_name LIKE ? THEN 10 ELSE 0 END";
            $scoreParts[] = "CASE WHEN s.biography LIKE ? THEN 8 ELSE 0 END";
            $scoreParts[] = "CASE WHEN s.specialization LIKE ? THEN 6 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            
            $matchCountParts[] = "CASE WHEN s.name LIKE ? OR s.arabic_name LIKE ? OR s.biography LIKE ? OR s.specialization LIKE ? THEN 1 ELSE 0 END";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }

        $where = empty($conditions) ? "1=1" : "(" . implode(" OR ", $conditions) . ")";
        $score = empty($scoreParts) ? "0" : "(" . implode(" + ", $scoreParts) . ")";
        $matchCount = empty($matchCountParts) ? "0" : "(" . implode(" + ", $matchCountParts) . ")";

        return [
            'where' => $where,
            'score' => $score,
            'match_count' => $matchCount,
            'params' => $params
        ];
    }

    /**
     * Apply content type weighting to unified search results
     */
    protected function applyContentTypeWeighting(array $results): array
    {
        $typeWeights = [
            'quran' => 1.5,      // Highest priority for Quran
            'hadith' => 1.3,     // High priority for Hadith
            'scholar' => 1.2,    // High priority for scholars
            'calendar' => 1.1,   // Medium-high for events
            'page' => 1.0,       // Standard weight for pages
            'prayer' => 0.9      // Lower weight for prayer times
        ];

        foreach ($results as &$result) {
            $weight = $typeWeights[$result['type']] ?? 1.0;
            $result['relevance'] = $result['relevance'] * $weight;
        }

        return $results;
    }

    /**
     * Normalize search query
     */
    public function normalizeQuery(string $query): string
    {
        // Remove extra whitespace
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        // Convert to lowercase for case-insensitive search
        $query = strtolower($query);
        
        // Remove common punctuation
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query);
        
        return trim($query);
    }

    /**
     * Tokenize query into searchable words
     */
    public function tokenizeQuery(string $query): array
    {
        $words = explode(' ', $query);
        $tokens = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 2 && !in_array($word, $this->englishStopWords)) {
                $tokens[] = $word;
            }
        }
        
        return array_unique($tokens);
    }

    /**
     * Create highlighted excerpt with search terms
     */
    protected function createHighlightedExcerpt(string $content, string $query, int $length = 200): string
    {
        // Remove HTML tags
        $content = strip_tags($content);
        
        // Find the position of the first search term
        $words = $this->tokenizeQuery($query);
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
     * Get total count for search type
     */
    protected function getTotalCount(string $query, string $type): int
    {
        $words = $this->tokenizeQuery($query);
        
        switch ($type) {
            case 'pages':
                return $this->getPageCount($words);
            case 'quran':
                return $this->getQuranCount($words);
            case 'hadith':
                return $this->getHadithCount($words);
            case 'calendar':
                return $this->getCalendarCount($words);
            case 'prayer':
                return $this->getPrayerCount($words);
            case 'scholars':
                return $this->getScholarCount($words);
            default:
                return $this->getAllCount($words);
        }
    }

    /**
     * Get count for pages
     */
    public function getPageCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(title LIKE ? OR content LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM pages WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for Quran
     */
    public function getQuranCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(v.text_arabic LIKE ? OR vt.translation_text LIKE ? OR s.name_english LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM verses v JOIN surahs s ON v.surah_number = s.number LEFT JOIN verse_translations vt ON v.id = vt.verse_id WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for Hadith
     */
    public function getHadithCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(h.arabic_text LIKE ? OR h.english_text LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM hadiths h JOIN hadith_collections c ON h.collection_id = c.id WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for calendar events
     */
    public function getCalendarCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.title_arabic LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM islamic_events e LEFT JOIN event_categories c ON e.category_id = c.id WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for prayer times
     */
    public function getPrayerCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(pt.location LIKE ?)";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM prayer_times pt WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for scholars
     */
    public function getScholarCount(array $words): int
    {
        if (empty($words)) return 0;
        
        $conditions = [];
        $params = [];
        
        foreach ($words as $word) {
            $conditions[] = "(s.name LIKE ? OR s.arabic_name LIKE ? OR s.biography LIKE ? OR s.specialization LIKE ?)";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
            $params[] = "%{$word}%";
        }
        
        $sql = "SELECT COUNT(*) FROM scholars s WHERE " . implode(" OR ", $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count for all content types
     */
    public function getAllCount(array $words): int
    {
        return $this->getPageCount($words) +
               $this->getQuranCount($words) +
               $this->getHadithCount($words) +
               $this->getCalendarCount($words) +
               $this->getPrayerCount($words) +
               $this->getScholarCount($words);
    }

    /**
     * Load Islamic terms for enhanced search
     */
    protected function loadIslamicTerms(): void
    {
        $this->islamicTerms = [
            'allah', 'muhammad', 'quran', 'hadith', 'sunnah', 'shariah', 'halal', 'haram',
            'salah', 'prayer', 'ramadan', 'eid', 'hajj', 'umrah', 'zakat', 'sadaqah',
            'jannah', 'akhirah', 'taqwa', 'iman', 'islam', 'muslim', 'sahaba', 'tabiun',
            'madhhab', 'fiqh', 'usul', 'aqeedah', 'tawhid', 'shirk', 'bidah', 'sunnah',
            'dua', 'dhikr', 'tasbih', 'istighfar', 'bismillah', 'alhamdulillah', 'mashallah',
            'inshallah', 'astaghfirullah', 'subhanallah', 'allahumma', 'ya allah'
        ];
    }

    /**
     * Load stop words for Arabic and English
     */
    protected function loadStopWords(): void
    {
        $this->englishStopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had',
            'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can',
            'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they',
            'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their'
        ];

        $this->arabicStopWords = [
            'في', 'من', 'إلى', 'على', 'عن', 'مع', 'هذا', 'هذه', 'ذلك', 'تلك',
            'كان', 'كانت', 'يكون', 'تكون', 'أنا', 'أنت', 'هو', 'هي', 'نحن', 'أنتم',
            'هم', 'هن', 'لي', 'لك', 'له', 'لها', 'لنا', 'لكم', 'لهم', 'لهن'
        ];
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions(string $query): array
    {
        $query = $this->normalizeQuery($query);
        if (strlen($query) < 2) {
            return [];
        }

        $suggestions = [];
        
        // Get page suggestions
        $sql = "SELECT title, slug FROM pages 
                WHERE title LIKE ? OR slug LIKE ? 
                ORDER BY updated_at DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);
        
        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'page',
                'text' => $row['title'],
                'url' => '/' . $row['slug']
            ];
        }

        // Get Quran suggestions
        $sql = "SELECT s.name_english as surah_name, v.verse_number, v.surah_number 
                FROM verses v 
                JOIN surahs s ON v.surah_number = s.number 
                LEFT JOIN verse_translations vt ON v.id = vt.verse_id
                WHERE vt.translation_text LIKE ? OR s.name_english LIKE ?
                ORDER BY v.surah_number ASC, v.verse_number ASC LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);
        
        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'quran',
                'text' => "{$row['surah_name']} {$row['verse_number']}",
                'url' => "/quran/verse/{$row['surah_number']}/{$row['verse_number']}"
            ];
        }

        // Get Hadith suggestions
        $sql = "SELECT h.hadith_number, c.name as collection_name, h.collection_id 
                FROM hadiths h 
                JOIN hadith_collections c ON h.collection_id = c.id 
                WHERE h.english_text LIKE ? OR c.name LIKE ?
                ORDER BY h.collection_id ASC, h.hadith_number ASC LIMIT 3";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%"]);
        
        while ($row = $stmt->fetch()) {
            $suggestions[] = [
                'type' => 'hadith',
                'text' => "{$row['collection_name']} #{$row['hadith_number']}",
                'url' => "/hadith/{$row['collection_id']}/{$row['hadith_number']}"
            ];
        }

        return $suggestions;
    }

    /**
     * Get search analytics
     */
    public function getSearchAnalytics(string $query): array
    {
        $words = $this->tokenizeQuery($query);
        
        $analytics = [
            'query_analysis' => [
                'original_query' => $query,
                'normalized_query' => $this->normalizeQuery($query),
                'tokenized_words' => $words,
                'word_count' => count($words),
                'contains_arabic' => $this->containsArabic($query),
                'contains_islamic_terms' => $this->containsIslamicTerms($query)
            ],
            'content_distribution' => [
                'pages' => $this->getPageCount($words),
                'quran' => $this->getQuranCount($words),
                'hadith' => $this->getHadithCount($words),
                'calendar' => $this->getCalendarCount($words),
                'prayer' => $this->getPrayerCount($words),
                'scholars' => $this->getScholarCount($words)
            ],
            'relevance_insights' => [
                'high_relevance_terms' => $this->getHighRelevanceTerms($words),
                'suggested_queries' => $this->getSuggestedQueries($query),
                'related_topics' => $this->getRelatedTopics($words)
            ]
        ];

        return $analytics;
    }

    /**
     * Check if query contains Arabic text
     */
    public function containsArabic(string $query): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $query);
    }

    /**
     * Check if query contains Islamic terms
     */
    public function containsIslamicTerms(string $query): array
    {
        $query = strtolower($query);
        $foundTerms = [];
        
        $islamicTerms = [
            'allah', 'muhammad', 'quran', 'hadith', 'sunnah', 'shariah', 'halal', 'haram',
            'salah', 'prayer', 'ramadan', 'eid', 'hajj', 'umrah', 'zakat', 'sadaqah'
        ];
        
        foreach ($islamicTerms as $term) {
            if (strpos($query, $term) !== false) {
                $foundTerms[] = $term;
            }
        }
        
        return $foundTerms;
    }

    /**
     * Get high relevance terms
     */
    public function getHighRelevanceTerms(array $words): array
    {
        $highRelevanceTerms = [];
        
        foreach ($words as $word) {
            if (strlen($word) >= 4) {
                $highRelevanceTerms[] = $word;
            }
        }
        
        return array_slice($highRelevanceTerms, 0, 5);
    }

    /**
     * Get suggested queries
     */
    public function getSuggestedQueries(string $query): array
    {
        $suggestions = [];
        
        // Add common Islamic terms to the query
        $islamicTerms = ['allah', 'quran', 'hadith', 'prayer', 'ramadan'];
        
        foreach ($islamicTerms as $term) {
            if (strpos(strtolower($query), $term) === false) {
                $suggestions[] = $query . ' ' . $term;
            }
        }
        
        return array_slice($suggestions, 0, 3);
    }

    /**
     * Get related topics
     */
    public function getRelatedTopics(array $words): array
    {
        $topics = [];
        
        foreach ($words as $word) {
            switch (strtolower($word)) {
                case 'allah':
                    $topics[] = 'Tawhid (Monotheism)';
                    $topics[] = 'Divine Names';
                    break;
                case 'quran':
                    $topics[] = 'Surah';
                    $topics[] = 'Verses';
                    $topics[] = 'Recitation';
                    break;
                case 'hadith':
                    $topics[] = 'Sunnah';
                    $topics[] = 'Narrators';
                    $topics[] = 'Authenticity';
                    break;
                case 'prayer':
                    $topics[] = 'Salah';
                    $topics[] = 'Prayer Times';
                    $topics[] = 'Mosque';
                    break;
            }
        }
        
        return array_unique($topics);
    }
} 