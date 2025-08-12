<?php

namespace App\Models;

use IslamWiki\Core\Database\Connection;
use PDO;

class QuranPage
{
    protected $db;

    public function __construct()
    {
        $this->db = new Connection();
    }
    
    public function getAllPages()
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT page_number,
                   MIN(surah_number) as start_surah,
                   MAX(surah_number) as end_surah,
                   MIN(ayah_number) as start_ayah,
                   MAX(ayah_number) as end_ayah,
                   COUNT(*) as ayah_count
            FROM quran_ayahs 
            GROUP BY page_number
            ORDER BY page_number
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPageInfo($pageNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT page_number,
                   MIN(surah_number) as start_surah,
                   MAX(surah_number) as end_surah,
                   MIN(ayah_number) as start_ayah,
                   MAX(ayah_number) as end_ayah,
                   COUNT(*) as ayah_count
            FROM quran_ayahs 
            WHERE page_number = ?
            GROUP BY page_number
        ");
        $stmt->execute([$pageNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPageBySurahAyah($surahNumber, $ayahNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT page_number
            FROM quran_ayahs 
            WHERE surah_number = ? AND ayah_number = ?
            LIMIT 1
        ");
        $stmt->execute([$surahNumber, $ayahNumber]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['page_number'] : null;
    }

    public function getPageByJuz($juzNumber)
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT DISTINCT page_number
            FROM quran_ayahs 
            WHERE juz_number = ?
            ORDER BY page_number
        ");
        $stmt->execute([$juzNumber]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTotalPages()
    {
        $stmt = $this->db->getPdo()->prepare("
            SELECT COUNT(DISTINCT page_number) as total_pages
            FROM quran_ayahs
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['total_pages'] : 0;
    }
}
