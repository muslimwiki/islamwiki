<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;

/**
 * HadithBook Model
 * 
 * Represents a book within a hadith collection (e.g., Book of Faith in Sahih Bukhari)
 */
class HadithBook extends BaseModel
{
    /**
     * @var string The table associated with the model
     */
    protected string $table = 'hadith_books';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected array $fillable = [
        'collection_id',
        'book_number',
        'name',
        'name_ar',
        'description',
        'total_hadith'
    ];
    
    /**
     * The collection this book belongs to
     */
    private ?HadithCollection $collection = null;
    
    /**
     * Get the collection this book belongs to
     */
    public function collection(): ?HadithCollection
    {
        if (!$this->collection && $this->collection_id) {
            $this->collection = HadithCollection::find($this->collection_id, $this->db);
        }
        
        return $this->collection;
    }
    
    /**
     * Get the hadith narrations in this book
     * 
     * @param int $limit Maximum number of hadith to return
     * @param int $offset Offset for pagination
     * @return array Array of HadithNarration models
     */
    public function hadith(int $limit = 50, int $offset = 0): array
    {
        if (!$this->exists) {
            return [];
        }
        
        $rows = $this->db->select(
            'hadith_narrations',
            '*',
            [
                'collection_id' => $this->collection_id,
                'book_id' => $this->id
            ],
            ['hadith_number' => 'ASC'],
            $limit,
            $offset
        );
        
        return array_map(function($row) {
            $hadith = new HadithNarration($this->db);
            $hadith->fill((array)$row);
            $hadith->exists = true;
            return $hadith;
        }, $rows);
    }
    
    /**
     * Get the total number of hadith in this book
     */
    public function getHadithCount(): int
    {
        if (!$this->exists) {
            return 0;
        }
        
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM hadith_narrations WHERE collection_id = ? AND book_id = ?",
            [$this->collection_id, $this->id]
        )->fetch();
        
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Update the hadith count for this book
     */
    public function updateHadithCount(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $count = $this->getHadithCount();
        $this->total_hadith = $count;
        return $this->save();
    }
    
    /**
     * Find a book by collection and book number
     */
    public static function findByCollectionAndNumber(int $collectionId, int $bookNumber, ?Connection $db = null): ?self
    {
        $instance = new static($db);
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            [
                'collection_id' => $collectionId,
                'book_number' => $bookNumber
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
     * Get all books for a collection
     */
    public static function getByCollection(int $collectionId, ?Connection $db = null): array
    {
        $instance = new static($db);
        $rows = $instance->db->select(
            $instance->getTable(),
            '*',
            ['collection_id' => $collectionId],
            ['book_number' => 'ASC']
        );
        
        $books = [];
        foreach ($rows as $row) {
            $book = new static($db);
            $book->fill((array)$row);
            $book->exists = true;
            $books[] = $book;
        }
        
        return $books;
    }
}
