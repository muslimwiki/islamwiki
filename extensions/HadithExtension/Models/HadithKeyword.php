<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;

/**
 * HadithKeyword Model
 * 
 * Manages keywords for hadith search functionality
 */
class HadithKeyword extends BaseModel
{
    /**
     * @var string The table associated with the model
     */
    protected string $table = 'hadith_keywords';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected array $fillable = [
        'keyword',
        'language',
        'hadith_count'
    ];
    
    /**
     * Get or create a keyword
     */
    public static function getOrCreate(
        string $keyword, 
        string $language = 'en',
        ?Connection $db = null
    ): self {
        $instance = new static($db);
        
        // Try to find existing keyword
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            [
                'keyword' => $keyword,
                'language' => $language
            ]
        );
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        // Create new keyword
        $instance->fill([
            'keyword' => $keyword,
            'language' => $language,
            'hadith_count' => 0
        ]);
        
        return $instance;
    }
    
    /**
     * Get hadith associated with this keyword
     */
    public function getHadith(int $limit = 20, int $offset = 0): array
    {
        if (!$this->exists) {
            return [];
        }
        
        $query = "SELECT h.* FROM hadith_narrations h 
                 INNER JOIN hadith_keyword_mappings km ON h.id = km.hadith_id 
                 WHERE km.keyword_id = ? 
                 ORDER BY km.relevance_score DESC, h.collection_id, h.hadith_number 
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
     * Get the total number of hadith associated with this keyword
     */
    public function getHadithCount(): int
    {
        if (!$this->exists) {
            return 0;
        }
        
        $query = "SELECT COUNT(*) as count FROM hadith_keyword_mappings WHERE keyword_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Update the hadith count for this keyword
     */
    public function updateHadithCount(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $count = $this->getHadithCount();
        $this->hadith_count = $count;
        return $this->save();
    }
    
    /**
     * Search for keywords by query string
     */
    public static function search(
        string $query, 
        ?string $language = null, 
        int $limit = 10,
        ?Connection $db = null
    ): array {
        $instance = new static($db);
        
        $params = [];
        $where = ['keyword LIKE ?'];
        $params[] = "%$query%";
        
        if ($language !== null) {
            $where[] = 'language = ?';
            $params[] = $language;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM " . $instance->getTable() . " 
                $whereClause 
                ORDER BY hadith_count DESC, keyword 
                LIMIT ?";
        
        $params[] = $limit;
        
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $keywords = [];
        foreach ($rows as $row) {
            $keyword = new static($db);
            $keyword->fill($row);
            $keyword->exists = true;
            $keywords[] = $keyword;
        }
        
        return $keywords;
    }
    
    /**
     * Associate a hadith with this keyword
     */
    public function attachHadith(int $hadithId, float $relevance = 1.0): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        try {
            $this->db->insert('hadith_keyword_mappings', [
                'hadith_id' => $hadithId,
                'keyword_id' => $this->id,
                'relevance_score' => $relevance
            ]);
            
            // Update the hadith count
            $this->hadith_count++;
            return $this->save();
            
        } catch (\PDOException $e) {
            // Likely a duplicate entry, which is fine
            if ($e->getCode() === '23000') { // Integrity constraint violation
                return true;
            }
            throw $e;
        }
    }
    
    /**
     * Remove association between this keyword and a hadith
     */
    public function detachHadith(int $hadithId): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $result = $this->db->delete(
            'hadith_keyword_mappings',
            [
                'hadith_id' => $hadithId,
                'keyword_id' => $this->id
            ]
        );
        
        if ($result) {
            // Update the hadith count
            $this->hadith_count = max(0, $this->hadith_count - 1);
            return $this->save();
        }
        
        return false;
    }
    
    /**
     * Get related keywords based on co-occurrence with this keyword
     */
    public function getRelatedKeywords(int $limit = 10): array
    {
        if (!$this->exists) {
            return [];
        }
        
        $query = "SELECT k.*, COUNT(*) as co_occurrence 
                 FROM hadith_keywords k 
                 INNER JOIN hadith_keyword_mappings km1 ON k.id = km1.keyword_id 
                 INNER JOIN hadith_keyword_mappings km2 ON km1.hadith_id = km2.hadith_id 
                 WHERE km2.keyword_id = ? AND k.id != ? 
                 GROUP BY k.id 
                 ORDER BY co_occurrence DESC 
                 LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->id, $this->id, $limit]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $keywords = [];
        foreach ($rows as $row) {
            $keyword = new static($this->db);
            $keyword->fill($row);
            $keyword->exists = true;
            $keywords[] = [
                'keyword' => $keyword,
                'co_occurrence' => (int)$row['co_occurrence']
            ];
        }
        
        return $keywords;
    }
}
