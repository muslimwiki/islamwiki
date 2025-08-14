<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;

/**
 * HadithCollection Model
 * 
 * Represents a collection of hadith (e.g., Sahih Bukhari, Sahih Muslim)
 */
class HadithCollection extends BaseModel
{
    /**
     * @var string The table associated with the model
     */
    protected string $table = 'hadith_collections';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected array $fillable = [
        'name',
        'name_ar',
        'author',
        'author_ar',
        'description',
        'total_hadith',
        'is_active'
    ];
    
    /**
     * Get the books belonging to this collection
     * 
     * @return array Array of HadithBook models
     */
    public function books(): array
    {
        if (!$this->exists) {
            return [];
        }
        
        $books = $this->db->select(
            'hadith_books',
            '*',
            ['collection_id' => $this->id],
            ['book_number' => 'ASC']
        );
        
        return array_map(function($book) {
            $model = new HadithBook($this->db);
            $model->fill((array)$book);
            $model->exists = true;
            return $model;
        }, $books);
    }
    
    /**
     * Get the total number of hadith in this collection
     */
    public function getTotalHadithCount(): int
    {
        if (!$this->exists) {
            return 0;
        }
        
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM hadith_narrations WHERE collection_id = ?",
            [$this->id]
        )->fetch();
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Update the total hadith count for this collection
     */
    public function updateHadithCount(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $count = $this->getTotalHadithCount();
        $this->total_hadith = $count;
        return $this->save();
    }
    
    /**
     * Get all active collections
     * 
     * @param Connection|null $db Database connection
     * @return array Array of HadithCollection models
     */
    public static function getActiveCollections(?Connection $db = null): array
    {
        $instance = new static($db);
        $rows = $instance->db->select(
            $instance->getTable(),
            '*',
            ['is_active' => true],
            ['name' => 'ASC']
        );
        
        $collections = [];
        foreach ($rows as $row) {
            $collection = new static($db);
            $collection->fill((array)$row);
            $collection->exists = true;
            $collections[] = $collection;
        }
        
        return $collections;
    }
    
    /**
     * Find a collection by its name
     * 
     * @param string $name Collection name
     * @param Connection|null $db Database connection
     * @return HadithCollection|null
     */
    public static function findByName(string $name, ?Connection $db = null): ?self
    {
        $instance = new static($db);
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            ['name' => $name]
        );
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
}
