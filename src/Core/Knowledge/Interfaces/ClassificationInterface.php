<?php
declare(strict_types=1);

namespace IslamWiki\Core\Knowledge\Interfaces;

/**
 * Classification Interface
 * 
 * Defines the contract for classification systems that organize
 * Islamic knowledge into categories and hierarchies.
 */
interface ClassificationInterface
{
    /**
     * Get related concepts for a term.
     * 
     * @param string $term The term to find related concepts for
     * @return array Array of related concepts
     */
    public function getRelatedConcepts(string $term): array;
    
    /**
     * Get classification type.
     * 
     * @return string The type of classification
     */
    public function getType(): string;
    
    /**
     * Classify a term into categories.
     * 
     * @param string $term The term to classify
     * @return array Array of categories
     */
    public function classify(string $term): array;
} 