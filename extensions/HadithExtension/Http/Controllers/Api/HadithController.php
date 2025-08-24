<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Http\Controllers\Api;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Database\Connection;
use Container;\Container
use IslamWiki\Extensions\HadithExtension\Models\HadithCollection;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarration;
use IslamWiki\Extensions\HadithExtension\Models\HadithNarrator;
use IslamWiki\Extensions\HadithExtension\Http\Controllers\BaseController;

/**
 * Api\HadithController
 * 
 * Handles API requests related to hadith functionality
 */
class HadithController extends BaseController
{
    /**
     * @var HadithCollection
     */
    private $collectionModel;
    
    /**
     * @var HadithNarration
     */
    private $hadithModel;
    
    /**
     * @var HadithNarrator
     */
    private $narratorModel;
    
    /**
     * Constructor
     */
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        
        $this->collectionModel = $container->get(HadithCollection::class);
        $this->hadithModel = $container->get(HadithNarration::class);
        $this->narratorModel = $container->get(HadithNarrator::class);
    }
    
    /**
     * Search for hadith
     */
    public function search(Request $request)
    {
        try {
            $query = trim($request->query('q', ''));
            $collectionId = $request->query('collection');
            $language = $request->query('lang', config('hadith.default_language', 'en'));
            $page = max(1, (int)$request->query('page', 1));
            $perPage = min(50, max(1, (int)$request->query('per_page', 20)));
            $offset = ($page - 1) * $perPage;
            
            // Validate query
            if (mb_strlen($query) < 3) {
                return $this->json([
                    'success' => false,
                    'error' => 'Search query must be at least 3 characters long.'
                ], 400);
            }
            
            // Perform search
            $results = $this->hadithModel->search($query, $language, $collectionId, $perPage, $offset);
            $totalResults = $this->hadithModel->searchCount($query, $language, $collectionId);
            
            // Format results
            $formattedResults = array_map(function($hadith) {
                return $this->formatHadithForApi($hadith);
            }, $results);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'results' => $formattedResults,
                    'pagination' => [
                        'total' => $totalResults,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'total_pages' => ceil($totalResults / $perPage),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while searching for hadith.'
            ], 500);
        }
    }
    
    /**
     * Get a list of hadith collections
     */
    public function collections(Request $request)
    {
        try {
            $collections = $this->collectionModel->getActiveCollections();
            
            $formattedCollections = array_map(function($collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'description' => $collection->description,
                    'total_hadith' => $collection->total_hadith,
                    'total_books' => $collection->total_books,
                ];
            }, $collections);
            
            return $this->json([
                'success' => true,
                'data' => $formattedCollections
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while fetching collections.'
            ], 500);
        }
    }
    
    /**
     * Get details of a specific collection
     */
    public function collection(Request $request, int $collectionId)
    {
        try {
            $collection = $this->collectionModel->find($collectionId);
            
            if (!$collection) {
                return $this->json([
                    'success' => false,
                    'error' => 'Collection not found.'
                ], 404);
            }
            
            // Get books in this collection
            $books = $collection->books();
            
            $formattedBooks = array_map(function($book) use ($collection) {
                return [
                    'id' => $book->id,
                    'collection_id' => $book->collection_id,
                    'name' => $book->name,
                    'slug' => $book->slug,
                    'description' => $book->description,
                    'total_hadith' => $book->getHadithCount(),
                    'url' => "/hadith/collection/{$collection->slug}/book/{$book->slug}",
                ];
            }, $books);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'slug' => $collection->slug,
                    'description' => $collection->description,
                    'total_hadith' => $collection->total_hadith,
                    'total_books' => $collection->total_books,
                    'books' => $formattedBooks,
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while fetching the collection.'
            ], 500);
        }
    }
    
    /**
     * Get details of a specific hadith
     */
    public function hadith(Request $request, int $hadithId)
    {
        try {
            $hadith = $this->hadithModel->find($hadithId);
            
            if (!$hadith) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hadith not found.'
                ], 404);
            }
            
            $collection = $hadith->collection();
            $book = $hadith->book();
            $narrators = $hadith->getNarrators();
            
            $formattedNarrators = array_map(function($narrator) {
                return [
                    'id' => $narrator->id,
                    'name' => $narrator->name,
                    'arabic_name' => $narrator->arabic_name,
                    'biography' => $narrator->biography,
                    'url' => "/hadith/narrator/{$narrator->id}",
                ];
            }, $narrators);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $hadith->id,
                    'hadith_number' => $hadith->hadith_number,
                    'secondary_number' => $hadith->secondary_number,
                    'arabic_text' => $hadith->arabic_text,
                    'english_text' => $hadith->english_text,
                    'translation' => $hadith->translation,
                    'grade' => $hadith->grade,
                    'collection' => $collection ? [
                        'id' => $collection->id,
                        'name' => $collection->name,
                        'slug' => $collection->slug,
                        'url' => "/hadith/collection/{$collection->slug}",
                    ] : null,
                    'book' => $book ? [
                        'id' => $book->id,
                        'name' => $book->name,
                        'slug' => $book->slug,
                        'url' => "/hadith/collection/{$collection->slug}/book/{$book->slug}",
                    ] : null,
                    'narrators' => $formattedNarrators,
                    'url' => "/hadith/collection/{$collection->slug}/hadith/{$hadith->hadith_number}",
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while fetching the hadith.'
            ], 500);
        }
    }
    
    /**
     * Get details of a specific narrator
     */
    public function narrator(Request $request, int $narratorId)
    {
        try {
            $narrator = $this->narratorModel->find($narratorId);
            
            if (!$narrator) {
                return $this->json([
                    'success' => false,
                    'error' => 'Narrator not found.'
                ], 404);
            }
            
            // Get paginated hadith for this narrator
            $page = max(1, (int)$request->query('page', 1));
            $perPage = min(50, max(1, (int)$request->query('per_page', 20)));
            $offset = ($page - 1) * $perPage;
            
            $hadith = $narrator->narratedHadith($perPage, $offset);
            $totalHadith = $narrator->getNarratedHadithCount();
            
            $formattedHadith = array_map(function($h) {
                return $this->formatHadithForApi($h);
            }, $hadith);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $narrator->id,
                    'name' => $narrator->name,
                    'arabic_name' => $narrator->arabic_name,
                    'biography' => $narrator->biography,
                    'birth_year' => $narrator->birth_year,
                    'death_year' => $narrator->death_year,
                    'generation' => $narrator->generation,
                    'reliability_grade' => $narrator->reliability_grade,
                    'teacher_of' => $narrator->teacher_of ? json_decode($narrator->teacher_of, true) : [],
                    'student_of' => $narrator->student_of ? json_decode($narrator->student_of, true) : [],
                    'hadith' => [
                        'data' => $formattedHadith,
                        'pagination' => [
                            'total' => $totalHadith,
                            'per_page' => $perPage,
                            'current_page' => $page,
                            'total_pages' => ceil($totalHadith / $perPage),
                        ]
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while fetching the narrator.'
            ], 500);
        }
    }
    
    /**
     * Format a hadith for API response
     */
    private function formatHadithForApi($hadith): array
    {
        $collection = $hadith->collection();
        $book = $hadith->book();
        
        return [
            'id' => $hadith->id,
            'hadith_number' => $hadith->hadith_number,
            'secondary_number' => $hadith->secondary_number,
            'arabic_text' => $hadith->arabic_text,
            'english_text' => $hadith->english_text,
            'translation' => $hadith->translation,
            'grade' => $hadith->grade,
            'collection' => $collection ? [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
            ] : null,
            'book' => $book ? [
                'id' => $book->id,
                'name' => $book->name,
                'slug' => $book->slug,
            ] : null,
            'url' => $collection ? "/hadith/collection/{$collection->slug}/hadith/{$hadith->hadith_number}" : null,
        ];
    }
}
