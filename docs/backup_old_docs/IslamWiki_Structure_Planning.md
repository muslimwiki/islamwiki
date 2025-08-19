# IslamWiki Structure Planning

## Overview

This document outlines the planning for implementing a MediaWiki-inspired structure for IslamWiki, incorporating Islamic-specific features while maintaining developer familiarity. Many of the planned features have been successfully implemented in versions 0.0.12 through 0.0.17.

**Date**: 2025-07-31  
**Status**: Production Ready - Extension System Complete  
**Current Version**: 0.0.19  
**Next Phase**: Advanced API System Implementation (v0.0.20)

---

## 📁 Documentation Structure Decisions

### Root Folder (Essential for Everyone)
```
islamwiki/
├── INSTALL                     # Installation guide
├── UPGRADE                     # Upgrade instructions  
├── SECURITY                    # Security guidelines
├── HISTORY                     # Version history
├── RELEASE-NOTES-1.44         # Release notes
├── FAQ                         # Frequently asked questions
├── COPYING                     # License file (GNU AGPL)
├── CREDITS                     # Contributors list
└── CODE_OF_CONDUCT            # Community guidelines
```

### Docs Folder (Specialized Documentation)
```
docs/
├── islamic/                    # Islamic-specific documentation
│   ├── content-guidelines.md   # Islamic content standards
│   ├── scholarly-standards.md  # Academic requirements
│   ├── arabic-guidelines.md    # Arabic text guidelines
│   ├── citation-formats.md     # Islamic citation standards
│   ├── moderation-policy.md    # Content moderation policy
│   ├── quran-integration.md    # Quran feature documentation
│   ├── hadith-standards.md     # Hadith authenticity standards
│   ├── prayer-times-guide.md   # Prayer time calculations
│   ├── islamic-calendar.md     # Hijri calendar documentation
│   └── scholar-verification.md # Scholar verification process
│
├── developer/                  # Developer documentation
│   ├── coding-standards.md     # Coding standards
│   ├── api-reference.md        # API documentation
│   ├── extension-development.md # Extension creation guide
│   ├── skin-development.md     # Theme development guide
│   ├── islamic-extensions.md   # Islamic extension development
│   ├── arabic-support.md       # Arabic text implementation
│   ├── performance-guide.md    # Performance optimization
│   └── security-guide.md       # Security best practices
│
└── user-guides/                # User documentation
    ├── installation-guide.md   # Detailed installation
    ├── usage-guide.md          # General usage
    ├── islamic-features.md     # Islamic features guide
    └── mobile-usage.md         # Mobile app usage
```

---

## 🏗️ Architecture Decisions

### 1. Islamic Content Organization ✅
**Decision**: Option B - Nested Islamic Core
```
app/
├── Core/
│   ├── Application.php
│   ├── Router.php
│   ├── Database.php
│   └── Islamic/              # Islamic core within Core
│       ├── QuranService.php
│       ├── HadithService.php
│       └── CalendarService.php
```

**Rationale**: Shows Islamic functionality is core to the system, not an add-on.

### 2. Language Files ✅
**Decision**: Laravel-style `resources/lang` instead of MediaWiki's `i18n`

**Rationale**: More intuitive than `i18n` (internationalization), familiar to modern developers.

### 3. Extensions Permissions ✅
**Decision**: Per-extension basis permissions

**Rationale**: Each extension can have its own permission system, more granular control.

### 4. Database Connections 🤔
**Status**: Research needed
- **A**: Separate connection per database (quran_db, hadith_db, wiki_db)
- **B**: Single connection with different schemas
- **C**: Connection pool with lazy loading

### 5. API Versioning ✅
**Decision**: Separate versioning for all APIs

**Rationale**: Allows independent evolution of different API components.

---

## 🔧 Configuration System Design

### Hybrid Approach
```php
// LocalSettings.php (main config)
$wgDBserver = 'localhost';
$wgDBname = 'islamwiki';
$wgDBuser = 'islamwiki_user';

// IslamSettings.php (optional override)
$wgQuranDatabase = 'quran_db';        // Override Quran DB
$wgHadithDatabase = 'hadith_db';       // Override Hadith DB
$wgPrayerTimesAPI = 'custom_api';      // Override prayer API
```

**Implementation Strategy**:
1. Load `LocalSettings.php` first
2. Check if `IslamSettings.php` exists
3. If exists, load and override Islamic-specific settings
4. Fallback to defaults if neither file exists

---

## 🌐 API System Design

### Hybrid Approach
```php
// api.php (main API)
$wgAPIModules['quran'] = 'ApiQuran';
$wgAPIModules['hadith'] = 'ApiHadith';

// api-quran.php (optional override)
$wgQuranAPIVersion = 'v2';
$wgQuranAPIRateLimit = 1000;
$wgQuranAPICache = true;
```

**Implementation Strategy**:
1. Load main `api.php` settings
2. Check for specific API files (`api-quran.php`, `api-hadith.php`)
3. Override with specific settings if files exist
4. Maintain backward compatibility

---

## 📋 Research Tasks (To-Do List)

### Database & Architecture Research
1. **Database Connection Strategy**: ✅ **COMPLETED** (0.0.11)
   - ✅ Separate connection per database (quran_db, hadith_db, wiki_db) - **IMPLEMENTED**
   - ✅ Single connection with different schemas - **EVALUATED**
   - ✅ Connection pool with lazy loading - **EVALUATED**
   - ✅ Performance analysis and security considerations
   - ✅ Scalability planning and migration strategy

2. **Islamic Database Implementation**: ✅ **COMPLETED** (0.0.12)
   - ✅ Quran database with 13 tables for verses, translations, recitations
   - ✅ Hadith database with 13 tables for collections, narrators, chains
   - ✅ Scholar database with 13 tables for verification and credentials
   - ✅ Wiki database for general Islamic content
   - ✅ Islamic Database Manager with separate connections
   - ✅ Performance: Sub-100ms connection times achieved

3. **Islamic Content Integration**: ✅ **COMPLETED** (0.0.13-0.0.16)
   - ✅ Quran Integration System (0.0.13) - Complete verse management
   - ✅ Hadith Integration System (0.0.14) - Complete hadith management
   - ✅ Islamic Calendar Integration (0.0.15) - Complete calendar system
   - ✅ Prayer Times Integration (0.0.16) - Complete prayer time system

4. **Search & Discovery System**: ✅ **COMPLETED** (0.0.17)
   - ✅ Comprehensive search across all Islamic content types
   - ✅ Full-text search indexes for fast performance
   - ✅ Search analytics and caching system
   - ✅ Advanced filtering and suggestions

5. **Islamic Entry Points**: Research routing strategies for Islamic features
   - Separate entry points for Islamic features
   - Everything through `index.php` with routing
   - Hybrid approach

6. **Performance Testing**: Compare different approaches
7. **Security Analysis**: Evaluate security implications of each approach

### Implementation Research
1. **Configuration System**: ✅ **COMPLETED** (0.0.18)
   - ✅ Research best practices for hybrid config approach
   - ✅ Implement LocalSettings.php + IslamSettings.php approach
   - ✅ Enhanced configuration management
   - ✅ Environment-specific settings

2. **Extension System**: ✅ **COMPLETED** (0.0.19)
   - ✅ Extension architecture with modular design
   - ✅ Hook system for extension communication
   - ✅ ExtensionManager for automatic discovery and loading
   - ✅ Enhanced Markdown and Git Integration extensions
   - ✅ Production-ready extension system

3. **API System**: 🔄 **PLANNED** (0.0.20)
   - Research API versioning and routing strategies
   - Implement hybrid api.php + specific API files approach
   - Advanced API versioning
   - API rate limiting and security

4. **Skin System**: Research theme development patterns

---

## 🚀 Implementation Roadmap

### ✅ Phase 1: Core Infrastructure (v0.0.12) - COMPLETE
1. ✅ **Database Architecture**: Implement separate database connections per content type
2. ✅ **Islamic Database Manager**: Create IslamicDatabaseManager with connection management
3. ✅ **Database Schema**: Implement 39 tables across 4 databases (Quran, Hadith, Scholar, Wiki)
4. ✅ **Performance Optimization**: Achieve sub-100ms connection times

### ✅ Phase 2: Islamic Authentication (v0.0.12) - COMPLETE
1. ✅ **Islamic User Model**: Enhanced user model with Islamic community features
2. ✅ **Scholar Verification**: Complete verification workflow with approval/rejection
3. ✅ **Role-Based Access Control**: 5 Islamic roles with specific permissions
4. ✅ **Islamic Profile Management**: Enhanced user profiles with Islamic data

### ✅ Phase 3: Content Management (v0.0.12) - COMPLETE
1. ✅ **Islamic Content Model**: Enhanced page model with Islamic categorization
2. ✅ **Content Moderation System**: Complete approval, rejection, and revision workflow
3. ✅ **Islamic Templates**: 10 specialized templates for different content types
4. ✅ **Quality Scoring**: Content quality assessment and scoring system

### ✅ Phase 4: Islamic Content Integration (v0.0.13-0.0.16) - COMPLETE
1. ✅ **Quran Integration** (v0.0.13): Complete verse management with API and widgets
2. ✅ **Hadith Integration** (v0.0.14): Complete hadith management with search and analytics
3. ✅ **Islamic Calendar** (v0.0.15): Complete calendar system with events and date conversion
4. ✅ **Prayer Times** (v0.0.16): Complete prayer time system with astronomical calculations

### ✅ Phase 5: Search & Discovery (v0.0.17) - COMPLETE
1. ✅ **Comprehensive Search System**: Search across all Islamic content types
2. ✅ **Full-Text Search Indexes**: Database indexes for fast search
3. ✅ **Search Analytics**: Real-time statistics and performance metrics
4. ✅ **Search Caching**: Intelligent caching system for improved performance

### ✅ Phase 6: Configuration System (v0.0.18) - COMPLETE
1. ✅ **Root Directory Structure**: Implemented MediaWiki-inspired root organization
2. ✅ **LocalSettings.php**: Created main configuration file with 108 settings
3. ✅ **IslamSettings.php**: Created optional Islamic override file
4. ✅ **ConfigurationManager**: Implemented hybrid configuration system
5. ✅ **ConfigurationServiceProvider**: Integrated with application container
6. ✅ **Helper Functions**: Created 8 global helper functions
7. ✅ **Validation System**: Complete configuration validation
8. ✅ **Testing System**: Comprehensive test suite with 15 test categories

### ✅ Phase 7: Extension System (v0.0.19) - COMPLETE
1. ✅ **Extension Architecture**: Implemented modular extension system
2. ✅ **Hook System**: Created event-driven extension communication
3. ✅ **ExtensionManager**: Built automatic extension discovery and loading
4. ✅ **Enhanced Markdown**: Implemented Islamic syntax and Arabic support
5. ✅ **Git Integration**: Built automatic version control and backup system
6. ✅ **Production Ready**: Extensions disabled by default for safety

### 🔄 Phase 8: Advanced API System (v0.0.20) - PLANNED
1. **API System**: Implement hybrid api.php + specific API files
2. **Extension System**: Implement MediaWiki-inspired extension system
3. **Documentation System**: Create comprehensive documentation structure
4. **Testing Framework**: Advanced testing and validation

### ⏳ Phase 7: Islamic Extensions (v0.1.1) - PLANNED
1. **Islamic Core Classes**: Implement within `app/Core/Islamic/`
2. **Islamic Extensions**: Create core Islamic extensions
3. **Advanced Database Schema**: Implement additional Islamic-specific schema

### ⏳ Phase 8: Documentation & Testing (v0.1.2) - PLANNED
1. **Technical Documentation**: Create root-level docs
2. **Islamic Documentation**: Create `docs/islamic/`
3. **Developer Documentation**: Create `docs/developer/`
4. **Comprehensive Testing**: Test all Islamic features and integrations

---

## 🎯 Key Benefits

### Developer Familiarity
- **MediaWiki-Inspired Structure**: Familiar to MediaWiki developers
- **Modern Practices**: Incorporates modern PHP practices
- **Clear Organization**: Logical file and folder structure

### Islamic Content Focus
- **Core Integration**: Islamic features are core, not add-ons
- **Specialized Services**: Dedicated Islamic content services
- **Extensible**: Easy to add new Islamic content types

### Maintainability
- **Modular Design**: Clear separation of concerns
- **Configuration Flexibility**: Hybrid configuration approach
- **Documentation**: Comprehensive documentation structure

---

## 📝 Next Actions

### Immediate (This Week)
1. **API System**: Begin implementation of advanced API system (0.0.19)
2. **Extension System**: Plan MediaWiki-inspired extension system
3. **Configuration Testing**: Complete testing and optimization of configuration features

### Short Term (Next 2 Weeks)
1. **Advanced Structure**: Begin implementing MediaWiki-inspired structure (v0.1.0)
2. **Configuration System**: Complete hybrid configuration implementation
3. **API System**: Implement hybrid API system

### Medium Term (Next Month)
1. **Islamic Extensions**: Implement Islamic extension system
2. **Documentation**: Create comprehensive documentation structure
3. **Testing**: Comprehensive testing of all Islamic features

### Long Term (Next Quarter)
1. **Production Readiness**: Prepare for v1.0.0 production release
2. **Community Features**: Implement advanced community features
3. **Mobile Integration**: Develop mobile application support

---

## 🎉 Current Achievements

### ✅ **Completed Infrastructure**
- **Database Architecture**: 4 separate databases with 39 tables
- **Islamic Content Integration**: Quran, Hadith, Calendar, Prayer Times
- **Search & Discovery**: Comprehensive search across all content types
- **Authentication System**: Enhanced with scholar verification
- **Content Management**: Complete moderation and quality control
- **Configuration System**: Hybrid configuration with 108 settings
- **Extension System**: Modular extension architecture with hook system
- **Performance**: Sub-100ms response times achieved
- **Security**: Enterprise-level security with proper isolation

### 🚀 **Ready for Advanced API System**
- **Core Foundation**: Solid foundation for MediaWiki-inspired structure
- **Islamic Features**: All major Islamic content types implemented
- **Search Capabilities**: Full-text search with analytics and caching
- **Extension System**: Production-ready extension architecture
- **Git Integration**: Professional-grade version control
- **Enhanced Markdown**: Islamic syntax and Arabic support
- **Documentation**: Extensive documentation and testing

---

**Status**: Extension System Complete ✅  
**Next Phase**: Advanced API System Implementation (v0.0.20) 