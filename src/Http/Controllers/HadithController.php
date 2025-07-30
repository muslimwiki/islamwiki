<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Models\Hadith;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;

/**
 * HadithController
 * 
 * Handles Hadith-related requests and operations for Phase 4 Islamic features integration.
 * Provides API endpoints and web interfaces for Hadith functionality.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.14
 * @since Phase 4
 */
class HadithController extends Controller
{
    private $hadith;
    private $renderer;

    public function __construct()
    {
        $this->hadith = new Hadith();
        $this->renderer = new TwigRenderer();
    }

    /**
     * Display Hadith search page
     * 
     * @param Request $request
     * @return Response
     */
    public function searchPage(Request $request)
    {
        $query = $request->getQuery('q', '');
        $language = $request->getQuery('lang', 'en');
        $collection = $request->getQuery('collection', '');
        $results = [];

        if (!empty($query)) {
            $results = $this->hadith->search($query, $language, 20);
        }

        $collections = $this->hadith->getCollections();

        $data = [
            'title' => 'Hadith Search - IslamWiki',
            'query' => $query,
            'language' => $language,
            'collection' => $collection,
            'results' => $results,
            'collections' => $collections,
            'total_results' => count($results)
        ];

        $html = $this->renderer->render('hadith/search.twig', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Display specific Hadith page
     * 
     * @param Request $request
     * @param int $collectionId Collection ID
     * @param int $hadithNumber Hadith number
     * @return Response
     */
    public function hadithPage(Request $request, $collectionId, $hadithNumber)
    {
        $hadithData = $this->hadith->getByReference($collectionId, $hadithNumber);
        
        if (!$hadithData) {
            return $this->notFound('Hadith not found');
        }

        // Get chain of narrators
        $chain = $this->hadith->getChain($hadithData['id']);
        
        // Get commentary if available
        $commentary = $this->hadith->getCommentary($hadithData['id'], 'en');

        $data = [
            'title' => "Hadith {$hadithData['collection_name']} {$hadithNumber} - IslamWiki",
            'hadith' => $hadithData,
            'chain' => $chain,
            'commentary' => $commentary,
            'collection_id' => $collectionId,
            'hadith_number' => $hadithNumber
        ];

        $html = $this->renderer->render('hadith/hadith.twig', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Display collection page
     * 
     * @param Request $request
     * @param int $collectionId Collection ID
     * @return Response
     */
    public function collectionPage(Request $request, $collectionId)
    {
        $hadiths = $this->hadith->getByCollection($collectionId);
        $collectionInfo = $this->hadith->getCollectionInfo($collectionId);

        if (empty($hadiths)) {
            return $this->notFound('Collection not found');
        }

        $data = [
            'title' => "Hadith Collection {$collectionInfo['name']} - IslamWiki",
            'hadiths' => $hadiths,
            'collection_info' => $collectionInfo,
            'collection_id' => $collectionId
        ];

        $html = $this->renderer->render('hadith/collection.twig', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * API endpoint to get Hadiths
     * 
     * @param Request $request
     * @return Response
     */
    public function apiHadiths(Request $request)
    {
        $collectionId = $request->getQuery('collection');
        $limit = (int)$request->getQuery('limit', 50);

        if ($collectionId) {
            $hadiths = $this->hadith->getByCollection($collectionId, $limit);
        } else {
            $hadiths = $this->hadith->getDailyHadiths($limit);
        }

        $response = [
            'success' => true,
            'data' => $hadiths,
            'total' => count($hadiths)
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get specific Hadith
     * 
     * @param Request $request
     * @param int $id Hadith ID
     * @return Response
     */
    public function apiHadith(Request $request, $id)
    {
        $hadith = $this->hadith->findById($id);

        if (!$hadith) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Hadith not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $hadith
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to search Hadiths
     * 
     * @param Request $request
     * @return Response
     */
    public function apiSearch(Request $request)
    {
        $query = $request->getQuery('q', '');
        $language = $request->getQuery('lang', 'en');
        $limit = (int)$request->getQuery('limit', 50);

        if (empty($query)) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Search query required']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        $results = $this->hadith->search($query, $language, $limit);

        $response = [
            'success' => true,
            'data' => $results,
            'total' => count($results),
            'query' => $query
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadith by reference
     * 
     * @param Request $request
     * @param int $collectionId Collection ID
     * @param int $hadithNumber Hadith number
     * @return Response
     */
    public function apiHadithByReference(Request $request, $collectionId, $hadithNumber)
    {
        $hadithData = $this->hadith->getByReference($collectionId, $hadithNumber);

        if (!$hadithData) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Hadith not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $hadithData,
            'reference' => "{$hadithData['collection_name']} {$hadithNumber}"
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadith chain
     * 
     * @param Request $request
     * @param int $hadithId Hadith ID
     * @return Response
     */
    public function apiChain(Request $request, $hadithId)
    {
        $chain = $this->hadith->getChain($hadithId);

        $response = [
            'success' => true,
            'data' => $chain,
            'hadith_id' => $hadithId
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadith commentary
     * 
     * @param Request $request
     * @param int $hadithId Hadith ID
     * @return Response
     */
    public function apiCommentary(Request $request, $hadithId)
    {
        $language = $request->getQuery('lang', 'en');
        $commentary = $this->hadith->getCommentary($hadithId, $language);

        if (!$commentary) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Commentary not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $commentary
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadith collections
     * 
     * @param Request $request
     * @return Response
     */
    public function apiCollections(Request $request)
    {
        $collections = $this->hadith->getCollections();

        $response = [
            'success' => true,
            'data' => $collections,
            'total' => count($collections)
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadith statistics
     * 
     * @param Request $request
     * @return Response
     */
    public function apiStatistics(Request $request)
    {
        $stats = $this->hadith->getStatistics();

        $response = [
            'success' => true,
            'data' => $stats
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get random Hadith
     * 
     * @param Request $request
     * @return Response
     */
    public function apiRandomHadith(Request $request)
    {
        $hadith = $this->hadith->getRandomHadith();

        if (!$hadith) {
            return new Response(
                json_encode(['success' => false, 'error' => 'No Hadiths available']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $hadith
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get Hadiths by authenticity
     * 
     * @param Request $request
     * @param string $authenticityLevel Authenticity level
     * @return Response
     */
    public function apiByAuthenticity(Request $request, $authenticityLevel)
    {
        $limit = (int)$request->getQuery('limit', 50);
        $hadiths = $this->hadith->getByAuthenticity($authenticityLevel, $limit);

        $response = [
            'success' => true,
            'data' => $hadiths,
            'total' => count($hadiths),
            'authenticity_level' => $authenticityLevel
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Widget endpoint for embedding Hadiths
     * 
     * @param Request $request
     * @param int $collectionId Collection ID
     * @param int $hadithNumber Hadith number
     * @return Response
     */
    public function widget(Request $request, $collectionId, $hadithNumber)
    {
        $hadithData = $this->hadith->getByReference($collectionId, $hadithNumber);
        
        if (!$hadithData) {
            return $this->notFound('Hadith not found');
        }

        $data = [
            'hadith' => $hadithData,
            'collection_id' => $collectionId,
            'hadith_number' => $hadithNumber,
            'is_widget' => true
        ];

        $html = $this->renderer->render('hadith/widget.twig', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Generate Hadith reference for wiki pages
     * 
     * @param Request $request
     * @param int $pageId Wiki page ID
     * @return Response
     */
    public function apiReferences(Request $request, $pageId)
    {
        // This would integrate with the wiki page system
        // For now, return empty references
        $response = [
            'success' => true,
            'data' => [],
            'page_id' => $pageId
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Display Hadith index page
     * 
     * @param Request $request
     * @return Response
     */
    public function indexPage(Request $request)
    {
        $stats = $this->hadith->getStatistics();
        $collections = $this->hadith->getCollections();
        $randomHadith = $this->hadith->getRandomHadith();

        $data = [
            'title' => 'Hadith - IslamWiki',
            'stats' => $stats,
            'collections' => $collections,
            'random_hadith' => $randomHadith
        ];

        $html = $this->renderer->render('hadith/index.twig', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Handle 404 errors
     * 
     * @param string $message Error message
     * @return Response
     */
    private function notFound($message = 'Not Found')
    {
        return new Response(
            json_encode(['success' => false, 'error' => $message]),
            404,
            ['Content-Type' => 'application/json']
        );
    }
} 