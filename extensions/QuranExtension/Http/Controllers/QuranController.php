<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Http\Controllers;

use IslamWiki\Extensions\QuranExtension\Models\QuranAyahRepository;
use IslamWiki\Extensions\QuranExtension\Models\QuranSurahRepository;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\Controller;
use Exception;

/**
 * QuranController
 *
 * Surah/Ayah oriented Quran browsing and APIs.
 */
class QuranController extends Controller
{
    private ?QuranAyahRepository $ayahRepository;
    private ?QuranAyahRepository $quranAyah; // backward-compat alias for older references
    private ?QuranSurahRepository $surahRepository;
    private ?\Psr\Log\LoggerInterface $logger;

    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\AsasContainer $container
    ) {
        parent::__construct($db, $container);
        
        // Initialize properties to null
        $this->ayahRepository = null;
        $this->quranAyah = null;
        $this->surahRepository = null;
        $this->logger = null;
        
        try {
            $this->logger = $container->has('logger') ? $container->get('logger') : null;
            
            // Establish the database connection before creating repositories
            $db->getPdo();
            
            $this->ayahRepository = new QuranAyahRepository($db, [], $this->logger);
            $this->quranAyah = $this->ayahRepository; // alias
            $this->surahRepository = new QuranSurahRepository($db, $this->logger);
        } catch (\Exception $e) {
            $this->logger?->error('Failed to initialize QuranController: ' . $e->getMessage());
            
            // Log the error but don't crash the controller
            error_log('QuranController initialization failed: ' . $e->getMessage());
            
            // Properties are already null, so no need to set them again
        }
    }

    /**
     * Quran home page
     * 
     * Displays the main Quran browsing interface with a random ayah, surah list, and statistics.
     * 
     * @param Request $request The HTTP request object
     * @return Response The HTTP response
     * @throws \RuntimeException If there's an error retrieving required data
     */
    public function indexPage(Request $request): Response
    {
        // Check if repositories are available
        if (!$this->ayahRepository || !$this->surahRepository) {
            return $this->view('quran/error', [
                'title' => 'Quran System Error',
                'error' => 'The Quran system is currently unavailable. Please try again later.',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'request_ip' => $this->getClientIp(),
                'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'memory_limit' => ini_get('memory_limit') ?: 'Unknown'
            ]);
        }
        
        try {
            $language = $request->getQueryParam('lang', 'en');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Get available translators with fallback
            try {
                $translators = $this->ayahRepository->getAllTranslators($language);
                
                // If no translators found for the language, try English
                if (empty($translators)) {
                    $language = 'en';
                    $translators = $this->ayahRepository->getAllTranslators($language);
                }

                // If still no translators, use a default
                if (empty($translators)) {
                    $translators = [
                        [
                            'id' => 1,
                            'name' => 'Saheeh International',
                            'translator' => 'Saheeh International',
                            'language' => 'en',
                            'translation_count' => 0
                        ]
                    ];
                    $translator = 'Saheeh International';
                }
            } catch (\Exception $e) {
                $this->logger?->error('Failed to load translators', [
                    'error' => $e->getMessage(),
                    'language' => $language
                ]);
                throw new \RuntimeException('Unable to load Quran translations. Please try again later.', 500, $e);
            }

            // Get random ayah
            $randomAyah = null;
            try {
                $randomAyah = $this->ayahRepository->getRandomAyah($language, $translator);
                
                if ($randomAyah) {
                    $randomAyah = [
                        'arabic_text' => $randomAyah['text_arabic'] ?? '',
                        'translation_text' => $randomAyah['translation'] ?? '',
                        'chapter_number' => $randomAyah['surah_number'] ?? 1,
                        'ayah_number' => $randomAyah['ayah_number'] ?? 1,
                        'translator_name' => $randomAyah['translator'] ?? 'Unknown'
                    ];
                }
            } catch (\Exception $e) {
                $this->logger?->error('Failed to load random ayah', [
                    'error' => $e->getMessage(),
                    'language' => $language,
                    'translator' => $translator
                ]);
                // Continue without random ayah
            }

            // Get statistics
            $stats = [
                'total_chapters' => 114, // Default values
                'total_ayahs' => 6236,
                'max_chapter' => 114,
                'max_ayah' => 286,
            ];
            
            try {
                $statsRow = $this->ayahRepository->getStatistics();
                if ($statsRow) {
                    $stats = [
                        'total_chapters' => (int)($statsRow['total_chapters'] ?? $stats['total_chapters']),
                        'total_ayahs' => (int)($statsRow['total_ayahs'] ?? $stats['total_ayahs']),
                        'max_chapter' => (int)($statsRow['max_chapter'] ?? $stats['max_chapter']),
                        'max_ayah' => (int)($statsRow['max_ayah'] ?? $stats['max_ayah']),
                    ];
                }
            } catch (\Exception $e) {
                $this->logger?->error('Failed to load Quran statistics', [
                    'error' => $e->getMessage()
                ]);
                // Continue with default stats
            }

            // Get surahs list
            $surahs = [];
            try {
                $surahs = $this->surahRepository->getAll();
            } catch (Exception $e) {
                error_log("Error getting surahs: " . $e->getMessage());
            }

            // Get Juz stats with fallback
            $juzStats = [];
            try {
                $juzStats = $this->ayahRepository->getJuzStats();
            } catch (Exception $e) {
                error_log("Error getting Juz stats: " . $e->getMessage());
            }

            $data = [
                'title' => 'Quran - IslamWiki',
                'language' => $language,
                'translator' => $translator,
                'translators' => $translators,
                'random_ayah' => $randomAyah,
                'stats' => $stats,
                'surahs' => $surahs,
                'juz_stats' => $juzStats,
            ];

            return $this->view('quran/index', $data);
        } catch (Exception $e) {
            error_log("Quran index page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Error',
                'error' => 'Unable to load Quran data. Please try again later.',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'request_ip' => $this->getClientIp(),
                'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'memory_limit' => ini_get('memory_limit') ?: 'Unknown'
            ]);
        }
    }

    /**
     * Quran search page
     */
    public function searchPage(Request $request)
    {
        try {
            $query = $request->getQueryParam('q', '');
            $language = $request->getQueryParam('lang', 'en');
            $translator = $request->getQueryParam('translator', 'Saheeh International');
            $type = $request->getQueryParam('type', 'all');

            $results = [];
            $totalResults = 0;

            if (!empty($query)) {
                try {
                    $results = $this->quranAyah->search($query, $language, 50, $translator);
                    $totalResults = count($results);
                } catch (Exception $e) {
                    error_log("Quran search error: " . $e->getMessage());
                }
            }

            $data = [
                'title' => 'Quran Search - IslamWiki',
                'query' => $query,
                'language' => $language,
                'translator' => $translator,
                'type' => $type,
                'results' => $results,
                'total_results' => $totalResults,
            ];

            return $this->view('quran/search', $data);
        } catch (Exception $e) {
            error_log("Quran search page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Search Error',
                'error' => 'Unable to perform search. Please try again later.',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'request_ip' => $this->getClientIp(),
                'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'memory_limit' => ini_get('memory_limit') ?: 'Unknown'
            ]);
        }
    }

    /**
     * Display specific ayah
     */
    public function ayahPage(Request $request, $surah, $ayah)
    {
        // Check if repositories are available
        if (!$this->quranAyah || !$this->surahRepository) {
            return $this->view('quran/error', [
                'title' => 'Quran System Error',
                'error' => 'The Quran system is currently unavailable. Please try again later.',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'request_ip' => $this->getClientIp(),
                'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'memory_limit' => ini_get('memory_limit') ?: 'Unknown'
            ]);
        }
        
        try {
            $language = $request->getQueryParam('lang', 'en');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert parameters to integers
            $surahNumber = (int) $surah;
            $ayahNumber = (int) $ayah;

            // Get ayah data
            $ayahData = $this->quranAyah->getByReference($surahNumber, $ayahNumber, $language, $translator);

            if (!$ayahData) {
                return $this->notFound("Ayah {$surah}:{$ayah} not found");
            }

            // Get surah info for breadcrumbs
            $surahInfo = $this->quranAyah->getSurahInfo($surahNumber);
            $surahName = $surahInfo['name'] ?? null;

            // Get previous and next ayahs for navigation
            $prevAyah = $this->quranAyah->getPreviousAyah($surahNumber, $ayahNumber, $language, $translator);
            $nextAyah = $this->quranAyah->getNextAyah($surahNumber, $ayahNumber, $language, $translator);

            // Get tafsir if available
            $tafsir = null;
            try {
                $tafsir = $this->quranAyah->getTafsir($ayahData['id'], $language);
            } catch (Exception $e) {
                // Tafsir not available, continue without it
            }

            // Get translators for the view
            $translators = $this->ayahRepository->getAllTranslators($language);

            // Prepare data for the view
            $data = [
                'title' => "{$surahName} {$surahNumber}:{$ayahNumber} - Quran - IslamWiki",
                'ayah' => [
                    'text_arabic' => $ayahData['text_arabic'] ?? '',
                    'translation_text' => $ayahData['translation'] ?? '',
                    'translator_name' => $ayahData['translator'] ?? $translator,
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayahNumber,
                ],
                'surah_name' => $surahName,
                'surah' => $surahNumber,
                'ayah_number' => $ayahNumber,
                'language' => $language,
                'translator' => $translator,
                'translators' => $translators,
                'prev_ayah' => $prevAyah ? [
                    'surah_number' => $prevAyah['surah_number'],
                    'ayah_number' => $prevAyah['ayah_number']
                ] : null,
                'next_ayah' => $nextAyah ? [
                    'surah_number' => $nextAyah['surah_number'],
                    'ayah_number' => $nextAyah['ayah_number']
                ] : null,
                'tafsir' => $tafsir,
            ];

            return $this->view('quran/ayah_new', $data);
        } catch (Exception $e) {
            error_log("Quran ayah page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Ayah Error',
                'error' => 'Unable to load ayah. Please try again later.',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'request_ip' => $this->getClientIp(),
                'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                'memory_usage' => $this->formatBytes(memory_get_usage()),
                'memory_limit' => ini_get('memory_limit') ?: 'Unknown'
            ]);
        }
    }

    /**
     * Display all ayahs in a surah
     */
    public function surahPage(Request $request, $surah)
    {
        try {
            // Normalize language to code (e.g., en, ar, ur)
            $language = $request->getQueryParam('lang', 'en');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert surah parameter to integer
            $surahNumber = (int) $surah;

            // Load translators for this language (normalize inside repo)
            $translators = $this->ayahRepository->getAllTranslators($language);

            // If requested translator isn't available, pick the first available
            $translatorNames = array_map(static fn($t) => $t['translator'] ?? $t['name'] ?? '', $translators);
            if ($translator && !in_array($translator, $translatorNames, true) && !empty($translators)) {
                $translator = $translators[0]['translator'] ?? $translators[0]['name'];
            }

            $ayahs = $this->ayahRepository->getBySurah($surahNumber, $language, $translator);
            $surahInfo = $this->ayahRepository->getSurahInfo($surahNumber);

            if (empty($ayahs)) {
                return $this->notFound("Surah {$surah} not found");
            }

            // If we received no translations for current translator, try first available translator for this language
            $hasTranslations = false;
            foreach ($ayahs as $a) {
                if (!empty($a['translation_text'])) { $hasTranslations = true; break; }
            }
            if (!$hasTranslations && !empty($translators)) {
                $fallbackTranslator = $translators[0]['translator'] ?? ($translators[0]['name'] ?? null);
                if ($fallbackTranslator && $fallbackTranslator !== $translator) {
                    $translator = $fallbackTranslator;
                    $ayahs = $this->ayahRepository->getBySurah($surahNumber, $language, $translator);
                }
            }

            $data = [
                'title' => "Quran Surah {$surah} - IslamWiki",
                'surah' => $surahInfo,
                'ayahs' => $ayahs,
                'language' => $language,
                'translator' => $translator,
                'translators' => $translators,
            ];

            return $this->view('quran/surah', $data);
        } catch (Exception $e) {
            error_log("Quran surah page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Surah Error',
                'error' => 'Unable to load surah. Please try again later.'
            ]);
        }
    }

    /**
     * Display surah information
     */
    public function surahInfoPage(Request $request, $surah)
    {
        try {
            // Convert surah parameter to integer
            $surahNumber = (int) $surah;
            $surahInfo = $this->quranAyah->getSurahInfo($surahNumber);

            if (!$surahInfo) {
                return $this->notFound("Surah {$surah} not found");
            }

            $data = [
                'title' => "Surah {$surah} Information - IslamWiki",
                'surah' => $surahInfo,
            ];

            return $this->view('quran/surah_info', $data);
        } catch (Exception $e) {
            error_log("Quran surah info page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Surah Info Error',
                'error' => 'Unable to load surah information. Please try again later.'
            ]);
        }
    }

    /**
     * Display ayahs in a Juz
     */
    public function juzPage(Request $request, $juz)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert juz parameter to integer
            $juzNumber = (int) $juz;

            $ayahs = $this->quranAyah->getByJuz($juzNumber, $language, $translator);

            if (empty($ayahs)) {
                return $this->notFound("Juz {$juz} not found");
            }

            $data = [
                'title' => "Quran Juz {$juz} - IslamWiki",
                'juz' => $juz,
                'ayahs' => $ayahs,
                'language' => $language,
                'translator' => $translator,
            ];

            return $this->view('quran/juz', $data);
        } catch (Exception $e) {
            error_log("Quran juz page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Juz Error',
                'error' => 'Unable to load Juz. Please try again later.'
            ]);
        }
    }

    /**
     * Display ayahs on a specific page
     */
    public function pagePage(Request $request, $page)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert page parameter to integer
            $pageNumber = (int) $page;

            $ayahs = $this->quranAyah->getByPage($pageNumber, $language, $translator);

            if (empty($ayahs)) {
                return $this->notFound("Page {$page} not found");
            }

            $data = [
                'title' => "Quran Page {$page} - IslamWiki",
                'page' => $page,
                'ayahs' => $ayahs,
                'language' => $language,
                'translator' => $translator,
            ];

            return $this->view('quran/page', $data);
        } catch (Exception $e) {
            error_log("Quran page error: " . $e->getMessage());
            return $this->view('quran/error', [
                'title' => 'Quran Page Error',
                'error' => 'Unable to load page. Please try again later.'
            ]);
        }
    }

    /**
     * API: Get all ayahs
     */
    public function apiAyahs(Request $request)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');
            $limit = (int)($request->getQueryParam('limit', 50));

            $ayahs = $this->quranAyah->getDailyAyahs($limit);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $ayahs,
                'total' => count($ayahs),
                'language' => $language,
                'translator' => $translator,
            ]));
        } catch (Exception $e) {
            error_log("Quran API ayahs error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get ayah by ID
     */
    public function apiAyah(Request $request, $id)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert id parameter to integer
            $ayahId = (int) $id;

            $ayah = $this->quranAyah->findById($ayahId, $language, $translator);

            if (!$ayah) {
                return $this->notFound("Ayah not found");
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $ayah,
            ]));
        } catch (Exception $e) {
            error_log("Quran API ayah error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get ayah by reference
     */
    public function apiAyahByReference(Request $request, $surah, $ayah)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert parameters to integers
            $surahNumber = (int) $surah;
            $ayahNumber = (int) $ayah;

            $ayahData = $this->quranAyah->getByReference($surahNumber, $ayahNumber, $language, $translator);

            if (!$ayahData) {
                return $this->notFound("Ayah {$surah}:{$ayah} not found");
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $ayahData,
            ]));
        } catch (Exception $e) {
            error_log("Quran API ayah by reference error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Search ayahs
     */
    public function apiSearch(Request $request)
    {
        try {
            $query = $request->getQueryParam('q', '');
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');
            $limit = (int)($request->getQueryParam('limit', 50));

            if (empty($query)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Search query is required',
                ]));
            }

            $results = $this->quranAyah->getByKeywords([$query], $language);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $results,
                'total' => count($results),
                'query' => $query,
                'language' => $language,
                'translator' => $translator,
            ]));
        } catch (Exception $e) {
            error_log("Quran API search error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get random ayah
     */
    public function apiRandomAyah(Request $request)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            $ayah = $this->quranAyah->getRandomAyah($language, $translator);

            if (!$ayah) {
                return $this->notFound("No ayah found");
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $ayah,
            ]));
        } catch (Exception $e) {
            error_log("Quran API random ayah error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get ayah references for a wiki page
     */
    public function apiReferences(Request $request, $pageId)
    {
        try {
            // This would typically query a separate table linking Quran verses to wiki pages
            // For now, return empty results
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => [],
                'page_id' => $pageId,
            ]));
        } catch (Exception $e) {
            error_log("Quran API references error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get tafsir for an ayah
     */
    public function apiTafsir(Request $request, $ayahId)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');

            // Convert ayahId parameter to integer
            $id = (int) $ayahId;

            $tafsir = $this->quranAyah->getTafsir($id, $language);

            if (!$tafsir) {
                return $this->notFound("Tafsir not found");
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $tafsir,
            ]));
        } catch (Exception $e) {
            error_log("Quran API tafsir error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get recitation for an ayah
     */
    public function apiRecitation(Request $request, $ayahId)
    {
        try {
            $reciter = $request->getQueryParam('reciter', 'default');
            $recitation = $this->quranAyah->getRecitation($ayahId, $reciter);

            if (!$recitation) {
                return $this->notFound("Recitation not found");
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $recitation,
            ]));
        } catch (Exception $e) {
            error_log("Quran API recitation error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get Quran statistics
     */
    public function apiStatistics(Request $request)
    {
        try {
            $stats = $this->quranAyah->getStatistics();

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $stats,
            ]));
        } catch (Exception $e) {
            error_log("Quran API statistics error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * Quran widget for embedding
     */
    public function widget(Request $request, $surah, $ayah)
    {
        try {
            $language = $request->getQueryParam('lang', 'english');
            $translator = $request->getQueryParam('translator', 'Saheeh International');

            // Convert parameters to integers
            $surahNumber = (int) $surah;
            $ayahNumber = (int) $ayah;

            $ayahData = $this->quranAyah->getByReference($surahNumber, $ayahNumber, $language, $translator);

            if (!$ayahData) {
                return $this->notFound("Ayah {$surahNumber}:{$ayahNumber} not found");
            }

            $data = [
                'ayah' => $ayahData,
                'language' => $language,
                'translator' => $translator,
                'surah' => $surahNumber,
                'ayah_number' => $ayahNumber,
            ];

            return $this->view('quran/widget', $data);
        } catch (Exception $e) {
            error_log("Quran widget error: " . $e->getMessage());
            return $this->view('quran/error', [
                'error' => 'Unable to load ayah widget. Please try again later.'
            ]);
        }
    }

    /**
     * Helper method for 404 responses
     */
    private function notFound($message = 'Not Found')
    {
        return $this->view('errors/404', [
            'title' => 'Page Not Found',
            'error' => $message,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? 'None',
            'timestamp' => date('Y-m-d H:i:s'),
            'debug' => ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false') === 'true',
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'server_info' => print_r($_SERVER, true)
        ], 404);
    }

    /**
     * API: Get all surahs
     */
    public function apiSurah(Request $request)
    {
        try {
            $surahs = $this->quranAyah->getAllSurahs();

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $surahs,
            ]));
        } catch (Exception $e) {
            error_log("Quran API surahs error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get all juz
     */
    public function apiJuz(Request $request)
    {
        try {
            $juz = $this->quranAyah->getJuzStats();

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $juz,
            ]));
        } catch (Exception $e) {
            error_log("Quran API juz error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        $realIp = $_SERVER['HTTP_X_REAL_IP'] ?? '';
        if ($realIp) {
            return $realIp;
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * API: Get specific juz details
     */
    public function apiJuzDetail(Request $request, $juz)
    {
        try {
            $juzNumber = (int) $juz;
            $juzData = $this->quranAyah->getByJuz($juzNumber);

            if (empty($juzData)) {
                return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Juz not found',
                ]));
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $juzData,
            ]));
        } catch (Exception $e) {
            error_log("Quran API juz detail error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get all pages
     */
    public function apiPages(Request $request)
    {
        try {
            // Since there's no getAllPages method, we'll return a basic structure
            // based on the 604 pages of the Quran
            $pages = [];
            for ($i = 1; $i <= 604; $i++) {
                $pages[] = [
                    'page_number' => $i,
                    'url' => "/quran/page/{$i}"
                ];
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $pages,
            ]));
        } catch (Exception $e) {
            error_log("Quran API pages error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }

    /**
     * API: Get specific page details
     */
    public function apiPageDetail(Request $request, $page)
    {
        try {
            $pageNumber = (int) $page;
            $pageData = $this->quranAyah->getByPage($pageNumber);

            if (empty($pageData)) {
                return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Page not found',
                ]));
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'data' => $pageData,
            ]));
        } catch (Exception $e) {
            error_log("Quran API page detail error: " . $e->getMessage());
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error',
            ]));
        }
    }
}
