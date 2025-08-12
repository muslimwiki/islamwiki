<?php

namespace App\Models;

use IslamWiki\Core\Database\Connection;
use PDO;

class QuranTranslation
{
    protected $db;
    
    public function __construct()
    {
        $this->db = new Connection();
    }
    
    public function getTranslation($surah, $ayah, $language = 'en')
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT translation, translator_name, language
            FROM quran_translations 
            WHERE surah_number = ? AND ayah_number = ? AND language = ?
        ");
        $stmt->execute([$surah, $ayah, $language]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getSurahTranslation($surahNumber, $language = 'en')
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT ayah_number, translation, translator_name
            FROM quran_translations 
            WHERE surah_number = ? AND language = ?
            ORDER BY ayah_number
        ");
        $stmt->execute([$surahNumber, $language]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAvailableLanguages()
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT language, language_name
            FROM quran_translations 
            ORDER BY language
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAvailableTranslators($language = null)
    {
        $sql = "SELECT DISTINCT translator_name, translator_info";
        $params = [];
        
        if ($language) {
            $sql .= " WHERE language = ?";
            $params[] = $language;
        }
        
        $sql .= " ORDER BY translator_name";
        
        $stmt = $this->db->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchTranslations($query, $language = 'en')
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT t.surah_number, t.ayah_number, t.translation,
                   a.text as arabic_text, a.surah_name_english
            FROM quran_translations t
            JOIN quran_ayahs a ON t.surah_number = a.surah_number AND t.ayah_number = a.ayah_number
            WHERE t.translation LIKE ? AND t.language = ?
            ORDER BY t.surah_number, t.ayah_number
            LIMIT 50
        ");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $language]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
