# Quran Import System

## Overview

The Quran Import System allows you to import Quranic text and translations from the [api.quran.com](https://api.quran.com) API into the IslamWiki database. This system provides a complete import of all 114 surahs, 6,236 ayahs, and multiple translations in various languages.

## Features

- **Complete Quran Import**: Import all 114 surahs and 6,236 ayahs
- **Multiple Translations**: Support for translations in English, Arabic, Urdu, and other languages
- **Flexible Import Options**: Import specific surahs, all surahs, or specific translations
- **Data Validation**: Ensures data integrity and handles duplicates gracefully
- **Clean Text Processing**: Removes HTML tags and footnotes from translations

## Database Schema

The import system works with the following database tables:

### Core Tables
- `surahs` - Quran chapters with metadata
- `ayahs` - Individual verses with Arabic text and positioning
- `translations` - Available translation sources
- `ayah_translations` - Translated text for each ayah

### Additional Tables
- `tajweed_rules` - Rules for Quranic recitation
- `ayah_tajweed` - Tajweed rule applications
- `recitations` - Audio recitation sources
- `ayah_recitations` - Audio files for ayahs
- `tafsir_sources` - Exegesis sources
- `ayah_tafsir` - Exegesis text for ayahs
- `quranic_topics` - Topic categorization
- `ayah_topics` - Topic assignments for ayahs

## Usage

### Basic Import Commands

```bash
# Import all surahs with all available translations
php scripts/quran/import_quran_from_quran_com.php --all

# Import specific surah (e.g., Al-Fatihah)
php scripts/quran/import_quran_from_quran_com.php --surah=1

# Import with specific language
php scripts/quran/import_quran_from_quran_com.php --all --lang=ar

# Import with specific translators only
php scripts/quran/import_quran_from_quran_com.php --all --translators="Saheeh International,Yusuf Ali"

# Import ayahs only (no translations)
php scripts/quran/import_quran_from_quran_com.php --all --no-translations
```

### Command Line Options

| Option | Description | Example |
|--------|-------------|---------|
| `--all` | Import all 114 surahs | `--all` |
| `--surah=N` | Import specific surah number | `--surah=1` |
| `--juz=N` | Import specific juz (currently imports via chapters) | `--juz=1` |
| `--lang=XX` | Language for surah names (default: en) | `--lang=ar` |
| `--translators=...` | Comma-separated list of translator names | `--translators="Saheeh International"` |
| `--no-translations` | Import ayahs only, skip translations | `--no-translations` |

### Available Translators

The system automatically detects available translators from the API. Common English translators include:

- **Saheeh International** - Modern, clear English translation
- **Yusuf Ali** - Classic English translation with commentary
- **Pickthall** - Traditional English translation
- **Muhsin Khan** - English translation with Hadith references
- **Dr. Ghali** - Academic English translation

## Import Process

### 1. Surah Import
- Fetches surah metadata from API
- Imports Arabic names, English names, and translations
- Records revelation type (Meccan/Medinan) and verse counts

### 2. Ayah Import
- Imports Arabic text in Uthmani script
- Records positioning information (juz, page numbers)
- Handles 6,236 individual ayahs across all surahs

### 3. Translation Import
- Fetches available translations from API
- Imports translation text for each ayah
- Cleans HTML tags and footnotes from text
- Maintains translation metadata and sources

### 4. Data Validation
- Uses UPSERT operations to handle duplicates
- Validates data integrity at each step
- Provides detailed error reporting

## API Integration

The system integrates with the [api.quran.com](https://api.quran.com) API:

- **Chapters Endpoint**: `/api/v4/chapters` - Surah metadata
- **Verses Endpoint**: `/api/v4/verses/by_chapter/{id}` - Ayah text and translations
- **Translations Endpoint**: `/api/v4/resources/translations` - Available translations

### Rate Limiting
- Implements 30-second timeout for API requests
- Processes data in batches of 50 ayahs per request
- Handles pagination automatically

## Data Quality

### Text Cleaning
- Removes HTML tags and footnotes
- Normalizes whitespace
- Preserves Arabic text integrity
- Maintains translation accuracy

### Validation
- Ensures all required fields are present
- Validates Arabic text encoding
- Checks translation completeness
- Maintains referential integrity

## Testing and Verification

### Test Script
Use the included test script to verify imported data:

```bash
php scripts/quran/test_imported_data.php
```

This script provides:
- Data count verification
- Sample data display
- Translation availability check
- Juz and page information

### Expected Results
After successful import:
- **114 surahs** with complete metadata
- **6,236 ayahs** with Arabic text
- **Multiple translations** in various languages
- **Complete positioning data** (juz, pages)

## Troubleshooting

### Common Issues

1. **API Connection Errors**
   - Check internet connectivity
   - Verify API endpoint availability
   - Check firewall settings

2. **Database Errors**
   - Ensure database connection is configured
   - Verify table schema matches expectations
   - Check user permissions

3. **Translation Issues**
   - Verify translator names match API exactly
   - Check language parameter validity
   - Ensure translation data is available

### Error Handling
- Detailed error messages for debugging
- Graceful handling of API failures
- Transaction rollback on critical errors
- Logging of import progress and issues

## Performance Considerations

### Import Speed
- **Full import**: Approximately 10-15 minutes
- **Single surah**: 1-2 minutes
- **Translation import**: Adds 2-3 minutes per translation

### Resource Usage
- Memory: Minimal (streaming API responses)
- Network: Moderate (API calls for each surah)
- Database: Efficient UPSERT operations

### Optimization Tips
- Use `--no-translations` for faster ayah-only imports
- Import specific surahs for testing
- Run during off-peak hours for large imports

## Future Enhancements

### Planned Features
- **Incremental Updates**: Update only changed data
- **Multiple API Sources**: Support for additional Quran APIs
- **Batch Processing**: Improved performance for large imports
- **Translation Management**: Better handling of translation updates

### API Improvements
- **Caching**: Local caching of API responses
- **Rate Limiting**: Intelligent API request throttling
- **Fallback Sources**: Alternative data sources for reliability

## Security Considerations

### Data Validation
- Input sanitization for all API responses
- SQL injection prevention through prepared statements
- XSS protection in text processing

### Access Control
- CLI-only execution for import scripts
- Database user with minimal required permissions
- Secure handling of API credentials

## Support and Maintenance

### Monitoring
- Regular verification of imported data
- API endpoint health checks
- Database performance monitoring

### Updates
- Monitor API changes and updates
- Regular schema validation
- Translation accuracy verification

### Documentation
- Keep this documentation updated
- Record any API changes or issues
- Maintain troubleshooting guides

## Conclusion

The Quran Import System provides a robust, efficient way to populate the IslamWiki database with complete Quranic text and translations. With its flexible import options, comprehensive error handling, and data validation, it ensures reliable and accurate Quran data for the platform.

For questions or issues, refer to the troubleshooting section or consult the development team.
