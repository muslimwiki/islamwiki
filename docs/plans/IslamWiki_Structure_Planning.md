# IslamWiki Structure Planning

## Overview

This document outlines the planning for implementing a MediaWiki-inspired structure for IslamWiki, incorporating Islamic-specific features while maintaining developer familiarity.

**Date**: 2025-07-30  
**Status**: Planning Phase  
**Next Phase**: Implementation (v0.1.0)

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
   - ✅ Separate connection per database (quran_db, hadith_db, wiki_db) - **RECOMMENDED**
   - ✅ Single connection with different schemas - **EVALUATED**
   - ✅ Connection pool with lazy loading - **EVALUATED**
   - ✅ Performance analysis and security considerations
   - ✅ Scalability planning and migration strategy

2. **Islamic Entry Points**: Research routing strategies for Islamic features
   - Separate entry points for Islamic features
   - Everything through `index.php` with routing
   - Hybrid approach

3. **Performance Testing**: Compare different approaches
4. **Security Analysis**: Evaluate security implications of each approach

### Implementation Research
1. **Configuration System**: Research best practices for hybrid config approach
2. **API System**: Research API versioning and routing strategies
3. **Extension System**: Research MediaWiki extension patterns
4. **Skin System**: Research theme development patterns

---

## 🚀 Implementation Roadmap

### Phase 1: Core Structure (v0.1.0)
1. **Root Directory Structure**: Implement MediaWiki-inspired root organization
2. **Core Folders**: Create all MediaWiki-inspired directories
3. **Configuration System**: Implement hybrid LocalSettings.php + IslamSettings.php
4. **API System**: Implement hybrid api.php + specific API files

### Phase 2: Islamic Integration (v0.1.1)
1. **Islamic Core Classes**: Implement within `app/Core/Islamic/`
2. **Islamic Extensions**: Create core Islamic extensions
3. **Database Schema**: Implement Islamic-specific schema

### Phase 3: Documentation (v0.1.2)
1. **Technical Documentation**: Create root-level docs
2. **Islamic Documentation**: Create `docs/islamic/`
3. **Developer Documentation**: Create `docs/developer/`

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
1. **Research Tasks**: ✅ Database connection strategy completed (0.0.11)
2. **Configuration Design**: Finalize configuration system design (0.0.12)
3. **API Design**: Finalize API system design (0.0.13)

### Short Term (Next 2 Weeks)
1. **Core Structure**: Begin implementing MediaWiki-inspired structure
2. **Configuration System**: Implement hybrid configuration
3. **API System**: Implement hybrid API system

### Medium Term (Next Month)
1. **Islamic Core**: Implement Islamic core classes
2. **Documentation**: Create comprehensive documentation
3. **Testing**: Comprehensive testing of new structure

---

**Status**: Planning Complete ✅  
**Next Phase**: Implementation (v0.1.0) 