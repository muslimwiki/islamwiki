<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;

/**
 * HadithNarrator Model
 * 
 * Represents a narrator in the chain of hadith transmission (isnad)
 */
class HadithNarrator extends BaseModel
{
    /**
     * @var string The table associated with the model
     */
    protected string $table = 'hadith_narrators';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected array $fillable = [
        'name',
        'name_ar',
        'biography',
        'biography_ar',
        'birth_year',
        'death_year',
        'era',
        'reliability_grade'
    ];
    
    /**
     * @var array Narrator reliability grades
     */
    public const RELIABILITY_GRADES = [
        'thiqah' => 'Trustworthy',
        'saduq' => 'Truthful',
        'hasan_al_hadith' => 'Good in Hadith',
        'saduq_yukhti' => 'Truthful but makes mistakes',
        'saduq_yurad_lahu_ghalat' => 'Truthful but has some errors',
        'mastur' => 'Unknown reliability',
        'majhul_al_hal' => 'Unknown status',
        'daif' => 'Weak',
        'matruk' => 'Abandoned',
        'kadhdhab' => 'Liar',
        'wadda' => 'Accused of lying'
    ];
    
    /**
     * Get the display name of the narrator
     */
    public function getDisplayName(string $language = 'en'): string
    {
        return $language === 'ar' && !empty($this->name_ar) ? $this->name_ar : $this->name;
    }
    
    /**
     * Get the biography in the specified language
     */
    public function getBiography(string $language = 'en'): ?string
    {
        if ($language === 'ar' && !empty($this->biography_ar)) {
            return $this->biography_ar;
        }
        
        return $this->biography;
    }
    
    /**
     * Get the reliability grade as a display string
     */
    public function getReliabilityGradeDisplay(): string
    {
        return self::RELIABILITY_GRADES[$this->reliability_grade] ?? ucfirst(str_replace('_', ' ', $this->reliability_grade));
    }
    
    /**
     * Get all hadith narrated by this narrator
     * 
     * @param int $limit Maximum number of hadith to return
     * @param int $offset Offset for pagination
     * @return array Array of HadithNarration models
     */
    public function narratedHadith(int $limit = 50, int $offset = 0): array
    {
        if (!$this->exists) {
            return [];
        }
        
        $query = "SELECT n.* FROM hadith_narrations n 
                 INNER JOIN hadith_narrator_chains nc ON n.id = nc.hadith_id 
                 WHERE nc.narrator_id = ? 
                 ORDER BY n.collection_id, n.book_id, n.hadith_number 
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id, $limit, $offset]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function($row) {
            $hadith = new HadithNarration($this->db);
            $hadith->fill($row);
            $hadith->exists = true;
            return $hadith;
        }, $rows);
    }
    
    /**
     * Get the total number of hadith narrated by this narrator
     */
    public function getNarratedHadithCount(): int
    {
        if (!$this->exists) {
            return 0;
        }
        
        $query = "SELECT COUNT(DISTINCT hadith_id) as count 
                 FROM hadith_narrator_chains 
                 WHERE narrator_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Find a narrator by name (case-insensitive)
     */
    public static function findByName(string $name, ?Connection $db = null): ?self
    {
        $instance = new static($db);
        $row = $instance->db->query(
            "SELECT * FROM " . $instance->getTable() . " WHERE LOWER(name) = LOWER(?) OR LOWER(name_ar) = LOWER(?)",
            [$name, $name]
        )->fetch();
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Search for narrators by name
     */
    public static function search(string $query, int $limit = 10, ?Connection $db = null): array
    {
        $instance = new static($db);
        $query = "%$query%";
        
        $rows = $instance->db->query(
            "SELECT * FROM " . $instance->getTable() . " 
            WHERE name LIKE ? OR name_ar LIKE ? 
            ORDER BY 
                CASE 
                    WHEN name LIKE ? THEN 1 
                    WHEN name_ar LIKE ? THEN 2 
                    ELSE 3 
                END, 
                name 
            LIMIT ?",
            [$query, $query, $query . '%', $query . '%', $limit]
        )->fetchAll();
        
        $narrators = [];
        foreach ($rows as $row) {
            $narrator = new static($db);
            $narrator->fill((array)$row);
            $narrator->exists = true;
            $narrators[] = $narrator;
        }
        
        return $narrators;
    }
}
