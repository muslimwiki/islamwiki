# Quran Namespace Structure

**Version:** 0.1.0  
**Status:** Production Ready - Complete Quran Integration  
**Last Updated:** 2025-01-27

The Quran namespace provides a comprehensive structure for organizing and accessing Islamic scripture content within IslamWiki, following Islamic scholarly traditions and modern web standards.

## Overview

The Quran namespace (`Quran:`) provides a hierarchical organization system for all Quran-related content, including:

- **Surah Structure**: Complete 114 surahs with proper Arabic numbering
- **Verse Organization**: Individual verses with cross-references
- **Juz Division**: Traditional 30 juz divisions for study and memorization
- **Translation Support**: Multi-language translations with source attribution
- **Tafsir Integration**: Scholarly commentary and interpretation
- **Search & Discovery**: Advanced search across all Quran content

## Namespace Hierarchy

### Primary Structure

```
Quran:
├── Surah:1-114          # Individual surah pages
├── Juz:1-30            # Juz division pages
├── Verse:1:1-114:6     # Individual verse pages
├── Translation:         # Translation collections
├── Tafsir:             # Commentary and interpretation
├── Search:             # Advanced search interface
└── Index:              # Master index and navigation
```

### Detailed Organization

#### **Surah Pages** (`Quran:Surah:1` to `Quran:Surah:114`)
- **URL Pattern**: `/quran/surah/{number}`
- **Content**: Complete surah text, translation, and metadata
- **Features**: Verse navigation, audio recitation, study tools

#### **Juz Pages** (`Quran:Juz:1` to `Quran:Juz:30`)
- **URL Pattern**: `/quran/juz/{number}`
- **Content**: Juz content with surah boundaries
- **Features**: Memorization tracking, study progress

#### **Verse Pages** (`Quran:Verse:1:1` to `Quran:Verse:114:6`)
- **URL Pattern**: `/quran/verse/{surah}:{ayah}`
- **Content**: Individual verse with context and translations
- **Features**: Cross-references, tafsir, related content

## Database Schema

### Core Quran Tables

#### **`quran_surahs`** - Surah Information
```sql
CREATE TABLE quran_surahs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    number INT UNIQUE NOT NULL,           -- 1-114
    arabic_name VARCHAR(100) NOT NULL,   -- Arabic surah name
    english_name VARCHAR(100) NOT NULL,  -- English transliteration
    english_meaning VARCHAR(200),        -- English meaning
    revelation_type ENUM('Meccan', 'Medinan'),
    verses_count INT NOT NULL,
    juz_start INT NOT NULL,              -- Starting juz
    ruku_count INT,                      -- Ruku divisions
    sajdah_ayahs TEXT,                   -- Sajdah verses (JSON)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **`quran_verses`** - Verse Content
```sql
CREATE TABLE quran_verses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    surah_number INT NOT NULL,
    ayah_number INT NOT NULL,
    arabic_text TEXT NOT NULL,
    page_number INT NOT NULL,
    juz_number INT NOT NULL,
    hizb_number INT NOT NULL,
    ruku_number INT,
    sajdah BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_verse (surah_number, ayah_number),
    FOREIGN KEY (surah_number) REFERENCES quran_surahs(number)
);
```

#### **`quran_translations`** - Multi-language Translations
```sql
CREATE TABLE quran_translations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    verse_id BIGINT NOT NULL,
    translator_name VARCHAR(100) NOT NULL,
    language_code VARCHAR(10) NOT NULL,  -- ISO 639-1
    translation_text TEXT NOT NULL,
    source_url VARCHAR(500),             -- Attribution source
    is_verified BOOLEAN DEFAULT FALSE,  -- Scholar verification
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verse_id) REFERENCES quran_verses(id),
    UNIQUE KEY unique_translation (verse_id, translator_name, language_code)
);
```

#### **`quran_tafsir`** - Commentary and Interpretation
```sql
CREATE TABLE quran_tafsir (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    verse_id BIGINT NOT NULL,
    scholar_name VARCHAR(100) NOT NULL,
    tafsir_text TEXT NOT NULL,
    source_book VARCHAR(200),            -- Book reference
    page_reference VARCHAR(50),          -- Page numbers
    is_verified BOOLEAN DEFAULT FALSE,  -- Scholar verification
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verse_id) REFERENCES quran_verses(id)
);
```

## URL Structure

### Primary Routes

```php
// Quran namespace routes
Route::prefix('quran')->group(function () {
    // Surah pages
    Route::get('/surah/{number}', [QuranController::class, 'showSurah'])
        ->where('number', '[1-9]|[1-9][0-9]|1[0-1][0-4]');
    
    // Juz pages
    Route::get('/juz/{number}', [QuranController::class, 'showJuz'])
        ->where('number', '[1-9]|[12][0-9]|30');
    
    // Verse pages
    Route::get('/verse/{surah}:{ayah}', [QuranController::class, 'showVerse'])
        ->where('surah', '[1-9]|[1-9][0-9]|1[0-1][0-4]')
        ->where('ayah', '[1-9]|[1-9][0-9]|[1-9][0-9][0-9]');
    
    // Search interface
    Route::get('/search', [QuranController::class, 'search']);
    
    // Master index
    Route::get('/', [QuranController::class, 'index']);
});
```

### URL Examples

| Content Type | URL Pattern | Example |
|--------------|-------------|---------|
| Surah | `/quran/surah/{number}` | `/quran/surah/1` (Al-Fatiha) |
| Juz | `/quran/juz/{number}` | `/quran/juz/1` (First Juz) |
| Verse | `/quran/verse/{surah}:{ayah}` | `/quran/verse/1:1` (Bismillah) |
| Search | `/quran/search` | `/quran/search?q=mercy` |
| Index | `/quran/` | `/quran/` (Master index) |

## Content Organization

### Surah Pages

Each surah page contains:

```markdown
# Surah {number}: {Arabic Name} ({English Name})

**Revelation**: {Meccan/Medinan}  
**Verses**: {count}  
**Juz**: {juz_number}  
**Ruku**: {ruku_count}

## Arabic Text
{Complete Arabic text with proper diacritics}

## Translations

### {Translator Name} ({Language})
{Translation text}

### {Another Translator} ({Language})
{Translation text}

## Tafsir

### {Scholar Name}
{Tafsir text with proper attribution}

## Related Content
- [Previous Surah](link)
- [Next Surah](link)
- [Juz {number}](link)
```

### Juz Pages

Each juz page contains:

```markdown
# Juz {number}

**Surahs**: {surah_range}  
**Verses**: {total_verses}  
**Pages**: {page_range}

## Content Overview

### {Surah Name} ({verses_in_juz} verses)
{Verse range and brief description}

### {Next Surah} ({verses_in_juz} verses)
{Verse range and brief description}

## Study Tools
- [Memorization Tracker](link)
- [Audio Recitation](link)
- [Tafsir Collection](link)
```

## Search and Discovery

### Search Interface

The Quran search system provides:

- **Text Search**: Search across Arabic text and translations
- **Advanced Filters**: By surah, juz, revelation type, language
- **Scholar Search**: Find content by specific scholars
- **Cross-Reference**: Find related verses and tafsir

### Search Syntax

```markdown
# Basic Search
mercy                    # Find verses containing "mercy"
"exact phrase"          # Exact phrase matching
surah:1                 # Search within specific surah
juz:1-5                 # Search within juz range
language:en             # Search English translations only
scholar:ibn-kathir      # Search specific scholar's work
```

## Integration with EnhancedMarkdown

The Quran namespace integrates with the EnhancedMarkdown extension:

### Quran Verse References

```markdown
# Inline References
{{quran:1:1}}           # Reference to Al-Fatiha, verse 1
{{quran:2:255}}         # Reference to Ayat Al-Kursi
{{quran:surah:1}}       # Reference to entire Al-Fatiha
{{quran:juz:1}}         # Reference to first juz
```

### Tafsir Integration

```markdown
# Scholar References
{{scholar:ibn-kathir}}  # Ibn Kathir's commentary
{{scholar:al-tabari}}   # Al-Tabari's interpretation
{{scholar:ibn-abbas}}   # Ibn Abbas's explanations
```

## Content Management

### Import Process

The Quran content is imported through specialized scripts:

```bash
# Import all Quran data with all translators
php scripts/quran/import_quran_from_quran_com.php --all --lang=en

# Import specific translators
php scripts/quran/import_quran_from_quran_com.php --all --lang=en --translators="Saheeh International"
php scripts/quran/import_quran_from_quran_com.php --all --lang=en --translators="Pickthall"
php scripts/quran/import_quran_from_quran_com.php --all --lang=en --translators="Yusuf Ali"
```

### Translation Sources

Currently supported translations:
- **Saheeh International** - Modern English translation
- **Pickthall** - Classical English translation
- **Yusuf Ali** - Comprehensive English translation
- **Muhsin Khan** - Sahih International parallel
- **Dr. Ghali** - Academic English translation

## Future Enhancements

### Planned Features

1. **Audio Integration**
   - Multiple reciter options
   - Verse-by-verse audio
   - Download capabilities

2. **Advanced Tafsir**
   - Multiple scholar perspectives
   - Historical context
   - Linguistic analysis

3. **Study Tools**
   - Memorization tracking
   - Progress analytics
   - Study groups

4. **Mobile Optimization**
   - Responsive design
   - Offline access
   - Push notifications

### API Development

```php
// Planned API endpoints
GET /api/quran/v1/surah/{number}
GET /api/quran/v1/verse/{surah}:{ayah}
GET /api/quran/v1/search?q={query}
GET /api/quran/v1/tafsir/{verse_id}
```

## Best Practices

### Content Guidelines

1. **Accuracy**: All content must be verified against authoritative sources
2. **Attribution**: Proper credit to scholars and translators
3. **Accessibility**: Support for screen readers and assistive technologies
4. **Performance**: Efficient database queries and caching
5. **SEO**: Proper meta tags and structured data

### Development Standards

1. **Database**: Use prepared statements and proper indexing
2. **Caching**: Implement Redis caching for frequently accessed content
3. **Validation**: Comprehensive input validation and sanitization
4. **Testing**: Unit tests for all Quran-related functionality
5. **Documentation**: Keep documentation updated with code changes

## Troubleshooting

### Common Issues

1. **Import Failures**: Check API rate limits and network connectivity
2. **Search Performance**: Ensure proper database indexing
3. **Translation Display**: Verify language codes and encoding
4. **Cache Issues**: Clear Redis cache for content updates

### Support Resources

- [Database Setup Guide](../DATABASE_SETUP.md)
- [API Documentation](../api/README.md)
- [Development Guidelines](../guides/README.md)
- [Issue Resolution](../troubleshooting/README.md)

---

*This documentation is part of the IslamWiki project. For questions or contributions, please refer to the project guidelines.*

