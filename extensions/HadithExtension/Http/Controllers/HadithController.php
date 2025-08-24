<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\JsonResponse;
use IslamWiki\Core\Database\Connection;
use Container;\Container
use IslamWiki\Extensions\HadithExtension\Models\HadithCollection;
use IslamWiki\Extensions\HadithExtension\Models\HadithBook;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarration;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarrator;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * HadithController
 * 
 * Handles all web requests related to hadith functionality
 */
class HadithController extends BaseController
{
    private $collectionModel;
    private $bookModel;
    private $hadithModel;
    private $narratorModel;
    private $logger;
    
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        
        $this->collectionModel = $container->get(HadithCollection::class);
        $this->bookModel = $container->get(HadithBook::class);
        $this->hadithModel = $container->get(HadithNarration::class);
        $this->narratorModel = $container->get(HadithNarrator::class);
        $this->logger = $container->has('logger') ? $container->get('logger') : null;
    }
    
    public function index(Request $request): Response
    {
        try {
            $collections = $this->collectionModel->getActiveCollections();
            $featuredHadith = $this->getRandomHadith();
            
            return $this->view('hadith.index', [
                'title' => 'Hadith Collections',
                'collections' => $collections,
                'featuredHadith' => $featuredHadith,
            ]);
            
        } catch (Exception $e) {
            $this->logError('Error loading hadith index', $e);
            return $this->errorResponse('Unable to load hadith collections.');
        }
    }
    
    public function collection(Request $request, string $collectionSlug): Response
    {
        try {
            $collection = $this->getCollectionBySlug($collectionSlug);
            if (!$collection) return $this->notFound('Collection not found');
            
            $books = $collection->books();
            
            return $this->view('hadith.collection', [
                'title' => $collection->name,
                'collection' => $collection,
                'books' => $books,
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading collection: $collectionSlug", $e);
            return $this->errorResponse('Error loading collection');
        }
    }
    
    public function book(Request $request, string $collectionSlug, string $bookSlug): Response
    {
        try {
            $collection = $this->getCollectionBySlug($collectionSlug);
            if (!$collection) return $this->notFound('Collection not found');
            
            $book = $this->getBookBySlug($collection->id, $bookSlug);
            if (!$book) return $this->notFound('Book not found');
            
            $page = max(1, (int)$request->query('page', 1));
            $perPage = config('hadith.per_page', 20);
            $offset = ($page - 1) * $perPage;
            
            $hadith = $book->hadith($perPage, $offset);
            $totalHadith = $book->getHadithCount();
            
            return $this->view('hadith.book', [
                'title' => "{$book->name} - {$collection->name}",
                'collection' => $collection,
                'book' => $book,
                'hadith' => $hadith,
                'currentPage' => $page,
                'totalPages' => ceil($totalHadith / $perPage),
                'totalHadith' => $totalHadith,
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading book: $collectionSlug/$bookSlug", $e);
            return $this->errorResponse('Error loading book');
        }
    }
    
    public function show(Request $request, string $collectionSlug, string $hadithNumber): Response
    {
        try {
            $collection = $this->getCollectionBySlug($collectionSlug);
            if (!$collection) return $this->notFound('Collection not found');
            
            $hadith = $this->hadithModel->findByCollectionAndNumber($collection->id, $hadithNumber) 
                ?: $this->hadithModel->findBySecondaryNumber($collection->id, $hadithNumber);
                
            if (!$hadith) return $this->notFound('Hadith not found');
            
            $book = $hadith->book();
            $narrators = $hadith->getNarrators();
            
            return $this->view('hadith.show', [
                'title' => "Hadith #{$hadith->hadith_number} - {$collection->name}",
                'collection' => $collection,
                'book' => $book,
                'hadith' => $hadith,
                'narrators' => $narrators,
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading hadith: $collectionSlug/$hadithNumber", $e);
            return $this->errorResponse('Error loading hadith');
        }
    }
    
    public function narrator(Request $request, int $narratorId): Response
    {
        try {
            $narrator = $this->narratorModel->find($narratorId);
            if (!$narrator) return $this->notFound('Narrator not found');
            
            $page = max(1, (int)$request->query('page', 1));
            $perPage = config('hadith.per_page', 20);
            $offset = ($page - 1) * $perPage;
            
            $hadith = $narrator->narratedHadith($perPage, $offset);
            $totalHadith = $narrator->getNarratedHadithCount();
            
            return $this->view('hadith.narrator', [
                'title' => "Hadith Narrated by {$narrator->name}",
                'narrator' => $narrator,
                'hadith' => $hadith,
                'currentPage' => $page,
                'totalPages' => ceil($totalHadith / $perPage),
                'totalHadith' => $totalHadith,
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error loading narrator: $narratorId", $e);
            return $this->errorResponse('Error loading narrator');
        }
    }
    
    public function search(Request $request): Response
    {
        $query = trim($request->query('q', ''));
        $collectionId = $request->query('collection');
        $language = $request->query('lang', config('hadith.default_language', 'en'));
        
        if (mb_strlen($query) < 3) {
            return $this->view('hadith.search', [
                'title' => 'Search Hadith',
                'query' => $query,
                'collections' => $this->collectionModel->getActiveCollections(),
                'error' => 'Search query must be at least 3 characters long.',
            ]);
        }
        
        try {
            $page = max(1, (int)$request->query('page', 1));
            $perPage = config('hadith.per_page', 20);
            $offset = ($page - 1) * $perPage;
            
            $results = $this->hadithModel->search($query, $language, $collectionId, $perPage, $offset);
            $totalResults = $this->hadithModel->searchCount($query, $language, $collectionId);
            
            $data = [
                'title' => 'Search Results: ' . $query,
                'query' => $query,
                'results' => $results,
                'totalResults' => $totalResults,
                'currentPage' => $page,
                'totalPages' => ceil($totalResults / $perPage),
                'collections' => $this->collectionModel->getActiveCollections(),
                'selectedCollectionId' => $collectionId,
                'language' => $language,
            ];
            
            if ($request->wantsJson() || $request->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'data' => $data
                ]);
            }
            
            return $this->view('hadith.search', $data);
            
        } catch (Exception $e) {
            $this->logError("Error searching: $query", $e);
            
            if ($request->wantsJson() || $request->ajax()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'An error occurred while searching.'
                ], 500);
            }
            
            return $this->view('hadith.search', [
                'title' => 'Search Hadith',
                'query' => $query,
                'collections' => $this->collectionModel->getActiveCollections(),
                'error' => 'An error occurred while searching.',
            ]);
        }
    }
    
    // Helper methods
    
    private function getCollectionBySlug(string $slug): ?HadithCollection
    {
        // Simple implementation - adjust based on your slug logic
        return $this->collectionModel->findBySlug($slug) ?: $this->collectionModel->find((int)$slug);
    }
    
    private function getBookBySlug(int $collectionId, string $slug): ?HadithBook
    {
        // Simple implementation - adjust based on your slug logic
        return $this->bookModel->findBySlug($collectionId, $slug) 
            ?: $this->bookModel->findByCollectionAndNumber($collectionId, (int)$slug);
    }
    
    private function getRandomHadith(): ?object
    {
        try {
            $result = $this->db->query(
                "SELECT * FROM hadith_narrations ORDER BY RAND() LIMIT 1"
            )->fetch();
            
            if ($result) {
                $hadith = new HadithNarration($this->db);
                $hadith->fill((array)$result);
                $hadith->exists = true;
                return $hadith;
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError('Error getting random hadith', $e);
            return null;
        }
    }
    
    private function logError(string $message, Exception $e): void
    {
        if ($this->logger) {
            $this->logger->error($message, ['exception' => $e]);
        } else {
            error_log($message . ': ' . $e->getMessage());
        }
    }
}
