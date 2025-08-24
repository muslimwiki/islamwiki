<?php

/**
 * Hadith Controller
 *
 * Handles HTTP requests for Hadith content and collections.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Hadith Controller - Handles Hadith Content Functionality
 */
class HadithController extends Controller
{
    /**
     * Display Hadith index page
     */
    public function index(Request $request): Response
    {
        try {
            $stats = $this->getHadithStatistics();
            $collections = $this->getHadithCollections();
            $randomHadith = $this->getRandomHadith();

            return $this->view('hadith/index', [
                'title' => 'Hadith - IslamWiki',
                'stats' => $stats,
                'collections' => $collections,
                'random_hadith' => $randomHadith
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display specific Hadith page
     */
    public function show(Request $request, int $collectionId, string $hadithNumber): Response
    {
        try {
            $hadithData = $this->getHadithByReference($collectionId, $hadithNumber);
            $collectionInfo = $this->getCollectionInfo($collectionId);
            
            if (!$hadithData) {
                $collectionName = $collectionInfo['name'] ?? "Collection {$collectionId}";
                $data = [
                    'title' => "Hadith {$collectionName} {$hadithNumber} - IslamWiki",
                    'hadith' => null,
                    'chain' => [],
                    'commentary' => null,
                    'collection_id' => $collectionId,
                    'hadith_number' => $hadithNumber
                ];
            } else {
                $chain = $this->getHadithChain($hadithData['id']);
                $commentary = $this->getHadithCommentary($hadithData['id'], 'en');

                $data = [
                    'title' => "Hadith {$hadithData['collection_name']} {$hadithData['hadith_number']} - IslamWiki",
                    'hadith' => $hadithData,
                    'chain' => $chain,
                    'commentary' => $commentary,
                    'collection_id' => $hadithData['collection_id'],
                    'hadith_number' => $hadithData['hadith_number']
                ];
            }

            return $this->view('hadith/hadith', $data, 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display collection page
     */
    public function collection(Request $request, int $collection): Response
    {
        try {
            $hadiths = $this->getHadithsByCollection($collection);
            $collectionInfo = $this->getCollectionInfo($collection);

            if (!$collectionInfo) {
                return new Response(404, [], 'Collection not found');
            }

            return $this->view('hadith/collection', [
                'title' => "Hadith Collection - {$collectionInfo['name']} - IslamWiki",
                'hadiths' => $hadiths,
                'collection' => $collectionInfo
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Search Hadith
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $collection = $request->getQueryParams()['collection'] ?? '';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $limit = 20;

            if (empty($query)) {
                return $this->view('hadith/search', [
                    'query' => '',
                    'results' => [],
                    'title' => 'Search Hadith - IslamWiki'
                ], 200);
            }

            $results = $this->searchHadiths($query, $collection, $page, $limit);
            $totalResults = $this->getTotalSearchResults($query, $collection);

            return $this->view('hadith/search', [
                'query' => $query,
                'collection' => $collection,
                'results' => $results,
                'totalResults' => $totalResults,
                'currentPage' => $page,
                'totalPages' => ceil($totalResults / $limit),
                'title' => "Search Hadith: {$query} - IslamWiki"
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get Hadith statistics
     */
    private function getHadithStatistics(): array
    {
        try {
            $sql = "SELECT COUNT(*) as total_hadith FROM hadiths";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            $totalHadith = (int)($result['total_hadith'] ?? 0);
            
            return [
                'total_hadith' => $totalHadith,
                'total_collections' => 0,
                'total_narrators' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'total_hadith' => 0,
                'total_collections' => 0,
                'total_narrators' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get Hadith collections
     */
    private function getHadithCollections(): array
    {
        try {
            $sql = "SELECT id, name, description, total_hadith FROM hadith_collections ORDER BY name";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get random Hadith
     */
    private function getRandomHadith(): ?array
    {
        try {
            $sql = "SELECT h.*, c.name as collection_name FROM hadiths h 
                    JOIN hadith_collections c ON h.collection_id = c.id 
                    ORDER BY RAND() LIMIT 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Hadith by reference
     */
    private function getHadithByReference(int $collectionId, string $hadithNumber): ?array
    {
        try {
            $sql = "SELECT h.*, c.name as collection_name FROM hadiths h 
                    JOIN hadith_collections c ON h.collection_id = c.id 
                    WHERE h.collection_id = ? AND h.hadith_number = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$collectionId, $hadithNumber]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get collection info
     */
    private function getCollectionInfo(int $collectionId): ?array
    {
        try {
            $sql = "SELECT id, name, description, total_hadith FROM hadith_collections WHERE id = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$collectionId]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Hadith chain
     */
    private function getHadithChain(int $hadithId): array
    {
        try {
            $sql = "SELECT narrator_name, narrator_level FROM hadith_narrators 
                    WHERE hadith_id = ? ORDER BY level";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$hadithId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Hadith commentary
     */
    private function getHadithCommentary(int $hadithId, string $language): ?array
    {
        try {
            $sql = "SELECT commentary_text, commentator, language FROM hadith_commentary 
                    WHERE hadith_id = ? AND language = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$hadithId, $language]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Hadiths by collection
     */
    private function getHadithsByCollection(int $collectionId): array
    {
        try {
            $sql = "SELECT id, hadith_number, title, narrator, grade FROM hadiths 
                    WHERE collection_id = ? ORDER BY hadith_number";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$collectionId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search Hadiths
     */
    private function searchHadiths(string $query, string $collection, int $page, int $limit): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT h.*, c.name as collection_name FROM hadiths h 
                    JOIN hadith_collections c ON h.collection_id = c.id 
                    WHERE (h.arabic_text LIKE ? OR h.english_text LIKE ?)";
            
            $params = [$searchTerm, $searchTerm];
            
            if (!empty($collection)) {
                $sql .= " AND c.name = ?";
                $params[] = $collection;
            }
            
            $sql .= " ORDER BY h.hadith_number LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total search results
     */
    private function getTotalSearchResults(string $query, string $collection): int
    {
        try {
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT COUNT(*) as count FROM hadiths h 
                    JOIN hadith_collections c ON h.collection_id = c.id 
                    WHERE (h.arabic_text LIKE ? OR h.english_text LIKE ?)";
            
            $params = [$searchTerm, $searchTerm];
            
            if (!empty($collection)) {
                $sql .= " AND c.name = ?";
                $params[] = $collection;
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
