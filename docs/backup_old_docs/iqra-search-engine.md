# Iqra Search Engine

## Overview

The Iqra Search Engine is an advanced, Islamic-content-optimized search system designed specifically for IslamWiki. Named after the first word revealed in the Quran ("Iqra" - "Read"), this search engine provides intelligent, context-aware search capabilities for Islamic knowledge.

## Features

### 🔍 Core Search Capabilities

- **Multi-Content Type Search**: Search across pages, Quran verses, Hadith, calendar events, prayer times, and scholars
- **Islamic Term Recognition**: Automatically recognizes and prioritizes Islamic terms and concepts
- **Arabic Text Support**: Full support for Arabic text search with proper Unicode handling
- **Relevance Scoring**: Advanced relevance scoring based on Islamic content importance
- **Fuzzy Matching**: Intelligent matching for Islamic terms and transliterations

### 🎯 Advanced Features

- **Query Normalization**: Cleans and normalizes search queries
- **Tokenization**: Breaks queries into meaningful search tokens
- **Stop Word Filtering**: Removes common words while preserving Islamic terms
- **Excerpt Generation**: Creates highlighted excerpts showing search term matches
- **Search Suggestions**: Provides intelligent search suggestions and autocomplete
- **Analytics**: Comprehensive search analytics and insights

### 📊 Search Analytics

- **Query Analysis**: Detailed analysis of search queries
- **Content Distribution**: Statistics across different content types
- **Relevance Insights**: High-relevance terms and related topics
- **Performance Metrics**: Search timing and result statistics

## Architecture

### Core Components

1. **IqraSearchEngine** (`src/Core/Search/IqraSearchEngine.php`)
   - Main search engine implementation
   - Handles all search logic and relevance scoring
   - Manages Islamic terms and stop words

2. **IqraSearchController** (`src/Http/Controllers/IqraSearchController.php`)
   - Web interface controller
   - Handles HTTP requests and responses
   - Provides API endpoints for search functionality

3. **Search Views** (`resources/views/iqra-search/`)
   - User interface templates
   - Search results display
   - Advanced search options

### Search Types

The Iqra search engine supports searching across multiple content types:

- **Pages**: Wiki pages and articles
- **Quran**: Verses and surahs with Arabic text and translations
- **Hadith**: Islamic traditions with authenticity ratings
- **Calendar**: Islamic events and important dates
- **Prayer**: Prayer times and locations
- **Scholars**: Islamic scholars and their works

## Usage

### Web Interface

Visit `/iqra-search` to access the full search interface with:
- Advanced search options
- Content type filtering
- Sort and order controls
- Real-time suggestions

### API Endpoints

#### Search API
```
GET /api/iqra/search?q={query}&type={type}&page={page}&limit={limit}
```

Parameters:
- `q`: Search query (required)
- `type`: Content type (all, pages, quran, hadith, calendar, prayer, scholars)
- `page`: Page number for pagination
- `limit`: Results per page
- `sort`: Sort field (relevance, date, title, type)
- `order`: Sort order (asc, desc)

#### Suggestions API
```
GET /api/iqra/suggestions?q={query}
```

Returns search suggestions for autocomplete functionality.

#### Analytics API
```
GET /api/iqra/analytics?q={query}
```

Returns detailed search analytics and insights.

### Code Examples

#### Basic Search
```php
use IslamWiki\Core\Search\IqraSearchEngine;

$searchEngine = new IqraSearchEngine($db);
$results = $searchEngine->search('allah', [
    'type' => 'all',
    'limit' => 20,
    'page' => 1
]);
```

#### Search with Analytics
```php
$analytics = $searchEngine->getSearchAnalytics('quran hadith');
$suggestions = $searchEngine->getSuggestions('allah');
```

## Islamic Content Optimization

### Islamic Terms Recognition

The search engine maintains a comprehensive list of Islamic terms:

```php
$islamicTerms = [
    'allah', 'muhammad', 'quran', 'hadith', 'sunnah', 'shariah',
    'halal', 'haram', 'salah', 'prayer', 'ramadan', 'eid',
    'hajj', 'umrah', 'zakat', 'sadaqah', 'jannah', 'akhirah',
    'taqwa', 'iman', 'islam', 'muslim', 'sahaba', 'tabiun',
    'madhhab', 'fiqh', 'usul', 'aqeedah', 'tawhid', 'shirk',
    'bidah', 'dua', 'dhikr', 'tasbih', 'istighfar'
];
```

### Arabic Text Support

Full support for Arabic text with proper Unicode handling:

```php
public function containsArabic(string $query): bool
{
    return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $query);
}
```

### Relevance Scoring

Advanced relevance scoring based on Islamic content importance:

- **Quran**: Highest priority (weight: 1.5)
- **Hadith**: High priority (weight: 1.3)
- **Scholars**: High priority (weight: 1.2)
- **Calendar**: Medium-high (weight: 1.1)
- **Pages**: Standard (weight: 1.0)
- **Prayer**: Lower priority (weight: 0.9)

## Search Features

### Query Processing

1. **Normalization**: Removes extra whitespace, converts to lowercase
2. **Tokenization**: Breaks query into searchable words
3. **Stop Word Filtering**: Removes common words while preserving Islamic terms
4. **Arabic Detection**: Identifies Arabic text for special handling

### Excerpt Generation

Creates highlighted excerpts showing search term matches:

```php
$excerpt = $searchEngine->createHighlightedExcerpt($content, $query);
// Returns: "The <mark>Quran</mark> is the holy book of <mark>Islam</mark>..."
```

### Search Suggestions

Provides intelligent suggestions based on:
- Page titles and slugs
- Quran verses and surahs
- Hadith collections and numbers
- Popular search terms

## Performance

### Optimization Features

- **Efficient SQL Queries**: Optimized database queries with proper indexing
- **Caching**: Search results caching for improved performance
- **Pagination**: Efficient pagination for large result sets
- **Relevance Scoring**: Fast relevance calculation using database functions

### Database Indexes

Recommended indexes for optimal performance:

```sql
-- Pages table
CREATE INDEX idx_pages_title_content ON pages(title, content);
CREATE INDEX idx_pages_updated_at ON pages(updated_at);

-- Quran verses
CREATE INDEX idx_verses_arabic_translation ON verses(arabic_text, translation);
CREATE INDEX idx_verses_surah_verse ON verses(surah_id, verse_number);

-- Hadith
CREATE INDEX idx_hadith_arabic_translation ON hadiths(arabic_text, translation);
CREATE INDEX idx_hadith_collection_number ON hadiths(collection_id, hadith_number);
```

## Testing

### Test Files

- `public/test-iqra-search.php`: Comprehensive functionality test
- `public/test-container-simple.php`: Container integration test
- `public/test-container.php`: Full application test

### Test Coverage

The Iqra search engine includes tests for:

- ✅ Query normalization and tokenization
- ✅ Arabic text detection
- ✅ Islamic terms recognition
- ✅ Excerpt creation with highlighting
- ✅ Search analytics and insights
- ✅ Related topics and suggestions
- ✅ Container integration
- ✅ Database connectivity

## Configuration

### Search Options

```php
$searchOptions = [
    'type' => 'all',           // Content type filter
    'page' => 1,               // Page number
    'limit' => 20,             // Results per page
    'sort' => 'relevance',     // Sort field
    'order' => 'desc'          // Sort order
];
```

### Islamic Terms Configuration

Islamic terms can be customized by modifying the `loadIslamicTerms()` method in the search engine.

### Stop Words Configuration

English and Arabic stop words can be customized in the `loadStopWords()` method.

## Future Enhancements

### Planned Features

1. **Semantic Search**: AI-powered semantic understanding of Islamic concepts
2. **Multi-language Support**: Enhanced support for multiple languages
3. **Advanced Filtering**: More granular filtering options
4. **Search History**: User search history and recommendations
5. **Voice Search**: Voice input support for search queries
6. **Image Search**: Search within Islamic images and calligraphy

### Technical Improvements

1. **Elasticsearch Integration**: Full-text search engine integration
2. **Machine Learning**: ML-based relevance scoring
3. **Real-time Updates**: Live search result updates
4. **Advanced Analytics**: More detailed search analytics
5. **API Rate Limiting**: Proper API rate limiting and caching

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure database is properly configured
2. **Missing Tables**: Run migrations to create required tables
3. **Performance Issues**: Check database indexes and query optimization
4. **Arabic Text Issues**: Verify proper UTF-8 encoding

### Debug Information

Enable debug logging to troubleshoot search issues:

```php
$logger->debug('Search query: ' . $query);
$logger->debug('Search results: ' . json_encode($results));
```

## Contributing

When contributing to the Iqra search engine:

1. Follow the existing code structure and patterns
2. Add comprehensive tests for new features
3. Update documentation for any API changes
4. Ensure Islamic content accuracy and relevance
5. Consider performance implications of changes

## License

The Iqra Search Engine is part of IslamWiki and is licensed under the GNU Affero General Public License v3.0. 