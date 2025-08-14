<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;

/**
 * HadithNarration Model
 * 
 * Represents a single hadith narration with its text, chain of narrators, and metadata
 */
class HadithNarration extends BaseModel
{
    /**
     * @var string The table associated with the model
     */
    protected string $table = 'hadith_narrations';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected array $fillable = [
        'collection_id',
        'book_id',
        'hadith_number',
        'hadith_number_secondary',
        'text_arabic',
        'text_arabic_diacless',
        'text_english',
        'text_urdu',
        'text_indonesian',
        'text_turkish',
        'text_malay',
        'grade',
        'explanation',
        'reference_book',
        'reference_page',
        'reference_hadith',
        'is_muttafaqun_alayh',
        'is_sahih',
        'is_hasan',
        'is_daif',
        'is_mawdu'
    ];
    
    /**
     * @var HadithCollection|null The collection this hadith belongs to
     */
    private ?HadithCollection $collection = null;
    
    /**
     * @var HadithBook|null The book this hadith belongs to
     */
    private ?HadithBook $book = null;
    
    /**
     * @var array|null Cache for narrators
     */
    private ?array $narrators = null;
    
    /**
     * Get the collection this hadith belongs to
     */
    public function collection(): ?HadithCollection
    {
        if (!$this->collection && $this->collection_id) {
            $this->collection = HadithCollection::find($this->collection_id, $this->db);
        }
        
        return $this->collection;
    }
    
    /**
     * Get the book this hadith belongs to
     */
    public function book(): ?HadithBook
    {
        if (!$this->book && $this->book_id) {
            $this->book = HadithBook::find($this->book_id, $this->db);
        }
        
        return $this->book;
    }
    
    /**
     * Get the hadith text in the specified language
     */
    public function getText(string $language = 'ar'): ?string
    {
        $property = 'text_' . $language;
        return $this->$property ?? null;
    }
    
    /**
     * Get the chain of narrators (isnad) for this hadith
     * 
     * @return array Array of HadithNarrator models in order of narration
     */
    public function getNarrators(): array
    {
        if ($this->narrators === null) {
            $this->narrators = [];
            
            $rows = $this->db->select(
                'hadith_narrator_chains',
                ['narrator_id', 'narrator_order', 'is_primary_narrator', 'notes'],
                ['hadith_id' => $this->id],
                ['narrator_order' => 'ASC']
            );
            
            foreach ($rows as $row) {
                $narrator = HadithNarrator::find($row['narrator_id'], $this->db);
                if ($narrator) {
                    $narrator->pivot = [
                        'is_primary' => (bool)$row['is_primary_narrator'],
                        'notes' => $row['notes']
                    ];
                    $this->narrators[] = $narrator;
                }
            }
        }
        
        return $this->narrators;
    }
    
    /**
     * Get the primary narrator of this hadith
     */
    public function getPrimaryNarrator(): ?HadithNarrator
    {
        $narrators = $this->getNarrators();
        
        foreach ($narrators as $narrator) {
            if (isset($narrator->pivot['is_primary']) && $narrator->pivot['is_primary']) {
                return $narrator;
            }
        }
        
        return $narrators[0] ?? null;
    }
    
    /**
     * Get the grade of the hadith as a display string
     */
    public function getGradeDisplay(): string
    {
        if ($this->is_muttafaqun_alayh) {
            return 'Muttafaqun Alayh (Agreed Upon)';
        } elseif ($this->is_sahih) {
            return 'Sahih (Authentic)';
        } elseif ($this->is_hasan) {
            return 'Hasan (Good)';
        } elseif ($this->is_daif) {
            return 'Da'if (Weak)';
        } elseif ($this->is_mawdu) {
            return 'Mawdu (Fabricated)';
        } elseif ($this->grade) {
            return ucfirst($this->grade);
        }
        
        return 'Not Graded';
    }
    
    /**
     * Find a hadith by collection and hadith number
     */
    public static function findByCollectionAndNumber(
        int $collectionId, 
        $hadithNumber, 
        ?Connection $db = null
    ): ?self {
        $instance = new static($db);
        
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            [
                'collection_id' => $collectionId,
                'hadith_number' => $hadithNumber
            ]
        );
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Find hadith by collection and secondary number
     */
    public static function findBySecondaryNumber(
        int $collectionId, 
        string $secondaryNumber, 
        ?Connection $db = null
    ): ?self {
        $instance = new static($db);
        
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            [
                'collection_id' => $collectionId,
                'hadith_number_secondary' => $secondaryNumber
            ]
        );
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Search for hadith by text
     * 
     * @param string $query Search query
     * @param string $language Language to search in
     * @param int $collectionId Optional collection ID to filter by
     * @param int $limit Maximum number of results
     * @param int $offset Offset for pagination
     * @return array Array of HadithNarration models
     */
    public static function search(
        string $query, 
        string $language = 'en', 
        ?int $collectionId = null, 
        int $limit = 20, 
        int $offset = 0,
        ?Connection $db = null
    ): array {
        $instance = new static($db);
        
        $params = [];
        $where = [];
        
        // Add text search condition
        $textField = 'text_' . $language;
        if (!in_array($textField, ['text_arabic', 'text_english', 'text_urdu', 'text_indonesian', 'text_turkish', 'text_malay'])) {
            $textField = 'text_english'; // Default to English if invalid language
        }
        
        $where[] = "$textField LIKE ?";
        $params[] = "%$query%";
        
        // Add collection filter if provided
        if ($collectionId !== null) {
            $where[] = 'collection_id = ?';
            $params[] = $collectionId;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Prepare and execute the query
        $sql = "SELECT * FROM " . $instance->getTable() . " $whereClause ORDER BY collection_id, hadith_number LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Map rows to models
        $hadiths = [];
        foreach ($rows as $row) {
            $hadith = new static($db);
            $hadith->fill($row);
            $hadith->exists = true;
            $hadiths[] = $hadith;
        }
        
        return $hadiths;
    }
    
    /**
     * Get the total count of search results
     */
    public static function searchCount(
        string $query, 
        string $language = 'en', 
        ?int $collectionId = null,
        ?Connection $db = null
    ): int {
        $instance = new static($db);
        
        $params = [];
        $where = [];
        
        // Add text search condition
        $textField = 'text_' . $language;
        if (!in_array($textField, ['text_arabic', 'text_english', 'text_urdu', 'text_indonesian', 'text_turkish', 'text_malay'])) {
            $textField = 'text_english'; // Default to English if invalid language
        }
        
        $where[] = "$textField LIKE ?";
        $params[] = "%$query%";
        
        // Add collection filter if provided
        if ($collectionId !== null) {
            $where[] = 'collection_id = ?';
            $params[] = $collectionId;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Execute the count query
        $sql = "SELECT COUNT(*) as count FROM " . $instance->getTable() . " $whereClause";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return (int)($result['count'] ?? 0);
    }
}
