# Hybrid Translation System

## Overview

The Hybrid Translation System is a comprehensive solution that combines Google Translate API integration with local translation memory and subdomain-based language switching. This system provides seamless multilingual support for IslamWiki with intelligent fallbacks and performance optimization.

## Features

### 🌐 **Multi-Language Support**
- **8 Languages**: English, Arabic, Urdu, Turkish, Indonesian, Malay, Persian, Hebrew
- **RTL Support**: Full right-to-left layout support for Arabic, Urdu, Persian, and Hebrew
- **Language Detection**: Automatic language detection from subdomains, sessions, and browser preferences

### 🔄 **Subdomain-Based Language Switching**
- **URL Pattern**: `{language}.{domain}/{current-page}`
- **Examples**:
  - `en.local.islam.wiki/quran` (English - default)
  - `ar.local.islam.wiki/quran` (Arabic)
  - `ur.local.islam.wiki/quran` (Urdu)
  - `tr.local.islam.wiki/quran` (Turkish)
- **Path Preservation**: Maintains current page when switching languages
- **Automatic Redirects**: Seamlessly redirects between language subdomains

### 🤖 **Google Translate API Integration**
- **Real-time Translation**: Instant translation of content via Google Translate API
- **Batch Processing**: Efficient batch translation for multiple texts
- **Quality Scoring**: Automatic quality assessment of translations
- **Fallback System**: Graceful degradation when API is unavailable

### 💾 **Translation Memory & Caching**
- **Local Cache**: Stores translations locally for faster access
- **Memory Management**: Intelligent memory management with configurable limits
- **Quality Thresholds**: Configurable quality thresholds for cached translations
- **Persistence**: Translations persist across sessions and page reloads

### 📊 **Quality Assurance**
- **Automatic Scoring**: Quality metrics for translation accuracy
- **User Feedback**: System for users to provide translation feedback
- **Continuous Improvement**: Learning from user feedback to improve translations

## Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Hybrid Translation System                │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐ │
│  │ Translation     │  │ Subdomain       │  │ Language    │ │
│  │ Service         │  │ Middleware      │  │ Controller  │ │
│  └─────────────────┘  └─────────────────┘  └─────────────┘ │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐ │
│  │ Google Translate│  │ Translation     │  │ Cache       │ │
│  │ API Client      │  │ Memory          │  │ System      │ │
│  └─────────────────┘  └─────────────────┘  └─────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **Request Processing**: SubdomainLanguageMiddleware detects language from URL
2. **Language Detection**: Determines current language and sets session data
3. **Content Translation**: TranslationService processes content with fallback strategies
4. **Response Generation**: Language-specific headers and content returned
5. **Caching**: Successful translations stored in memory and cache

## Configuration

### Environment Variables

Create a `.env` file with the following configuration:

```bash
# Google Translate API
GOOGLE_TRANSLATE_API_KEY=your_api_key_here

# Language Configuration
DEFAULT_LANGUAGE=en
SUPPORTED_LANGUAGES=en,ar,ur,tr,id,ms,fa,he

# Translation Settings
TRANSLATION_CACHE_TTL=86400
TRANSLATION_MEMORY_SIZE=1000
TRANSLATION_QUALITY_THRESHOLD=0.7

# Subdomain Settings
BASE_DOMAIN=local.islam.wiki
ENABLE_LANGUAGE_SUBDOMAINS=true
```

### Configuration File

The system uses `config/translation.php` for detailed configuration:

```php
return [
    'default_language' => 'en',
    'supported_languages' => [
        'en' => [
            'name' => 'English',
            'subdomain' => null, // No subdomain for default
            'direction' => 'ltr'
        ],
        'ar' => [
            'name' => 'Arabic',
            'subdomain' => 'ar',
            'direction' => 'rtl'
        ],
        // ... more languages
    ],
    'google_translate' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'batch_size' => 50
    ]
];
```

## Usage

### Basic Language Switching

The system automatically handles language switching based on subdomains:

```php
// User visits: ar.local.islam.wiki/quran
// System automatically:
// 1. Detects Arabic language from subdomain
// 2. Sets session language to 'ar'
// 3. Applies RTL layout
// 4. Translates content to Arabic
```

### API Endpoints

#### Get Current Language
```http
GET /language/current
```

#### Switch Language
```http
GET /language/switch/{language}
```

#### Translate Text
```http
POST /language/translate
Content-Type: application/json

{
    "text": "Hello World",
    "target_language": "ar",
    "source_language": "en"
}
```

#### Batch Translation
```http
POST /language/translate/batch
Content-Type: application/json

{
    "texts": ["Hello", "World", "Welcome"],
    "target_language": "ar"
}
```

### Frontend Integration

Include the enhanced language switch component:

```twig
{% include 'components/enhanced-language-switch.twig' %}
```

The component automatically:
- Detects current language from subdomain
- Provides language switching interface
- Handles subdomain redirects
- Shows translation status

## Implementation Details

### Translation Service

The `TranslationService` class provides the core translation functionality:

```php
class TranslationService
{
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        // 1. Check translation memory
        // 2. Check cache
        // 3. Use Google Translate API
        // 4. Store in memory and cache
        // 5. Return translation
    }
    
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        // Batch translation with API limits
    }
}
```

### Subdomain Middleware

The `SubdomainLanguageMiddleware` handles language detection and routing:

```php
class SubdomainLanguageMiddleware
{
    public function process(Request $request, callable $next): Response
    {
        // 1. Extract language from host
        // 2. Set language in request attributes
        // 3. Update session
        // 4. Process request
        // 5. Add language headers to response
    }
}
```

### Language Controller

The `LanguageController` provides API endpoints for language management:

```php
class LanguageController
{
    public function switchLanguage(Request $request, string $language): Response
    {
        // 1. Validate language
        // 2. Generate subdomain URL
        // 3. Redirect to language-specific subdomain
    }
}
```

## Performance Optimization

### Caching Strategy

1. **Memory Cache**: Fastest access for frequently used translations
2. **Database Cache**: Persistent storage for larger translation sets
3. **API Caching**: Reduces Google Translate API calls
4. **Batch Processing**: Minimizes API requests for multiple translations

### Memory Management

- Configurable memory limits
- LRU (Least Recently Used) eviction
- Automatic cleanup of low-quality translations
- Memory usage monitoring

### Quality Metrics

The system automatically scores translation quality:

- **Length Ratio**: Compares original vs. translated text length
- **Character Set Consistency**: Ensures proper character encoding
- **HTML Entity Check**: Detects encoding issues
- **Punctuation Check**: Validates punctuation preservation

## Security Considerations

### API Key Protection

- Store API keys in environment variables
- Never expose keys in client-side code
- Implement rate limiting for API endpoints
- Monitor API usage and costs

### Input Validation

- Validate all language codes
- Sanitize input text before translation
- Implement CSRF protection for language switching
- Rate limit translation requests

### Session Security

- Secure session configuration
- Language preference validation
- Subdomain validation
- HTTPS enforcement for production

## Monitoring & Analytics

### Translation Statistics

Track translation system performance:

```php
$stats = $translationService->getTranslationStats();
// Returns:
// - Memory size
// - Supported languages count
// - API configuration status
// - Cache status
```

### Quality Metrics

Monitor translation quality over time:

- Average quality scores
- User feedback ratings
- Fallback usage statistics
- API response times

### Performance Metrics

Track system performance:

- Cache hit rates
- Memory usage patterns
- API call frequency
- Response times

## Troubleshooting

### Common Issues

#### API Key Not Configured
```
Warning: Google Translate API key not configured
```
**Solution**: Set `GOOGLE_TRANSLATE_API_KEY` in your `.env` file

#### Subdomain Not Working
```
Error: Language subdomain not accessible
```
**Solution**: Ensure DNS is configured for language subdomains

#### Translation Quality Issues
```
Warning: Translation quality below threshold
```
**Solution**: Check API responses and adjust quality thresholds

### Debug Mode

Enable debug mode for detailed logging:

```bash
APP_DEBUG=true
LOG_LEVEL=debug
```

### Testing

Test the translation system:

```bash
# Test language switching
curl "http://ar.local.islam.wiki/language/current"

# Test translation
curl -X POST "http://en.local.islam.wiki/language/translate" \
  -H "Content-Type: application/json" \
  -d '{"text":"Hello","target_language":"ar"}'
```

## Future Enhancements

### Planned Features

- **Neural Machine Translation**: Integration with advanced NMT services
- **Community Translations**: User-contributed translation improvements
- **Translation Memory Sharing**: Collaborative translation memory
- **Advanced Quality Metrics**: Machine learning-based quality assessment
- **Offline Translation**: Local translation models for offline use

### Extension Points

The system is designed for extensibility:

- Custom translation providers
- Language-specific translation rules
- Advanced caching strategies
- Quality improvement algorithms

## Support

### Documentation

- [API Reference](api-reference.md)
- [Configuration Guide](configuration.md)
- [Troubleshooting](troubleshooting.md)
- [Performance Tuning](performance.md)

### Community

- [GitHub Issues](https://github.com/islamwiki/islamwiki/issues)
- [Discord Community](https://discord.gg/islamwiki)
- [Documentation Wiki](https://docs.islam.wiki)

---

*This documentation is part of IslamWiki and is maintained by the development team.* 