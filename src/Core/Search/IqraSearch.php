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
 *
 * @category  Core
 * @package   IslamWiki\Core\Search
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Search;

use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * IqraSearch (إقرأ) - Search Engine and Content Discovery System
 *
 * Iqra means "Read" in Arabic, from the first word of the Quran revealed to
 * Prophet Muhammad (PBUH). This class provides comprehensive search capabilities,
 * content discovery, and Islamic knowledge exploration for the IslamWiki application.
 *
 * This system is part of the User Interface Layer and enables users to discover
 * and explore Islamic content through advanced search algorithms and indexing.
 *
 * @category  Core
 * @package   IslamWiki\Core\Search
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class IqraSearch
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Search configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Search index.
     *
     * @var array<string, mixed>
     */
    protected array $searchIndex = [];

    /**
     * Search statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Search filters.
     *
     * @var array<string, array>
     */
    protected array $filters = [];

    /**
     * Constructor.
     *
     * @param ShahidLogger $logger The logging system
     * @param array        $config Search configuration
     */
    public function __construct(ShahidLogger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeSearch();
    }

    /**
     * Initialize search system.
     *
     * @return self
     */
    protected function initializeSearch(): self
    {
        $this->initializeStatistics();
        $this->initializeFilters();
        $this->initializeSearchIndex();
        $this->logger->info('IqraSearch system initialized');

        return $this;
    }

    /**
     * Initialize search statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'searches' => [
                'total_searches' => 0,
                'successful_searches' => 0,
                'failed_searches' => 0,
                'average_search_time' => 0.0
            ],
            'results' => [
                'total_results_returned' => 0,
                'average_results_per_search' => 0,
                'zero_result_searches' => 0
            ],
            'indexing' => [
                'total_documents_indexed' => 0,
                'index_updates' => 0,
                'index_size' => 0
            ],
            'performance' => [
                'total_search_time' => 0.0,
                'fastest_search' => PHP_FLOAT_MAX,
                'slowest_search' => 0.0,
                'cache_hits' => 0,
                'cache_misses' => 0
            ]
        ];

        return $this;
    }

    /**
     * Initialize search filters.
     *
     * @return self
     */
    protected function initializeFilters(): self
    {
        $this->filters = [
            'content_type' => [
                'quran' => 'Quran verses and translations',
                'hadith' => 'Hadith collections and narrations',
                'fiqh' => 'Islamic jurisprudence and rulings',
                'aqeedah' => 'Islamic beliefs and theology',
                'seerah' => 'Prophet Muhammad\'s biography',
                'history' => 'Islamic history and civilization',
                'scholars' => 'Islamic scholars and their works',
                'articles' => 'General Islamic articles'
            ],
            'language' => [
                'arabic' => 'Arabic content',
                'english' => 'English content',
                'urdu' => 'Urdu content',
                'indonesian' => 'Indonesian content',
                'turkish' => 'Turkish content',
                'malay' => 'Malay content'
            ],
            'authenticity' => [
                'sahih' => 'Authentic and reliable',
                'hasan' => 'Good and acceptable',
                'daif' => 'Weak and unreliable',
                'mawdu' => 'Fabricated and false'
            ],
            'scholarly_level' => [
                'beginner' => 'Basic Islamic knowledge',
                'intermediate' => 'Intermediate Islamic knowledge',
                'advanced' => 'Advanced Islamic knowledge',
                'scholarly' => 'Scholarly and academic level'
            ]
        ];

        return $this;
    }

    /**
     * Initialize search index.
     *
     * @return self
     */
    protected function initializeSearchIndex(): self
    {
        // Initialize with sample Islamic content for demonstration
        $this->searchIndex = [
            'quran' => [
                'al-fatiha' => [
                    'title' => 'Al-Fatiha (The Opening)',
                    'arabic' => 'الفاتحة',
                    'content' => 'In the name of Allah, the Entirely Merciful, the Especially Merciful',
                    'type' => 'quran',
                    'language' => 'arabic',
                    'surah_number' => 1,
                    'ayah_number' => 1,
                    'keywords' => ['fatiha', 'opening', 'quran', 'allah', 'mercy'],
                    'tags' => ['quran', 'surah', 'opening', 'mercy'],
                    'relevance_score' => 1.0
                ],
                'al-baqarah' => [
                    'title' => 'Al-Baqarah (The Cow)',
                    'arabic' => 'البقرة',
                    'content' => 'This is the Book about which there is no doubt',
                    'type' => 'quran',
                    'language' => 'arabic',
                    'surah_number' => 2,
                    'ayah_number' => 2,
                    'keywords' => ['baqarah', 'cow', 'book', 'doubt', 'guidance'],
                    'tags' => ['quran', 'surah', 'guidance', 'book'],
                    'relevance_score' => 1.0
                ]
            ],
            'hadith' => [
                'bukhari-1' => [
                    'title' => 'Sahih Bukhari - First Hadith',
                    'arabic' => 'إنما الأعمال بالنيات',
                    'content' => 'Actions are judged by intentions',
                    'type' => 'hadith',
                    'language' => 'arabic',
                    'narrator' => 'Umar ibn al-Khattab',
                    'authenticity' => 'sahih',
                    'keywords' => ['actions', 'intentions', 'bukhari', 'sahih'],
                    'tags' => ['hadith', 'bukhari', 'intentions', 'actions'],
                    'relevance_score' => 1.0
                ]
            ],
            'islamic_concepts' => [
                'tawheed' => [
                    'title' => 'Tawheed (Monotheism)',
                    'arabic' => 'التوحيد',
                    'content' => 'The fundamental principle of Islamic monotheism',
                    'type' => 'aqeedah',
                    'language' => 'english',
                    'keywords' => ['tawheed', 'monotheism', 'oneness', 'allah'],
                    'tags' => ['aqeedah', 'belief', 'monotheism', 'core'],
                    'relevance_score' => 1.0
                ],
                'salah' => [
                    'title' => 'Salah (Prayer)',
                    'arabic' => 'الصلاة',
                    'content' => 'The five daily prayers in Islam',
                    'type' => 'fiqh',
                    'language' => 'english',
                    'keywords' => ['salah', 'prayer', 'worship', 'daily'],
                    'tags' => ['fiqh', 'worship', 'prayer', 'obligation'],
                    'relevance_score' => 1.0
                ]
            ]
        ];

        $this->statistics['indexing']['total_documents_indexed'] = count($this->searchIndex, COUNT_RECURSIVE);
        $this->statistics['indexing']['index_size'] = count($this->searchIndex);

        return $this;
    }

    /**
     * Perform a search query.
     *
     * @param string $query   Search query
     * @param array  $options Search options
     * @return array<string, mixed>
     */
    public function search(string $query, array $options = []): array
    {
        $startTime = microtime(true);
        $this->statistics['searches']['total_searches']++;

        try {
            // Validate query
            if (empty(trim($query))) {
                throw new Exception('Search query cannot be empty');
            }

            // Apply filters
            $filteredResults = $this->applyFilters($options);

            // Perform search
            $searchResults = $this->performSearch($query, $filteredResults, $options);

            // Sort results by relevance
            $sortedResults = $this->sortResultsByRelevance($searchResults);

            // Apply pagination
            $paginatedResults = $this->applyPagination($sortedResults, $options);

            // Update statistics
            $searchTime = microtime(true) - $startTime;
            $this->updateSearchStatistics($searchTime, count($paginatedResults['results']));

            $this->logger->info("Search completed successfully: '{$query}' returned " . count($paginatedResults['results']) . " results");

            return [
                'query' => $query,
                'results' => $paginatedResults['results'],
                'total_results' => $paginatedResults['total'],
                'page' => $paginatedResults['page'],
                'per_page' => $paginatedResults['per_page'],
                'total_pages' => $paginatedResults['total_pages'],
                'search_time' => $searchTime,
                'filters_applied' => $options['filters'] ?? [],
                'suggestions' => $this->generateSearchSuggestions($query)
            ];

        } catch (Exception $e) {
            $this->statistics['searches']['failed_searches']++;
            $this->logger->error("Search failed: '{$query}' - " . $e->getMessage());
            
            return [
                'query' => $query,
                'results' => [],
                'error' => $e->getMessage(),
                'search_time' => microtime(true) - $startTime
            ];
        }
    }

    /**
     * Apply search filters.
     *
     * @param array $options Search options
     * @return array<string, mixed>
     */
    protected function applyFilters(array $options): array
    {
        $filteredIndex = $this->searchIndex;

        // Filter by content type
        if (isset($options['filters']['content_type'])) {
            $contentType = $options['filters']['content_type'];
            $filteredIndex = array_filter($filteredIndex, function($category) use ($contentType) {
                return isset($category[$contentType]) || $category === $contentType;
            });
        }

        // Filter by language
        if (isset($options['filters']['language'])) {
            $language = $options['filters']['language'];
            $filteredIndex = $this->filterByLanguage($filteredIndex, $language);
        }

        // Filter by authenticity (for hadith)
        if (isset($options['filters']['authenticity'])) {
            $authenticity = $options['filters']['authenticity'];
            $filteredIndex = $this->filterByAuthenticity($filteredIndex, $authenticity);
        }

        return $filteredIndex;
    }

    /**
     * Filter by language.
     *
     * @param array  $index    Search index
     * @param string $language Language to filter by
     * @return array<string, mixed>
     */
    protected function filterByLanguage(array $index, string $language): array
    {
        $filtered = [];
        
        foreach ($index as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if (isset($item['language']) && $item['language'] === $language) {
                        if (!isset($filtered[$category])) {
                            $filtered[$category] = [];
                        }
                        $filtered[$category][$key] = $item;
                    }
                }
            }
        }

        return $filtered;
    }

    /**
     * Filter by authenticity.
     *
     * @param array  $index        Search index
     * @param string $authenticity Authenticity level
     * @return array<string, mixed>
     */
    protected function filterByAuthenticity(array $index, string $authenticity): array
    {
        $filtered = [];
        
        foreach ($index as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if (isset($item['authenticity']) && $item['authenticity'] === $authenticity) {
                        if (!isset($filtered[$category])) {
                            $filtered[$category] = [];
                        }
                        $filtered[$category][$key] = $item;
                    }
                }
            }
        }

        return $filtered;
    }

    /**
     * Perform the actual search.
     *
     * @param string $query           Search query
     * @param array $filteredIndex   Filtered search index
     * @param array $options         Search options
     * @return array<string, mixed>
     */
    protected function performSearch(string $query, array $filteredIndex, array $options): array
    {
        $results = [];
        $query = strtolower($query);
        $queryWords = explode(' ', $query);

        foreach ($filteredIndex as $category => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    $score = $this->calculateRelevanceScore($item, $queryWords, $options);
                    
                    if ($score > 0) {
                        $results[] = [
                            'id' => $key,
                            'category' => $category,
                            'data' => $item,
                            'relevance_score' => $score,
                            'matched_terms' => $this->findMatchedTerms($item, $queryWords)
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Calculate relevance score for a document.
     *
     * @param array $item        Document item
     * @param array $queryWords  Query words
     * @param array $options     Search options
     * @return float
     */
    protected function calculateRelevanceScore(array $item, array $queryWords, array $options): float
    {
        $score = 0.0;

        // Title match (highest weight)
        if (isset($item['title'])) {
            $title = strtolower($item['title']);
            foreach ($queryWords as $word) {
                if (strpos($title, $word) !== false) {
                    $score += 10.0;
                }
            }
        }

        // Content match (medium weight)
        if (isset($item['content'])) {
            $content = strtolower($item['content']);
            foreach ($queryWords as $word) {
                if (strpos($content, $word) !== false) {
                    $score += 5.0;
                }
            }
        }

        // Keywords match (high weight)
        if (isset($item['keywords'])) {
            foreach ($item['keywords'] as $keyword) {
                $keyword = strtolower($keyword);
                foreach ($queryWords as $word) {
                    if (strpos($keyword, $word) !== false || strpos($word, $keyword) !== false) {
                        $score += 8.0;
                    }
                }
            }
        }

        // Tags match (medium weight)
        if (isset($item['tags'])) {
            foreach ($item['tags'] as $tag) {
                $tag = strtolower($tag);
                foreach ($queryWords as $word) {
                    if (strpos($tag, $word) !== false || strpos($word, $tag) !== false) {
                        $score += 6.0;
                    }
                }
            }
        }

        // Arabic text match (high weight for Islamic content)
        if (isset($item['arabic'])) {
            $arabic = $item['arabic'];
            foreach ($queryWords as $word) {
                if (strpos($arabic, $word) !== false) {
                    $score += 7.0;
                }
            }
        }

        // Boost score for exact matches
        if (isset($item['title']) && strtolower($item['title']) === strtolower(implode(' ', $queryWords))) {
            $score += 20.0;
        }

        return $score;
    }

    /**
     * Find matched terms in a document.
     *
     * @param array $item       Document item
     * @param array $queryWords Query words
     * @return array<string>
     */
    protected function findMatchedTerms(array $item, array $queryWords): array
    {
        $matchedTerms = [];

        foreach ($queryWords as $word) {
            if (isset($item['title']) && stripos($item['title'], $word) !== false) {
                $matchedTerms[] = $word;
            }
            if (isset($item['content']) && stripos($item['content'], $word) !== false) {
                $matchedTerms[] = $word;
            }
            if (isset($item['keywords'])) {
                foreach ($item['keywords'] as $keyword) {
                    if (stripos($keyword, $word) !== false) {
                        $matchedTerms[] = $word;
                    }
                }
            }
        }

        return array_unique($matchedTerms);
    }

    /**
     * Sort results by relevance score.
     *
     * @param array $results Search results
     * @return array<string, mixed>
     */
    protected function sortResultsByRelevance(array $results): array
    {
        usort($results, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return $results;
    }

    /**
     * Apply pagination to results.
     *
     * @param array $results Search results
     * @param array $options Search options
     * @return array<string, mixed>
     */
    protected function applyPagination(array $results, array $options): array
    {
        $page = $options['page'] ?? 1;
        $perPage = $options['per_page'] ?? 10;
        $total = count($results);

        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($results, $offset, $perPage);

        return [
            'results' => $paginatedResults,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Generate search suggestions.
     *
     * @param string $query Search query
     * @return array<string>
     */
    protected function generateSearchSuggestions(string $query): array
    {
        $suggestions = [];
        $query = strtolower($query);

        // Generate suggestions based on common Islamic terms
        $commonTerms = [
            'quran', 'hadith', 'salah', 'zakat', 'hajj', 'ramadan',
            'tawheed', 'shirk', 'halal', 'haram', 'sunnah', 'bidah',
            'fiqh', 'aqeedah', 'seerah', 'sahabah', 'tabiun'
        ];

        foreach ($commonTerms as $term) {
            if (strpos($term, $query) !== false && $term !== $query) {
                $suggestions[] = $term;
            }
        }

        // Limit suggestions
        return array_slice($suggestions, 0, 5);
    }

    /**
     * Update search statistics.
     *
     * @param float $searchTime Search time
     * @param int   $resultCount Result count
     * @return self
     */
    protected function updateSearchStatistics(float $searchTime, int $resultCount): self
    {
        $this->statistics['searches']['successful_searches']++;
        $this->statistics['results']['total_results_returned'] += $resultCount;
        $this->statistics['performance']['total_search_time'] += $searchTime;

        // Update average search time
        $totalSearches = $this->statistics['searches']['successful_searches'];
        $this->statistics['searches']['average_search_time'] = 
            $this->statistics['performance']['total_search_time'] / $totalSearches;

        // Update fastest/slowest search
        if ($searchTime < $this->statistics['performance']['fastest_search']) {
            $this->statistics['performance']['fastest_search'] = $searchTime;
        }
        if ($searchTime > $this->statistics['performance']['slowest_search']) {
            $this->statistics['performance']['slowest_search'] = $searchTime;
        }

        // Update average results per search
        $this->statistics['results']['average_results_per_search'] = 
            $this->statistics['results']['total_results_returned'] / $totalSearches;

        // Track zero result searches
        if ($resultCount === 0) {
            $this->statistics['results']['zero_result_searches']++;
        }

        return $this;
    }

    /**
     * Index a new document.
     *
     * @param string $id       Document ID
     * @param array  $document Document data
     * @return bool
     */
    public function indexDocument(string $id, array $document): bool
    {
        try {
            $this->searchIndex[$id] = $document;
            $this->statistics['indexing']['total_documents_indexed']++;
            $this->statistics['indexing']['index_updates']++;
            $this->statistics['indexing']['index_size'] = count($this->searchIndex);

            $this->logger->info("Document indexed successfully: {$id}");
            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to index document {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a document from the index.
     *
     * @param string $id Document ID
     * @return bool
     */
    public function removeDocument(string $id): bool
    {
        if (isset($this->searchIndex[$id])) {
            unset($this->searchIndex[$id]);
            $this->statistics['indexing']['index_updates']++;
            $this->statistics['indexing']['index_size'] = count($this->searchIndex);

            $this->logger->info("Document removed from index: {$id}");
            return true;
        }

        return false;
    }

    /**
     * Get search statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get available filters.
     *
     * @return array<string, array>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get search index size.
     *
     * @return int
     */
    public function getIndexSize(): int
    {
        return $this->statistics['indexing']['index_size'];
    }

    /**
     * Clear search statistics.
     *
     * @return self
     */
    public function clearStatistics(): self
    {
        $this->initializeStatistics();
        return $this;
    }

    /**
     * Get search configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set search configuration.
     *
     * @param array<string, mixed> $config Search configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
