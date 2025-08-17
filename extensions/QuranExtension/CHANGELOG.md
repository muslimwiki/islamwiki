# QuranExtension Changelog

All notable changes to the QuranExtension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.3] - 2025-01-15

### Added
- **Unified Quran page design** with consistent styling across all pages
- **Enhanced surah pages** with integrated Bismillah and unified ayah display
- **Improved ayah pages** with better styling and consistent navigation
- **Combined navigation system** merging top navigation with breadcrumbs
- **Space-optimized layouts** eliminating redundant sections
- **Enhanced translator display** with better contrast and readability
- **Arabic numeral support** for ayah numbers (١, ٢, ٣, etc.)
- **Professional card-based ayah display** for better visual hierarchy

### Changed
- **Template structure** unified across all Quran pages for consistency
- **CSS organization** improved with better maintainability and reduced conflicts
- **Navigation layout** combined for space efficiency and better UX
- **Bismillah display** integrated into page headers instead of separate sections
- **Translation selector** positioned within breadcrumbs for space optimization
- **Ayah styling** unified between surah list and individual ayah pages

### Fixed
- **Database field references** corrected (ayah_count → verses_count)
- **CSS conflicts** eliminated by removing conflicting inline styles
- **HTML structure** corrected for better semantic markup
- **Styling inconsistencies** resolved across all Quran pages
- **Navigation duplication** eliminated for cleaner user experience
- **Responsive design** improved for mobile and desktop viewing

### Technical Details
- **New CSS classes** added for unified ayah content styling
- **Template structure** standardized across surah and ayah pages
- **CSS organization** improved with better maintainability
- **Performance optimization** through reduced CSS conflicts and redundancy

## [Unreleased]

### Planned
- Advanced Quran search with semantic understanding
- Machine learning-based verse classification
- Integration with external Quran databases
- Advanced tafsir and commentary system
- Quran memorization tools and progress tracking
- Community-driven verse interpretation system
- Advanced audio recitation with multiple qaris
- Quran translation management system

## [0.0.2] - 2024-12-19

### Added
- **Advanced Quran search system** with multiple search algorithms
- **Enhanced verse display** with multiple translations and tafsir
- **Audio recitation system** with multiple qaris and recitation styles
- **Tafsir integration** with comprehensive commentary system
- **Quran memorization tools** with progress tracking
- **Advanced admin interface** for Quran content management
- **API endpoints** for external Quran access
- **Caching system** for improved performance
- **Export functionality** for Quran data
- **Multi-language support** for translations and interface

### Changed
- **Search performance** optimized with database indexing
- **Database schema** enhanced for better Quran relationships
- **Template system** improved for better customization
- **Widget system** optimized for various page layouts
- **Language support** enhanced with better RTL handling
- **Audio system** improved with better streaming and quality

### Fixed
- **Search accuracy** issues with complex queries
- **Database performance** problems with large Quran collections
- **Template rendering** issues on mobile devices
- **Language switching** problems in Quran display
- **Admin interface** usability issues
- **Audio playback** problems on various devices

### Technical Details
- **Search algorithms** updated with fuzzy matching and relevance scoring
- **Database optimization** with proper indexing and query optimization
- **Caching layer** implemented for frequently accessed Quran data
- **API architecture** redesigned for better performance and scalability
- **Audio streaming** optimized for better performance and quality

## [0.0.1] - 2024-12-19

### Added
- **Initial release** of QuranExtension
- **Basic Quran search** functionality with simple text matching
- **Quran verse display** system with formatted text and metadata
- **Basic Quran management** for administrators
- **Simple Quran widgets** for page integration
- **Core extension architecture** following IslamWiki standards
- **Hook system integration** for content and page display
- **Resource management** for CSS, JavaScript, and templates
- **Configuration system** for customizable Quran settings
- **Basic admin interface** for managing Quran data

### Technical Details
- **Extension class** extending `IslamWiki\Core\Extensions\Extension`
- **Hook registration** for ContentParse, PageDisplay, and SearchIndex
- **Resource management** for CSS, JavaScript, and template files
- **Configuration management** with default settings and customization options
- **Template integration** with main application layout

---

## Release Notes

### Version 0.0.3 - QuranUI Enhancement
This release introduces comprehensive improvements to the Quran user interface, creating a more consistent, beautiful, and user-friendly experience across all Quran-related pages. The focus is on visual consistency, space optimization, and enhanced readability.

**Key Improvements:**
- Unified design language across all Quran pages
- Space-optimized layouts eliminating redundant sections
- Enhanced navigation combining top navigation with breadcrumbs
- Professional card-based ayah display with Arabic numerals
- Improved translator display with better contrast and readability
- Integrated Bismillah in page headers for space efficiency

**Breaking Changes:**
- None - this is a backward-compatible release

**Migration Guide:**
No migration required. Existing Quran data and settings will be automatically upgraded.

### Version 0.0.2 - Enhanced Quran System
This release introduces significant improvements to Quran search, display, and management capabilities. The extension now provides advanced search algorithms, better audio integration, and comprehensive tafsir system.

**Key Improvements:**
- Advanced search algorithms with fuzzy matching
- Enhanced audio recitation system with multiple qaris
- Comprehensive tafsir and commentary integration
- Quran memorization tools with progress tracking
- Better performance with caching and optimization

**Breaking Changes:**
- None - this is a backward-compatible release

**Migration Guide:**
No migration required. Existing Quran data and settings will be automatically upgraded.

### Version 0.0.1 - Initial Release
The initial release provides basic Quran functionality with simple search and display capabilities.

**Features:**
- Basic Quran search and display
- Simple Quran management
- Basic widgets and templates
- Core extension architecture

---

## Contributing

When contributing to this extension, please:

1. **Update this changelog** with your changes
2. **Follow semantic versioning** for releases
3. **Document breaking changes** clearly
4. **Include migration guides** when necessary
5. **Test thoroughly** before submitting changes

## Version History

- **0.0.2** - Enhanced Quran system with advanced search and audio features
- **0.0.1** - Initial release with basic Quran functionality

---

*This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format and is maintained by the IslamWiki development team.* 