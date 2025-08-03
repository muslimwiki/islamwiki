<?php
declare(strict_types=1);

namespace IslamWiki\Core\Knowledge\Interfaces;

/**
 * Schema Layer Interface
 * 
 * Defines the contract for schema layers that define
 * the structure and organization of Islamic knowledge.
 */
interface SchemaLayerInterface
{
    /**
     * Get schema layer type.
     * 
     * @return string The type of schema layer
     */
    public function getType(): string;
    
    /**
     * Define schema structure.
     * 
     * @param array $structure The schema structure
     * @return bool True if defined successfully
     */
    public function defineSchema(array $structure): bool;
    
    /**
     * Validate data against schema.
     * 
     * @param array $data The data to validate
     * @return bool True if valid
     */
    public function validateData(array $data): bool;
    
    /**
     * Get schema definition.
     * 
     * @return array The schema definition
     */
    public function getSchema(): array;
} 