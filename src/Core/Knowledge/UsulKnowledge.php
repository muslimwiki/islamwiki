<?php
declare(strict_types=1);

/**
 * Usul (أصول) - Knowledge System
 * 
 * Comprehensive knowledge engine, ontology, and data modeling system for IslamWiki.
 * Usul means "principles" or "roots" in Arabic, especially in Islamic jurisprudence
 * (uṣūl al-fiqh), representing the foundational principles of Islamic knowledge.
 * 
 * @package IslamWiki\Core\Knowledge
 * @version 0.0.40
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Knowledge;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;

/**
 * Usul Knowledge System
 * 
 * Handles knowledge engine, ontology, and data modeling including:
 * - Qur'anic root systems
 * - Hadith classifications
 * - Category trees
 * - Schema layers
 * - Semantic core and knowledge ontology engine
 */
class UsulKnowledge
{
    private AsasContainer $container;
    private ShahidLogger $logger;
    private Connection $db;
    private array $ontologies = [];
    private array $classifications = [];
    private array $rootSystems = [];
    private array $schemaLayers = [];
    
    /**
     * Create a new Usul knowledge system.
     */
    public function __construct(AsasContainer $container, ShahidLogger $logger, Connection $db)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->db = $db;
        $this->initializeKnowledgeSystems();
    }
    
    /**
     * Initialize knowledge systems.
     */
    private function initializeKnowledgeSystems(): void
    {
        // Initialize Qur'anic root systems
        $this->rootSystems = [
            'quranic' => new \IslamWiki\Core\Knowledge\RootSystems\QuranicRootSystem($this->db, $this->logger),
            'hadith' => new \IslamWiki\Core\Knowledge\RootSystems\HadithRootSystem($this->db, $this->logger),
            'fiqh' => new \IslamWiki\Core\Knowledge\RootSystems\FiqhRootSystem($this->db, $this->logger),
        ];
        
        // Initialize classifications
        $this->classifications = [
            'hadith' => new \IslamWiki\Core\Knowledge\Classifications\HadithClassification($this->db, $this->logger),
            'scholars' => new \IslamWiki\Core\Knowledge\Classifications\ScholarClassification($this->db, $this->logger),
            'topics' => new \IslamWiki\Core\Knowledge\Classifications\TopicClassification($this->db, $this->logger),
        ];
        
        // Initialize ontologies
        $this->ontologies = [
            'islamic_concepts' => new \IslamWiki\Core\Knowledge\Ontologies\IslamicConceptsOntology($this->db, $this->logger),
            'quranic_verses' => new \IslamWiki\Core\Knowledge\Ontologies\QuranicVersesOntology($this->db, $this->logger),
            'hadith_chain' => new \IslamWiki\Core\Knowledge\Ontologies\HadithChainOntology($this->db, $this->logger),
        ];
        
        // Initialize schema layers
        $this->schemaLayers = [
            'content' => new \IslamWiki\Core\Knowledge\SchemaLayers\ContentSchemaLayer($this->db, $this->logger),
            'relationships' => new \IslamWiki\Core\Knowledge\SchemaLayers\RelationshipSchemaLayer($this->db, $this->logger),
            'metadata' => new \IslamWiki\Core\Knowledge\SchemaLayers\MetadataSchemaLayer($this->db, $this->logger),
        ];
    }
    
    /**
     * Extract principles from a text (like Qur'anic verses).
     */
    public function extractPrinciples(string $text, string $type = 'quranic'): array
    {
        try {
            $this->logger->info('Extracting principles from text', [
                'type' => $type,
                'text_length' => strlen($text),
            ]);
            
            if (!isset($this->rootSystems[$type])) {
                throw new \InvalidArgumentException("Unknown root system type: {$type}");
            }
            
            $rootSystem = $this->rootSystems[$type];
            $principles = $rootSystem->extractPrinciples($text);
            
            $this->logger->info('Principles extracted successfully', [
                'type' => $type,
                'principles_count' => count($principles),
            ]);
            
            return $principles;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to extract principles', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Get related concepts for a given term.
     */
    public function getRelatedConcepts(string $term, string $classification = 'topics'): array
    {
        try {
            $this->logger->info('Getting related concepts', [
                'term' => $term,
                'classification' => $classification,
            ]);
            
            if (!isset($this->classifications[$classification])) {
                throw new \InvalidArgumentException("Unknown classification: {$classification}");
            }
            
            $classifier = $this->classifications[$classification];
            $concepts = $classifier->getRelatedConcepts($term);
            
            $this->logger->info('Related concepts retrieved', [
                'term' => $term,
                'concepts_count' => count($concepts),
            ]);
            
            return $concepts;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get related concepts', [
                'term' => $term,
                'classification' => $classification,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Build category tree for a given domain.
     */
    public function buildCategoryTree(string $domain, int $depth = 3): array
    {
        try {
            $this->logger->info('Building category tree', [
                'domain' => $domain,
                'depth' => $depth,
            ]);
            
            $tree = [];
            
            // Get root categories
            $rootCategories = $this->db->select(
                'SELECT * FROM usul_categories WHERE domain = ? AND parent_id IS NULL ORDER BY name',
                [$domain]
            );
            
            foreach ($rootCategories as $category) {
                $tree[] = $this->buildCategoryNode($category, $depth);
            }
            
            $this->logger->info('Category tree built successfully', [
                'domain' => $domain,
                'root_categories' => count($rootCategories),
            ]);
            
            return $tree;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to build category tree', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Build a category node recursively.
     */
    private function buildCategoryNode(array $category, int $depth): array
    {
        $node = [
            'id' => $category['id'],
            'name' => $category['name'],
            'description' => $category['description'],
            'children' => [],
        ];
        
        if ($depth > 0) {
            $children = $this->db->select(
                'SELECT * FROM usul_categories WHERE parent_id = ? ORDER BY name',
                [$category['id']]
            );
            
            foreach ($children as $child) {
                $node['children'][] = $this->buildCategoryNode($child, $depth - 1);
            }
        }
        
        return $node;
    }
    
    /**
     * Create a new ontology.
     */
    public function createOntology(string $name, array $config): bool
    {
        try {
            $this->logger->info('Creating ontology', [
                'name' => $name,
                'config' => $config,
            ]);
            
            // Validate ontology configuration
            $this->validateOntologyConfig($config);
            
            // Create ontology in database
            $this->db->insert(
                'INSERT INTO usul_ontologies (name, config, created_at) VALUES (?, ?, ?)',
                [$name, json_encode($config), date('Y-m-d H:i:s')]
            );
            
            $this->logger->info('Ontology created successfully', [
                'name' => $name,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create ontology', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Validate ontology configuration.
     */
    private function validateOntologyConfig(array $config): void
    {
        $required = ['entities', 'relationships', 'rules'];
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }
    
    /**
     * Get schema layer for a given type.
     */
    public function getSchemaLayer(string $type): ?SchemaLayerInterface
    {
        return $this->schemaLayers[$type] ?? null;
    }
    
    /**
     * Get root system for a given type.
     */
    public function getRootSystem(string $type): ?RootSystemInterface
    {
        return $this->rootSystems[$type] ?? null;
    }
    
    /**
     * Get classification for a given type.
     */
    public function getClassification(string $type): ?ClassificationInterface
    {
        return $this->classifications[$type] ?? null;
    }
    
    /**
     * Get ontology for a given type.
     */
    public function getOntology(string $type): ?OntologyInterface
    {
        return $this->ontologies[$type] ?? null;
    }
    
    /**
     * Search knowledge base.
     */
    public function search(string $query, array $options = []): array
    {
        try {
            $this->logger->info('Searching knowledge base', [
                'query' => $query,
                'options' => $options,
            ]);
            
            $results = [];
            
            // Search in different knowledge systems
            foreach ($this->ontologies as $type => $ontology) {
                $ontologyResults = $ontology->search($query, $options);
                $results[$type] = $ontologyResults;
            }
            
            foreach ($this->classifications as $type => $classification) {
                $classificationResults = $classification->search($query, $options);
                $results[$type] = $classificationResults;
            }
            
            $this->logger->info('Knowledge base search completed', [
                'query' => $query,
                'results_count' => count($results),
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to search knowledge base', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
} 