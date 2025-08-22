<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Intelligent Content Crawler Service
 * Automatically discovers and indexes new Islamic content
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ContentCrawler
{
    private Connection $db;
    private LoggerInterface $logger;
    private SearchIndexer $indexer;
    private array $contentSources;

    public function __construct(
        Connection $db,
        LoggerInterface $logger,
        SearchIndexer $indexer
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->indexer = $indexer;
        $this->initializeContentSources();
    }

    /**
     * Initialize content sources for crawling
     */
    private function initializeContentSources(): void
    {
        $this->contentSources = [
            'wiki' => [
                'base_url' => '/wiki',
                'content_pattern' => '/wiki/([^/]+)',
                'priority' => 'high',
                'update_frequency' => 'daily'
            ],
            'quran' => [
                'base_url' => '/quran',
                'content_pattern' => '/quran/([^/]+)',
                'priority' => 'high',
                'update_frequency' => 'weekly'
            ],
            'hadith' => [
                'base_url' => '/hadith',
                'content_pattern' => '/hadith/([^/]+)',
                'priority' => 'high',
                'update_frequency' => 'weekly'
            ],
            'articles' => [
                'base_url' => '/articles',
                'content_pattern' => '/articles/([^/]+)',
                'priority' => 'medium',
                'update_frequency' => 'daily'
            ],
            'scholars' => [
                'base_url' => '/scholars',
                'content_pattern' => '/scholars/([^/]+)',
                'priority' => 'medium',
                'update_frequency' => 'monthly'
            ]
        ];
    }

    /**
     * Start content crawling process
     */
    public function startCrawling(): array
    {
        $this->logger->info('Starting content crawling process');
        
        $crawlResults = [
            'start_time' => microtime(true),
            'sources_processed' => 0,
            'content_discovered' => 0,
            'content_indexed' => 0,
            'errors' => []
        ];

        try {
            foreach ($this->contentSources as $sourceType => $sourceConfig) {
                $this->logger->info("Crawling content source: {$sourceType}");
                
                try {
                    $sourceResults = $this->crawlContentSource($sourceType, $sourceConfig);
                    $crawlResults['sources_processed']++;
                    $crawlResults['content_discovered'] += $sourceResults['discovered'];
                    $crawlResults['content_indexed'] += $sourceResults['indexed'];
                    
                } catch (\Exception $e) {
                    $error = "Failed to crawl {$sourceType}: " . $e->getMessage();
                    $crawlResults['errors'][] = $error;
                    $this->logger->error($error);
                }
            }
            
            $crawlResults['end_time'] = microtime(true);
            $crawlResults['duration'] = $crawlResults['end_time'] - $crawlResults['start_time'];
            
            $this->logger->info('Content crawling completed', $crawlResults);
            
            // Update crawling statistics
            $this->updateCrawlingStatistics($crawlResults);
            
        } catch (\Exception $e) {
            $error = 'Content crawling process failed: ' . $e->getMessage();
            $crawlResults['errors'][] = $error;
            $this->logger->error($error);
        }

        return $crawlResults;
    }

    /**
     * Crawl a specific content source
     */
    private function crawlContentSource(string $sourceType, array $sourceConfig): array
    {
        $results = [
            'discovered' => 0,
            'indexed' => 0,
            'updated' => 0,
            'errors' => []
        ];

        try {
            // Discover content URLs
            $contentUrls = $this->discoverContentUrls($sourceType, $sourceConfig);
            $results['discovered'] = count($contentUrls);
            
            $this->logger->info("Discovered {$results['discovered']} content items for {$sourceType}");
            
            // Process each content item
            foreach ($contentUrls as $url) {
                try {
                    $contentData = $this->extractContentData($url, $sourceType);
                    
                    if ($contentData) {
                        $indexResult = $this->indexer->indexContent($contentData);
                        
                        if ($indexResult['success']) {
                            if ($indexResult['action'] === 'created') {
                                $results['indexed']++;
                            } else {
                                $results['updated']++;
                            }
                        }
                    }
                    
                } catch (\Exception $e) {
                    $error = "Failed to process {$url}: " . $e->getMessage();
                    $results['errors'][] = $error;
                    $this->logger->warning($error);
                }
            }
            
        } catch (\Exception $e) {
            $error = "Failed to crawl content source {$sourceType}: " . $e->getMessage();
            $results['errors'][] = $error;
            $this->logger->error($error);
        }

        return $results;
    }

    /**
     * Discover content URLs for a source type
     */
    private function discoverContentUrls(string $sourceType, array $sourceConfig): array
    {
        $urls = [];
        
        try {
            switch ($sourceType) {
                case 'wiki':
                    $urls = $this->discoverWikiPages();
                    break;
                    
                case 'quran':
                    $urls = $this->discoverQuranContent();
                    break;
                    
                case 'hadith':
                    $urls = $this->discoverHadithContent();
                    break;
                    
                case 'articles':
                    $urls = $this->discoverArticles();
                    break;
                    
                case 'scholars':
                    $urls = $this->discoverScholarProfiles();
                    break;
                    
                default:
                    $this->logger->warning("Unknown content source type: {$sourceType}");
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover URLs for {$sourceType}: " . $e->getMessage());
        }

        return $urls;
    }

    /**
     * Discover wiki pages
     */
    private function discoverWikiPages(): array
    {
        try {
            // Query existing wiki pages from database
            $sql = "
                SELECT DISTINCT slug, title, last_updated
                FROM wiki_pages 
                WHERE is_active = TRUE 
                AND last_updated >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY last_updated DESC
            ";
            
            $pages = $this->db->query($sql);
            
            $urls = [];
            foreach ($pages as $page) {
                $urls[] = [
                    'url' => "/wiki/{$page['slug']}",
                    'title' => $page['title'],
                    'last_updated' => $page['last_updated'],
                    'type' => 'wiki'
                ];
            }
            
            return $urls;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover wiki pages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover Quran content
     */
    private function discoverQuranContent(): array
    {
        try {
            // Query existing Quran content from database
            $sql = "
                SELECT DISTINCT surah_number, surah_name, ayah_number, last_updated
                FROM quran_content 
                WHERE is_active = TRUE 
                AND last_updated >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY surah_number, ayah_number
            ";
            
            $quranContent = $this->db->query($sql);
            
            $urls = [];
            foreach ($quranContent as $content) {
                $urls[] = [
                    'url' => "/quran/{$content['surah_number']}/{$content['ayah_number']}",
                    'title' => "Surah {$content['surah_name']} - Ayah {$content['ayah_number']}",
                    'last_updated' => $content['last_updated'],
                    'type' => 'quran'
                ];
            }
            
            return $urls;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover Quran content: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover Hadith content
     */
    private function discoverHadithContent(): array
    {
        try {
            // Query existing Hadith content from database
            $sql = "
                SELECT DISTINCT hadith_id, title, collection_name, last_updated
                FROM hadith_content 
                WHERE is_active = TRUE 
                AND last_updated >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY last_updated DESC
            ";
            
            $hadithContent = $this->db->query($sql);
            
            $urls = [];
            foreach ($hadithContent as $content) {
                $urls[] = [
                    'url' => "/hadith/{$content['hadith_id']}",
                    'title' => $content['title'],
                    'last_updated' => $content['last_updated'],
                    'type' => 'hadith'
                ];
            }
            
            return $urls;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover Hadith content: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover articles
     */
    private function discoverArticles(): array
    {
        try {
            // Query existing articles from database
            $sql = "
                SELECT DISTINCT slug, title, author, publish_date, last_updated
                FROM articles 
                WHERE is_active = TRUE 
                AND publish_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                ORDER BY publish_date DESC
            ";
            
            $articles = $this->db->query($sql);
            
            $urls = [];
            foreach ($articles as $article) {
                $urls[] = [
                    'url' => "/articles/{$article['slug']}",
                    'title' => $article['title'],
                    'last_updated' => $article['last_updated'],
                    'type' => 'article'
                ];
            }
            
            return $urls;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover articles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Discover scholar profiles
     */
    private function discoverScholarProfiles(): array
    {
        try {
            // Query existing scholar profiles from database
            $sql = "
                SELECT DISTINCT scholar_id, name, specialization, last_updated
                FROM scholars 
                WHERE is_active = TRUE 
                AND last_updated >= DATE_SUB(NOW(), INTERVAL 180 DAY)
                ORDER BY last_updated DESC
            ";
            
            $scholars = $this->db->query($sql);
            
            $urls = [];
            foreach ($scholars as $scholar) {
                $urls[] = [
                    'url' => "/scholars/{$scholar['scholar_id']}",
                    'title' => $scholar['name'],
                    'last_updated' => $scholar['last_updated'],
                    'type' => 'scholar'
                ];
            }
            
            return $urls;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to discover scholar profiles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract content data from a URL
     */
    private function extractContentData(array $urlInfo, string $sourceType): ?array
    {
        try {
            $url = $urlInfo['url'];
            $title = $urlInfo['title'];
            $lastUpdated = $urlInfo['last_updated'];
            
            // Extract content based on source type
            $content = $this->extractContentByType($url, $sourceType);
            
            if (!$content) {
                return null;
            }
            
            return [
                'title' => $title,
                'content' => $content,
                'url' => $url,
                'type' => $sourceType,
                'last_updated' => $lastUpdated,
                'crawl_timestamp' => date('Y-m-d H:i:s'),
                'content_length' => strlen($content),
                'word_count' => str_word_count($content)
            ];
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract content from {$urlInfo['url']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract content based on source type
     */
    private function extractContentByType(string $url, string $sourceType): ?string
    {
        try {
            switch ($sourceType) {
                case 'wiki':
                    return $this->extractWikiContent($url);
                    
                case 'quran':
                    return $this->extractQuranContent($url);
                    
                case 'hadith':
                    return $this->extractHadithContent($url);
                    
                case 'article':
                    return $this->extractArticleContent($url);
                    
                case 'scholar':
                    return $this->extractScholarContent($url);
                    
                default:
                    $this->logger->warning("Unknown source type for content extraction: {$sourceType}");
                    return null;
            }
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract content by type {$sourceType}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract wiki content
     */
    private function extractWikiContent(string $url): ?string
    {
        try {
            // Extract slug from URL
            $slug = basename($url);
            
            // Query wiki content from database
            $sql = "SELECT content, excerpt FROM wiki_pages WHERE slug = ? AND is_active = TRUE";
            $page = $this->db->queryOne($sql, [$slug]);
            
            if ($page) {
                return $page['content'] . "\n\n" . $page['excerpt'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract wiki content from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract Quran content
     */
    private function extractQuranContent(string $url): ?string
    {
        try {
            // Extract surah and ayah from URL
            $parts = explode('/', trim($url, '/'));
            $surahNumber = $parts[1] ?? null;
            $ayahNumber = $parts[2] ?? null;
            
            if (!$surahNumber || !$ayahNumber) {
                return null;
            }
            
            // Query Quran content from database
            $sql = "SELECT arabic_text, translation, tafsir FROM quran_content WHERE surah_number = ? AND ayah_number = ? AND is_active = TRUE";
            $ayah = $this->db->queryOne($sql, [$surahNumber, $ayahNumber]);
            
            if ($ayah) {
                return $ayah['arabic_text'] . "\n\n" . $ayah['translation'] . "\n\n" . $ayah['tafsir'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract Quran content from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract Hadith content
     */
    private function extractHadithContent(string $url): ?string
    {
        try {
            // Extract hadith ID from URL
            $hadithId = basename($url);
            
            // Query Hadith content from database
            $sql = "SELECT arabic_text, translation, chain_of_narration, commentary FROM hadith_content WHERE hadith_id = ? AND is_active = TRUE";
            $hadith = $this->db->queryOne($sql, [$hadithId]);
            
            if ($hadith) {
                return $hadith['arabic_text'] . "\n\n" . $hadith['translation'] . "\n\n" . $hadith['chain_of_narration'] . "\n\n" . $hadith['commentary'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract Hadith content from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract article content
     */
    private function extractArticleContent(string $url): ?string
    {
        try {
            // Extract slug from URL
            $slug = basename($url);
            
            // Query article content from database
            $sql = "SELECT content, excerpt, tags FROM articles WHERE slug = ? AND is_active = TRUE";
            $article = $this->db->queryOne($sql, [$slug]);
            
            if ($article) {
                return $article['content'] . "\n\n" . $article['excerpt'] . "\n\n" . $article['tags'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract article content from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract scholar content
     */
    private function extractScholarContent(string $url): ?string
    {
        try {
            // Extract scholar ID from URL
            $scholarId = basename($url);
            
            // Query scholar content from database
            $sql = "SELECT biography, specializations, publications, achievements FROM scholars WHERE scholar_id = ? AND is_active = TRUE";
            $scholar = $this->db->queryOne($sql, [$scholarId]);
            
            if ($scholar) {
                return $scholar['biography'] . "\n\n" . $scholar['specializations'] . "\n\n" . $scholar['publications'] . "\n\n" . $scholar['achievements'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to extract scholar content from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update crawling statistics
     */
    private function updateCrawlingStatistics(array $crawlResults): void
    {
        try {
            $sql = "
                INSERT INTO iqra_search_performance (
                    metric_date, total_searches, unique_users, avg_response_time,
                    avg_results_count, search_success_rate, click_through_rate,
                    crawl_content_discovered, crawl_content_indexed
                ) VALUES (
                    CURDATE(), 0, 0, 0.000, 0.00, 0.00, 0.00, ?, ?
                ) ON DUPLICATE KEY UPDATE
                    crawl_content_discovered = VALUES(crawl_content_discovered),
                    crawl_content_indexed = VALUES(crawl_content_indexed)
            ";
            
            $this->db->execute($sql, [
                $crawlResults['content_discovered'],
                $crawlResults['content_indexed']
            ]);
            
            $this->logger->info('Crawling statistics updated successfully');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to update crawling statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get crawling status and statistics
     */
    public function getCrawlingStatus(): array
    {
        try {
            $sql = "
                SELECT 
                    metric_date,
                    crawl_content_discovered,
                    crawl_content_indexed,
                    total_searches,
                    avg_response_time
                FROM iqra_search_performance 
                WHERE metric_date = CURDATE()
            ";
            
            $status = $this->db->queryOne($sql);
            
            if ($status) {
                return [
                    'date' => $status['metric_date'],
                    'content_discovered' => $status['crawl_content_discovered'] ?? 0,
                    'content_indexed' => $status['crawl_content_indexed'] ?? 0,
                    'total_searches' => $status['total_searches'] ?? 0,
                    'avg_response_time' => $status['avg_response_time'] ?? 0,
                    'last_crawl' => 'Today'
                ];
            }
            
            return [
                'date' => date('Y-m-d'),
                'content_discovered' => 0,
                'content_indexed' => 0,
                'total_searches' => 0,
                'avg_response_time' => 0,
                'last_crawl' => 'Never'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get crawling status: ' . $e->getMessage());
            
            return [
                'error' => $e->getMessage(),
                'date' => date('Y-m-d'),
                'content_discovered' => 0,
                'content_indexed' => 0,
                'total_searches' => 0,
                'avg_response_time' => 0,
                'last_crawl' => 'Error'
            ];
        }
    }
} 