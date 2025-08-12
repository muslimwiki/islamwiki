<?php

namespace App\Services;

use App\Models\QuranAyah;
use App\Models\QuranSurah;
use App\Models\QuranJuz;
use App\Models\QuranPage;
use App\Models\QuranTranslation;

class QuranService
{
    protected $ayahModel;
    protected $surahModel;
    protected $juzModel;
    protected $pageModel;
    protected $translationModel;
    
    public function __construct()
    {
        $this->ayahModel = new QuranAyah();
        $this->surahModel = new QuranSurah();
        $this->juzModel = new QuranJuz();
        $this->pageModel = new QuranPage();
        $this->translationModel = new QuranTranslation();
    }
    
    public function getAyah($surah, $ayah, $language = 'en')
    {
        $ayahData = $this->ayahModel->getAyah($surah, $ayah);
        if (!$ayahData) {
            return null;
        }
        
        if ($language !== 'ar') {
            $translation = $this->translationModel->getTranslation($surah, $ayah, $language);
            if ($translation) {
                $ayahData['translation'] = $translation['translation'];
                $ayahData['translator'] = $translation['translator_name'];
            }
        }
        
        return $ayahData;
    }
    
    public function getSurah($surahNumber, $language = 'en')
    {
        $surahInfo = $this->surahModel->getSurahInfo($surahNumber);
        if (!$surahInfo) {
            return null;
        }
        
        $ayahs = $this->ayahModel->getSurah($surahNumber);
        
        if ($language !== 'ar') {
            $translations = $this->translationModel->getSurahTranslation($surahNumber, $language);
            $translationMap = [];
            foreach ($translations as $trans) {
                $translationMap[$trans['ayah_number']] = $trans;
            }
            
            foreach ($ayahs as &$ayah) {
                if (isset($translationMap[$ayah['ayah_number']])) {
                    $ayah['translation'] = $translationMap[$ayah['ayah_number']]['translation'];
                    $ayah['translator'] = $translationMap[$ayah['ayah_number']]['translator_name'];
                }
            }
        }
        
        return [
            'info' => $surahInfo,
            'ayahs' => $ayahs
        ];
    }
    
    public function getJuz($juzNumber, $language = 'en')
    {
        $juzInfo = $this->juzModel->getJuzInfo($juzNumber);
        if (!$juzInfo) {
            return null;
        }
        
        $ayahs = $this->ayahModel->getJuz($juzNumber);
        
        if ($language !== 'ar') {
            // Get translations for all ayahs in the juz
            foreach ($ayahs as &$ayah) {
                $translation = $this->translationModel->getTranslation(
                    $ayah['surah_number'], 
                    $ayah['ayah_number'], 
                    $language
                );
                if ($translation) {
                    $ayah['translation'] = $translation['translation'];
                    $ayah['translator'] = $translation['translator_name'];
                }
            }
        }
        
        return [
            'info' => $juzInfo,
            'ayahs' => $ayahs
        ];
    }
    
    public function getPage($pageNumber, $language = 'en')
    {
        $pageInfo = $this->pageModel->getPageInfo($pageNumber);
        if (!$pageInfo) {
            return null;
        }
        
        $ayahs = $this->pageModel->getAyahByPage($pageNumber);
        
        if ($language !== 'ar') {
            foreach ($ayahs as &$ayah) {
                $translation = $this->translationModel->getTranslation(
                    $ayah['surah_number'], 
                    $ayah['ayah_number'], 
                    $language
                );
                if ($translation) {
                    $ayah['translation'] = $translation['translation'];
                    $ayah['translator'] = $translation['translator_name'];
                }
            }
        }
        
        return [
            'info' => $pageInfo,
            'ayahs' => $ayahs
        ];
    }
    
    public function search($query, $language = 'en')
    {
        $results = $this->ayahModel->searchAyahs($query, $language);
        
        if ($language !== 'ar') {
            foreach ($results as &$result) {
                $translation = $this->translationModel->getTranslation(
                    $result['surah_number'], 
                    $result['ayah_number'], 
                    $language
                );
                if ($translation) {
                    $result['translation'] = $translation['translation'];
                    $result['translator'] = $translation['translator_name'];
                }
            }
        }
        
        return $results;
    }
    
    public function getAllSurahs()
    {
        return $this->surahModel->getAllSurahs();
    }
    
    public function getAllJuz()
    {
        return $this->juzModel->getAllJuz();
    }
    
    public function getAllPages()
    {
        return $this->pageModel->getAllPages();
    }
    
    public function getAvailableLanguages()
    {
        return $this->translationModel->getAvailableLanguages();
    }
    
    public function getAvailableTranslators($language = null)
    {
        return $this->translationModel->getAvailableTranslators($language);
    }
}
