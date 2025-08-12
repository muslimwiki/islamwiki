<?php

namespace App\Http\Controllers;

use App\Services\QuranService;
use App\Core\Http\Request;
use App\Core\Http\Response;

class QuranApiController
{
    protected $quranService;
    
    public function __construct()
    {
        $this->quranService = new QuranService();
    }
    
    public function index()
    {
        $surahs = $this->quranService->getAllSurahs();
        $juz = $this->quranService->getAllJuz();
        $languages = $this->quranService->getAvailableLanguages();
        
        return Response::json([
            'surahs' => $surahs,
            'juz' => $juz,
            'languages' => $languages
        ]);
    }
    
    public function getSurah(Request $request, $surahNumber)
    {
        $language = $request->get('language', 'en');
        $surah = $this->quranService->getSurah($surahNumber, $language);
        
        if (!$surah) {
            return Response::json(['error' => 'Surah not found'], 404);
        }
        
        return Response::json($surah);
    }
    
    public function getAyah(Request $request, $surahNumber, $ayahNumber)
    {
        $language = $request->get('language', 'en');
        $ayah = $this->quranService->getAyah($surahNumber, $ayahNumber, $language);
        
        if (!$ayah) {
            return Response::json(['error' => 'Ayah not found'], 404);
        }
        
        return Response::json($ayah);
    }
    
    public function getJuz(Request $request, $juzNumber)
    {
        $language = $request->get('language', 'en');
        $juz = $this->quranService->getJuz($juzNumber, $language);
        
        if (!$juz) {
            return Response::json(['error' => 'Juz not found'], 404);
        }
        
        return Response::json($juz);
    }
    
    public function getPage(Request $request, $pageNumber)
    {
        $language = $request->get('language', 'en');
        $page = $this->quranService->getPage($pageNumber, $language);
        
        if (!$page) {
            return Response::json(['error' => 'Page not found'], 404);
        }
        
        return Response::json($page);
    }
    
    public function search(Request $request)
    {
        $query = $request->get('q');
        $language = $request->get('language', 'en');
        
        if (!$query) {
            return Response::json(['error' => 'Search query required'], 400);
        }
        
        $results = $this->quranService->search($query, $language);
        
        return Response::json([
            'query' => $query,
            'language' => $language,
            'results' => $results,
            'count' => count($results)
        ]);
    }
    
    public function getLanguages()
    {
        $languages = $this->quranService->getAvailableLanguages();
        return Response::json($languages);
    }
    
    public function getTranslators(Request $request)
    {
        $language = $request->get('language');
        $translators = $this->quranService->getAvailableTranslators($language);
        return Response::json($translators);
    }
}
