<?php
declare(strict_types=1);

namespace IslamWiki\Core\Knowledge\Interfaces;

/**
 * Ontology Interface
 * 
 * Defines the contract for ontology systems that model
 * Islamic knowledge concepts and their relationships.
 */
interface OntologyInterface
{
    /**
     * Search the ontology.
     * 
     * @param string $query The search query
     * @param array $options Search options
     * @return array Search results
     */
    public function search(string $query, array $options = []): array;
    
    /**
     * Get ontology type.
     * 
     * @return string The type of ontology
     */
    public function getType(): string;
    
    /**
     * Add a concept to the ontology.
     * 
     * @param string $concept The concept to add
     * @param array $properties Concept properties
     * @return bool True if added successfully
     */
    public function addConcept(string $concept, array $properties = []): bool;
    
    /**
     * Get concept relationships.
     * 
     * @param string $concept The concept to get relationships for
     * @return array Array of relationships
     */
    public function getRelationships(string $concept): array;
} 