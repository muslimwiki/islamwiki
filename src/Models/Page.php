<?php

namespace App\Models;

class Page
{
    // This is a simplified model for testing
    // In a real application, this would extend an ORM class
    public static function where($column, $value)
    {
        // For testing purposes, return a mock query builder
        return new class($column, $value) {
            private $column;
            private $value;
            
            public function __construct($column, $value)
            {
                $this->column = $column;
                $this->value = $value;
            }
            
            public function first()
            {
                // Return a mock page for testing
                if ($this->column === 'slug' && $this->value === 'test-page') {
                    return (object)[
                        'id' => 1,
                        'title' => 'Test Page',
                        'slug' => 'test-page',
                        'content' => 'This is a test page content.',
                        'is_published' => true
                    ];
                }
                
                return null;
            }
        };
    }
}
