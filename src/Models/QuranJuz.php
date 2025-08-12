<?php

namespace App\Models;

use IslamWiki\Core\Database\Connection;
use PDO;

class QuranJuz
{
    protected $db;
    
    public function __construct()
    {
        $this->db = new Connection();
    }
    
    public function getAllJuz()
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT juz_number, 
                   MIN(surah_number) as start_surah,
                   MAX(surah_number) as end_surah,
                   MIN(page_number) as start_page,
                   MAX(page_number) as end_page,
                   COUNT(*) as ayah_count
            FROM quran_ayahs 
            GROUP BY juz_number
            ORDER BY juz_number
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJuzInfo($juzNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT juz_number, 
                   MIN(surah_number) as start_surah,
                   MAX(surah_number) as end_surah,
                   MIN(page_number) as start_page,
                   MAX(page_number) as end_page,
                   COUNT(*) as ayah_count
            FROM quran_ayahs 
            WHERE juz_number = ?
            GROUP BY juz_number
        ");
        $stmt->execute([$juzNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getJuzByPage($pageNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT juz_number
            FROM quran_ayahs 
            WHERE page_number = ?
            LIMIT 1
        ");
        $stmt->execute([$pageNumber]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['juz_number'] : null;
    }

    public function getJuzBySurah($surahNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT juz_number
            FROM quran_ayahs 
            WHERE surah_number = ? 
            ORDER BY juz_number
        ");
        $stmt->execute([$surahNumber]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
