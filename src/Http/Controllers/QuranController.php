<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Models\QuranVerse;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * QuranController
 *
 * Handles Quran-related requests and operations for Phase 4 Islamic features integration.
 * Provides API endpoints and web interfaces for Quran functionality.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.13
 * @since Phase 4
 */
class QuranController extends Controller
{
    private $quranVerse;

    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container\Asas $container)
    {
        parent::__construct($db, $container);
        $this->quranVerse = new QuranVerse();
    }

    /**
     * Display Quran search page
     *
     * @param Request $request
     * @return Response
     */
    public function searchPage(Request $request)
    {
        $query = $request->getQuery('q', '');
        $language = $request->getQuery('lang', 'en');
        $results = [];

        if (!empty($query)) {
            $results = $this->quranVerse->search($query, $language, 20);
        }

        $data = [
            'title' => 'Quran Search - IslamWiki',
            'query' => $query,
            'language' => $language,
            'results' => $results,
            'total_results' => count($results)
        ];

        return $this->view('quran/search', $data);
    }

    /**
     * Display specific verse page
     *
     * @param Request $request
     * @param int $chapter Chapter number
     * @param int $verse Verse number
     * @return Response
     */
    public function versePage(Request $request, $chapter, $verse)
    {
        $verseData = $this->quranVerse->getByReference($chapter, $verse);

        if (!$verseData) {
            return $this->notFound('Verse not found');
        }

        // Get tafsir if available
        $tafsir = $this->quranVerse->getTafsir($verseData['id'], 'en');

        // Get recitation if available
        $recitation = $this->quranVerse->getRecitation($verseData['id']);

        $data = [
            'title' => "Quran {$chapter}:{$verse} - IslamWiki",
            'verse' => $verseData,
            'tafsir' => $tafsir,
            'recitation' => $recitation,
            'chapter' => $chapter,
            'verse_number' => $verse
        ];

        return $this->view('quran/verse', $data);
    }

    /**
     * Display chapter page
     *
     * @param Request $request
     * @param int $chapter Chapter number
     * @return Response
     */
    public function chapterPage(Request $request, $chapter)
    {
        $verses = $this->quranVerse->getChapter($chapter);

        if (empty($verses)) {
            return $this->notFound('Chapter not found');
        }

        $data = [
            'title' => "Quran Chapter {$chapter} - IslamWiki",
            'verses' => $verses,
            'chapter' => $chapter
        ];

        return $this->view('quran/chapter', $data);
    }

    /**
     * API endpoint to get verses
     *
     * @param Request $request
     * @return Response
     */
    public function apiVerses(Request $request)
    {
        $chapter = $request->getQuery('chapter');
        $verse = $request->getQuery('verse');
        $limit = (int)$request->getQuery('limit', 50);

        if ($chapter) {
            $verses = $this->quranVerse->getByChapter($chapter, $verse);
        } else {
            $verses = $this->quranVerse->getDailyVerses($limit);
        }

        $response = [
            'success' => true,
            'data' => $verses,
            'total' => count($verses)
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get specific verse
     *
     * @param Request $request
     * @param int $id Verse ID
     * @return Response
     */
    public function apiVerse(Request $request, $id)
    {
        $verse = $this->quranVerse->findById($id);

        if (!$verse) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Verse not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $verse
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to search verses
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

        $results = $this->quranVerse->search($query, $language, $limit);

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
     * API endpoint to get verse by reference
     *
     * @param Request $request
     * @param int $chapter Chapter number
     * @param int $verse Verse number
     * @return Response
     */
    public function apiVerseByReference(Request $request, $chapter, $verse)
    {
        $verseData = $this->quranVerse->getByReference($chapter, $verse);

        if (!$verseData) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Verse not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $verseData,
            'reference' => "{$chapter}:{$verse}"
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get tafsir
     *
     * @param Request $request
     * @param int $verseId Verse ID
     * @return Response
     */
    public function apiTafsir(Request $request, $verseId)
    {
        $language = $request->getQuery('lang', 'en');
        $tafsir = $this->quranVerse->getTafsir($verseId, $language);

        if (!$tafsir) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Tafsir not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $tafsir
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get recitation
     *
     * @param Request $request
     * @param int $verseId Verse ID
     * @return Response
     */
    public function apiRecitation(Request $request, $verseId)
    {
        $reciter = $request->getQuery('reciter', 'default');
        $recitation = $this->quranVerse->getRecitation($verseId, $reciter);

        if (!$recitation) {
            return new Response(
                json_encode(['success' => false, 'error' => 'Recitation not found']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $recitation
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * API endpoint to get statistics
     *
     * @param Request $request
     * @return Response
     */
    public function apiStatistics(Request $request)
    {
        $stats = $this->quranVerse->getStatistics();

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
     * API endpoint to get random verse
     *
     * @param Request $request
     * @return Response
     */
    public function apiRandomVerse(Request $request)
    {
        $verse = $this->quranVerse->getRandomVerse();

        if (!$verse) {
            return new Response(
                json_encode(['success' => false, 'error' => 'No verses available']),
                404,
                ['Content-Type' => 'application/json']
            );
        }

        $response = [
            'success' => true,
            'data' => $verse
        ];

        return new Response(
            json_encode($response),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * Widget endpoint for embedding Quran verses
     *
     * @param Request $request
     * @param int $chapter Chapter number
     * @param int $verse Verse number
     * @return Response
     */
    public function widget(Request $request, $chapter, $verse)
    {
        $verseData = $this->quranVerse->getByReference($chapter, $verse);

        if (!$verseData) {
            return $this->notFound('Verse not found');
        }

        $data = [
            'verse' => $verseData,
            'chapter' => $chapter,
            'verse_number' => $verse,
            'is_widget' => true
        ];

        return $this->view('quran/widget', $data);
    }

    /**
     * Generate Quran reference for wiki pages
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
     * Display Quran index page
     *
     * @param Request $request
     * @return Response
     */
    public function indexPage(Request $request)
    {
        $stats = $this->quranVerse->getStatistics();
        $randomVerse = $this->quranVerse->getRandomVerse();

        $data = [
            'title' => 'Quran - IslamWiki',
            'stats' => $stats,
            'random_verse' => $randomVerse
        ];

        return $this->view('quran/index', $data);
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
