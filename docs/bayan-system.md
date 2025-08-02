# Bayan Knowledge Graph System

## Overview

The Bayan Knowledge Graph System is a comprehensive platform for connecting Islamic knowledge through a graph-based approach. It enables users to create nodes representing Islamic concepts, verses, hadith, scholars, and other entities, then establish relationships between them to discover connections and insights.

## Architecture

### Core Components

#### BayanManager
The main orchestrator for the knowledge graph system that coordinates all operations.

**Key Methods:**
- `createNode(array $data): ?int` - Create a new knowledge node
- `createRelationship(int $sourceId, int $targetId, string $type, array $attributes = []): ?int` - Create a relationship
- `findNodes(string $type, array $criteria = []): array` - Find nodes by type and criteria
- `getRelatedNodes(int $nodeId, string $relationshipType = null, int $limit = 10): array` - Get related nodes
- `findPaths(int $sourceId, int $targetId, int $maxDepth = 3): array` - Find paths between nodes
- `search(string $query, array $filters = [], int $limit = 20): array` - Search the knowledge graph
- `getStatistics(): array` - Get graph statistics

#### NodeManager
Handles knowledge graph nodes representing Islamic concepts, verses, hadith, scholars, etc.

**Key Methods:**
- `create(array $data): ?int` - Create a new node
- `findById(int $id): ?array` - Find a node by ID
- `findByType(string $type, array $criteria = []): array` - Find nodes by type
- `update(int $id, array $data): bool` - Update a node
- `delete(int $id): bool` - Soft delete a node
- `search(string $query, array $filters = [], int $limit = 20): array` - Search nodes

#### EdgeManager
Manages relationships (edges) between knowledge graph nodes.

**Key Methods:**
- `create(int $sourceId, int $targetId, string $type, array $attributes = []): ?int` - Create a relationship
- `findByNodes(int $sourceId, int $targetId, string $type = null): array` - Find relationships between nodes
- `findByNode(int $nodeId, string $direction = 'both'): array` - Find all relationships for a node
- `findByType(string $type, int $limit = 100): array` - Find relationships by type
- `update(int $id, array $attributes): bool` - Update relationship attributes
- `delete(int $id): bool` - Soft delete a relationship

#### QueryManager
Handles complex graph queries, traversals, and search operations.

**Key Methods:**
- `getRelatedNodes(int $nodeId, string $relationshipType = null, int $limit = 10): array` - Get related nodes
- `findPaths(int $sourceId, int $targetId, int $maxDepth = 3): array` - Find paths between nodes
- `search(string $query, array $filters = [], int $limit = 20): array` - Search the knowledge graph
- `getHubNodes(int $limit = 10): array` - Get nodes with the most relationships
- `getNodesByCentrality(string $centralityType = 'degree', int $limit = 10): array` - Get nodes by centrality
- `getGraphMetrics(): array` - Get graph statistics and metrics

## Database Schema

### Tables

#### bayan_nodes
Stores knowledge graph nodes representing Islamic concepts, verses, hadith, scholars, etc.

```sql
CREATE TABLE bayan_nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL COMMENT 'Node type: concept, verse, hadith, scholar, etc.',
    title VARCHAR(255) NOT NULL COMMENT 'Node title/name',
    content TEXT NOT NULL COMMENT 'Node content/description',
    metadata JSON DEFAULT '{}' COMMENT 'Additional metadata as JSON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_type (type),
    INDEX idx_title (title),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at),
    FULLTEXT idx_content (title, content)
);
```

#### bayan_edges
Stores relationships between knowledge graph nodes.

```sql
CREATE TABLE bayan_edges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id INT NOT NULL COMMENT 'Source node ID',
    target_id INT NOT NULL COMMENT 'Target node ID',
    type VARCHAR(50) NOT NULL COMMENT 'Relationship type: references, explains, authored_by, etc.',
    attributes JSON DEFAULT '{}' COMMENT 'Relationship attributes as JSON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (source_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
    FOREIGN KEY (target_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
    INDEX idx_source_id (source_id),
    INDEX idx_target_id (target_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at),
    UNIQUE KEY unique_relationship (source_id, target_id, type, deleted_at)
);
```

#### bayan_node_types
Predefined node types for the knowledge graph.

```sql
CREATE TABLE bayan_node_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE COMMENT 'Node type identifier',
    name VARCHAR(100) NOT NULL COMMENT 'Human-readable name',
    description TEXT COMMENT 'Type description',
    icon VARCHAR(50) COMMENT 'Icon identifier',
    color VARCHAR(7) COMMENT 'Hex color code',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether this type is active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);
```

#### bayan_edge_types
Predefined relationship types for the knowledge graph.

```sql
CREATE TABLE bayan_edge_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE COMMENT 'Relationship type identifier',
    name VARCHAR(100) NOT NULL COMMENT 'Human-readable name',
    description TEXT COMMENT 'Relationship description',
    is_directed BOOLEAN DEFAULT TRUE COMMENT 'Whether this relationship is directed',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether this type is active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);
```

#### bayan_graph_metrics
Cached graph metrics and statistics.

```sql
CREATE TABLE bayan_graph_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(100) NOT NULL COMMENT 'Metric identifier',
    metric_value JSON NOT NULL COMMENT 'Metric value as JSON',
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_name (metric_name),
    INDEX idx_calculated_at (calculated_at)
);
```

#### bayan_search_index
Search index for knowledge graph nodes.

```sql
CREATE TABLE bayan_search_index (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL COMMENT 'Reference to bayan_nodes.id',
    search_text TEXT NOT NULL COMMENT 'Searchable text content',
    search_vector TEXT COMMENT 'Full-text search vector',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (node_id) REFERENCES bayan_nodes(id) ON DELETE CASCADE,
    INDEX idx_node_id (node_id),
    FULLTEXT idx_search_text (search_text)
);
```

## Node Types

### Default Node Types

1. **Concept** - Islamic concepts and terms
   - Examples: Tawhid, Shirk, Iman, Taqwa
   - Color: #4CAF50 (Green)
   - Icon: lightbulb

2. **Verse** - Quran verses
   - Examples: Al-Fatiha 1:1, Al-Baqarah 2:255
   - Color: #2196F3 (Blue)
   - Icon: book-open

3. **Hadith** - Prophet's sayings and actions
   - Examples: Bukhari 1:1, Muslim 1:1
   - Color: #FF9800 (Orange)
   - Icon: quote-right

4. **Scholar** - Islamic scholars and authorities
   - Examples: Imam Abu Hanifa, Imam Shafi'i
   - Color: #9C27B0 (Purple)
   - Icon: user-graduate

5. **School** - Schools of thought and madhabs
   - Examples: Hanafi, Shafi'i, Maliki, Hanbali
   - Color: #795548 (Brown)
   - Icon: building

6. **Event** - Historical events
   - Examples: Battle of Badr, Conquest of Mecca
   - Color: #F44336 (Red)
   - Icon: calendar-alt

7. **Place** - Important places in Islamic history
   - Examples: Mecca, Medina, Jerusalem
   - Color: #607D8B (Blue Grey)
   - Icon: map-marker-alt

8. **Person** - Important figures
   - Examples: Prophet Muhammad, Abu Bakr, Umar
   - Color: #E91E63 (Pink)
   - Icon: user

9. **Book** - Islamic books and texts
   - Examples: Sahih Bukhari, Sahih Muslim
   - Color: #3F51B5 (Indigo)
   - Icon: book

10. **Topic** - General topics and subjects
    - Examples: Aqeedah, Fiqh, Tafsir
    - Color: #009688 (Teal)
    - Icon: folder

## Relationship Types

### Default Relationship Types

1. **References** - One concept references another
   - Directed: Yes
   - Example: A verse references a concept

2. **Explains** - One concept explains another
   - Directed: Yes
   - Example: A hadith explains a verse

3. **Authored By** - Content authored by a scholar
   - Directed: Yes
   - Example: A book is authored by a scholar

4. **Belongs To** - Concept belongs to a category
   - Directed: Yes
   - Example: A concept belongs to a topic

5. **Related To** - General relationship between concepts
   - Directed: No
   - Example: Two concepts are related

6. **Mentions** - One concept mentions another
   - Directed: Yes
   - Example: A text mentions a person

7. **Derived From** - Concept derived from another
   - Directed: Yes
   - Example: A ruling derived from a verse

8. **Similar To** - Similar concepts
   - Directed: No
   - Example: Two similar concepts

9. **Opposes** - Opposing concepts
   - Directed: Yes
   - Example: Tawhid opposes Shirk

10. **Supports** - Supporting evidence or concept
    - Directed: Yes
    - Example: A hadith supports a ruling

## Usage Examples

### Creating Nodes

```php
// Create a concept node
$nodeData = [
    'type' => 'concept',
    'title' => 'Tawhid (Monotheism)',
    'content' => 'Tawhid is the Islamic concept of monotheism, the belief in the oneness of Allah. It is the fundamental principle of Islam.',
    'metadata' => [
        'category' => 'Aqeedah',
        'importance' => 'fundamental',
        'arabic' => 'التوحيد'
    ]
];
$nodeId = $bayanManager->createNode($nodeData);

// Create a verse node
$verseData = [
    'type' => 'verse',
    'title' => 'Al-Ikhlas 112:1',
    'content' => 'Say, "He is Allah, [who is] One."',
    'metadata' => [
        'surah' => 'Al-Ikhlas',
        'verse' => 1,
        'arabic' => 'قُلْ هُوَ اللَّهُ أَحَدٌ'
    ]
];
$verseId = $bayanManager->createNode($verseData);
```

### Creating Relationships

```php
// Create a relationship between a verse and a concept
$relationshipId = $bayanManager->createRelationship(
    $verseId,  // Al-Ikhlas 112:1
    $nodeId,   // Tawhid concept
    'explains',
    [
        'strength' => 'strong',
        'reason' => 'This verse directly explains the concept of Tawhid'
    ]
);

// Create an opposing relationship
$shirkNodeId = $bayanManager->createNode([
    'type' => 'concept',
    'title' => 'Shirk (Polytheism)',
    'content' => 'Shirk is the sin of practicing idolatry or polytheism.',
    'metadata' => ['category' => 'Aqeedah']
]);

$opposesId = $bayanManager->createRelationship(
    $nodeId,   // Tawhid
    $shirkNodeId, // Shirk
    'opposes',
    [
        'strength' => 'strong',
        'reason' => 'Tawhid and Shirk are opposite concepts in Islamic theology'
    ]
);
```

### Searching the Graph

```php
// Search for nodes containing "Tawhid"
$results = $bayanManager->search('Tawhid');

// Search for concept nodes only
$concepts = $bayanManager->search('', ['type' => 'concept']);

// Search for nodes with specific relationships
$relatedToTawhid = $bayanManager->search('', ['relationship_type' => 'explains']);
```

### Finding Related Nodes

```php
// Get all nodes related to Tawhid
$relatedNodes = $bayanManager->getRelatedNodes($nodeId);

// Get only nodes that explain Tawhid
$explainingNodes = $bayanManager->getRelatedNodes($nodeId, 'explains');

// Get nodes that Tawhid opposes
$opposingNodes = $bayanManager->getRelatedNodes($nodeId, 'opposes');
```

### Path Finding

```php
// Find paths between two nodes
$paths = $bayanManager->findPaths($sourceNodeId, $targetNodeId, 3);

// Each path contains the sequence of nodes and relationships
foreach ($paths as $path) {
    foreach ($path as $step) {
        echo "From: " . $step['from_node']['title'] . "\n";
        echo "To: " . $step['to_node']['title'] . "\n";
        echo "Via: " . $step['relationships'][0]['type'] . "\n";
    }
}
```

### Graph Statistics

```php
// Get overall statistics
$stats = $bayanManager->getStatistics();
echo "Total nodes: " . $stats['total_nodes'] . "\n";
echo "Total relationships: " . $stats['total_relationships'] . "\n";

// Get metrics
$metrics = $bayanManager->getQueryManager()->getGraphMetrics();
echo "Average degree: " . $metrics['average_degree'] . "\n";
echo "Node types: " . print_r($metrics['node_types'], true) . "\n";
```

## Web Interface

### Routes

- `/bayan` - Main dashboard with statistics
- `/bayan/search` - Search interface with filters
- `/bayan/create` - Node creation form
- `/bayan/node/{id}` - Node details and relationships
- `/bayan/statistics` - Graph statistics and metrics
- `/bayan/paths` - Path finding between nodes

### API Endpoints

#### Create Node
```http
POST /bayan/create
Content-Type: application/json

{
    "type": "concept",
    "title": "Tawhid (Monotheism)",
    "content": "Tawhid is the Islamic concept of monotheism...",
    "metadata": {
        "category": "Aqeedah",
        "importance": "fundamental"
    }
}
```

#### Create Relationship
```http
POST /bayan/relationship
Content-Type: application/json

{
    "source_id": 1,
    "target_id": 2,
    "type": "explains",
    "attributes": {
        "strength": "strong",
        "reason": "Direct explanation"
    }
}
```

#### Get Statistics
```http
GET /bayan/statistics
```

Response:
```json
{
    "total_nodes": 150,
    "total_relationships": 300,
    "node_types": {
        "concept": 50,
        "verse": 30,
        "hadith": 40,
        "scholar": 20,
        "school": 10
    },
    "relationship_types": {
        "explains": 100,
        "references": 80,
        "opposes": 30,
        "supports": 90
    }
}
```

#### Find Paths
```http
GET /bayan/paths?source_id=1&target_id=5&max_depth=3
```

## Performance Considerations

### Database Optimization

1. **Indexes** - All tables have appropriate indexes for fast queries
2. **Full-text Search** - Nodes table has full-text index on title and content
3. **Foreign Keys** - Proper foreign key constraints for data integrity
4. **Soft Deletes** - Uses soft deletes to preserve data relationships

### Query Optimization

1. **Prepared Statements** - All queries use prepared statements
2. **Pagination** - Search results are paginated for large datasets
3. **Caching** - Graph metrics are cached for dashboard performance
4. **Efficient Joins** - Optimized joins for relationship queries

### Memory Management

1. **Lazy Loading** - Related nodes are loaded on demand
2. **Result Limiting** - Queries limit results to prevent memory issues
3. **Batch Processing** - Large operations are processed in batches

## Security Features

### Input Validation

1. **Node Creation** - Validates required fields and data types
2. **Relationship Creation** - Ensures both nodes exist before creating relationship
3. **Search Queries** - Sanitizes search input to prevent injection
4. **Metadata Validation** - Validates JSON metadata structure

### SQL Injection Prevention

1. **Prepared Statements** - All database queries use prepared statements
2. **Parameter Binding** - User input is properly bound to query parameters
3. **Input Sanitization** - All user input is sanitized before processing

### XSS Protection

1. **Output Encoding** - All output is properly encoded
2. **Content Sanitization** - User content is sanitized before storage
3. **Template Escaping** - Twig templates automatically escape output

### Error Handling

1. **Graceful Degradation** - System continues to function even with errors
2. **Secure Error Messages** - Error messages don't reveal sensitive information
3. **Comprehensive Logging** - All errors are logged for debugging

## Future Enhancements

### Planned Features

1. **Visual Graph Interface** - Interactive graph visualization
2. **Advanced Analytics** - Graph centrality and clustering analysis
3. **Import/Export** - Bulk data import and export capabilities
4. **Versioning** - Node and relationship version history
5. **Collaboration** - Multi-user editing and approval workflows
6. **Integration** - Connect with existing Quran and Hadith systems

### Performance Optimizations

1. **Caching** - Redis-based caching for frequently accessed data
2. **Indexing** - Advanced database indexing strategies
3. **Pagination** - Efficient pagination for large datasets
4. **CDN** - Content delivery network for static assets

### Advanced Features

1. **Machine Learning** - AI-powered relationship suggestions
2. **Natural Language Processing** - Automatic entity extraction
3. **Semantic Search** - Meaning-based search capabilities
4. **Graph Analytics** - Advanced graph analysis tools

## Troubleshooting

### Common Issues

1. **Node Creation Fails**
   - Check that all required fields are provided
   - Ensure the node type exists in the database
   - Verify database connection and permissions

2. **Relationship Creation Fails**
   - Ensure both source and target nodes exist
   - Check that the relationship type is valid
   - Verify that the relationship doesn't already exist

3. **Search Returns No Results**
   - Check the search query syntax
   - Verify that the search filters are correct
   - Ensure the database contains the expected data

4. **Performance Issues**
   - Check database indexes are properly created
   - Monitor query execution times
   - Consider implementing caching for frequently accessed data

### Debugging Tools

1. **Debug Scripts** - Use `debug-bayan.php` for system diagnostics
2. **Test Scripts** - Use `test-bayan.php` for functionality testing
3. **Log Files** - Check application logs for error details
4. **Database Queries** - Monitor database query performance

## Conclusion

The Bayan Knowledge Graph System provides a powerful foundation for Islamic knowledge management. Its graph-based approach enables users to discover relationships and insights that would be difficult to find through traditional search methods.

The system is designed to be extensible, secure, and performant, making it suitable for both small-scale and large-scale deployments. With its comprehensive API, modern web interface, and robust architecture, it serves as a solid foundation for future enhancements and integrations.

---

**Version:** 0.0.34  
**Last Updated:** August 1, 2025  
**Status:** Production Ready 