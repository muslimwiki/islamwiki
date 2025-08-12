<?php

namespace App\Models;

use IslamWiki\Core\Database\Connection;
use PDO;

class QuranSurah
{
    protected $db;
    
    public function __construct()
    {
        $this->db = new Connection();
    }
    
    public function getAllSurahs()
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT surah_number, surah_name_arabic, surah_name_english, 
                   revelation_type, ayah_count, juz_start, page_start
            FROM quran_ayahs 
            ORDER BY surah_number
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSurahInfo($surahNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT surah_number, surah_name_arabic, surah_name_english, 
                   revelation_type, ayah_count, juz_start, page_start
            FROM quran_ayahs 
            WHERE surah_number = ?
        ");
        $stmt->execute([$surahNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getSurahByRevelationType($type)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT surah_number, surah_name_arabic, surah_name_english, 
                   revelation_type, ayah_count, juz_start, page_start
            FROM quran_ayahs 
            WHERE revelation_type = ?
            ORDER BY surah_number
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSurahByJuz($juzNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT surah_number, surah_name_arabic, surah_name_english, 
                   revelation_type, ayah_count, juz_start, page_start
            FROM quran_ayahs 
            WHERE juz_start <= ? AND (juz_start + CEIL(ayah_count / 10)) >= ?
            ORDER BY surah_number
        ");
        $stmt->execute([$juzNumber, $juzNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
