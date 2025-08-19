# IslamWiki Versioning Guide

## 🎯 **Overview**

This guide defines the versioning strategy, release process, and naming conventions for IslamWiki. Following these guidelines ensures consistent version management and clear communication about platform updates.

---

## 🏗️ **Versioning Philosophy**

### **Core Principles**
- **4-Part Versioning**: Follow 4-part versioning system for clear phase separation
- **Islamic Values**: Version names reflect Islamic principles of excellence and community
- **Phase-Based Development**: Clear progression through development phases
- **Transparency**: Clear communication about changes and stability
- **Community**: Involve the community in version planning and feedback

### **Version Structure**
```
Version Format: {MAJOR}.{MINOR}.{PATCH}.{BUILD}
Example: 0.0.1.0, 0.0.2.1, 0.1.0.0, 1.0.0.0

Components:
├── MAJOR: Production releases (1.x.x.x = stable releases)
├── MINOR: Development phases (0.1.x.x = stable development, 0.0.x.x = unstable development)
├── PATCH: Feature/system additions (0.0.2.x = new features, 0.0.1.x = restructuring)
└── BUILD: Incremental changes (0.0.1.1, 0.0.1.2, etc.)
```

---

## 🔄 **Development Phase Versioning**

### **Phase 1: Testing & Bug Fixes (0.0.0.x)**
```
0.0.0.1 - Initial testing
0.0.0.2 - Bug fixes
0.0.0.3 - Minor corrections
...
0.0.0.62 - QuranUI Enhancement ✅ (completed)
```
**Purpose**: Very minor changes, bug fixes, testing phase
**Stability**: Unstable, experimental
**Scope**: Minimal, focused fixes
**Use Case**: Quick bug fixes, testing new ideas

### **Phase 2: Restructuring & Major Changes (0.0.1.x)**
```
0.0.1.0 - Documentation restructuring ✅ (completed)
0.0.1.1 - Site restructuring starts (next)
0.0.1.2 - Site restructuring continues
0.0.1.3 - Site restructuring finalization
```
**Purpose**: Major architectural changes, restructuring work
**Stability**: Unstable, breaking changes expected
**Scope**: Significant, architectural changes
**Use Case**: Major restructuring, architecture changes

### **Phase 3: Feature Development (0.0.2.x)**
```
0.0.2.0 - Quran system added
0.0.2.1 - Hadith system added
0.0.2.2 - Forums system added
0.0.2.3 - Messaging system added
0.0.3.0 - User management system
0.0.3.1 - Content management system
```
**Purpose**: Adding new major features/systems
**Stability**: May be unstable, new functionality
**Scope**: New functionality, system additions
**Use Case**: Adding new features, expanding capabilities

### **Phase 4: Stabilization (0.1.x.x)**
```
0.1.0.0 - Architecture stable, all systems working
0.1.1.0 - Performance improvements
0.1.2.0 - Security enhancements
```
**Purpose**: Stable architecture, production-ready
**Stability**: Stable, minor testing needed
**Scope**: Optimizations, stability improvements
**Use Case**: Production-ready features, optimizations

### **Phase 5: Production Releases (x.x.x.x)**
```
1.0.0.0 - First production release
1.1.0.0 - Feature release
2.0.0.0 - Major release
```
**Purpose**: Fully tested, production-ready releases
**Stability**: Fully tested, enterprise-ready
**Scope**: Complete, production systems
**Use Case**: Production deployments, enterprise use

---

## 🚀 **Version Progression Examples**

### **Current Development Path**
```
0.0.0.62 - QuranUI Enhancement (completed)
0.0.1.0 - Documentation restructuring (completed)
0.0.1.1 - Site restructuring begins (next)
0.0.1.2 - Site restructuring continues
0.0.2.0 - Quran system implementation
0.0.2.1 - Hadith system implementation
0.1.0.0 - All systems stable and working
1.0.0.0 - First production release
```

### **Version Increment Rules**
- **Bug fixes**: Increment BUILD (0.0.1.0 → 0.0.1.1)
- **Restructuring**: Increment PATCH (0.0.1.x → 0.0.2.0)
- **New features**: Increment PATCH (0.0.2.x → 0.0.3.0)
- **Stabilization**: Increment MINOR (0.0.x.x → 0.1.0.0)
- **Production**: Increment MAJOR (0.x.x.x → 1.0.0.0)

---

## 🔌 **Extension Versioning**

### **Extension Version Independence**
**Important**: Each individual extension follows its own versioning system that is completely separate from the main site versioning. This allows extensions to evolve independently while maintaining compatibility with the core platform.

### **Extension Version Structure**
```
Extension Version Format: {MAJOR}.{MINOR}.{PATCH}
Example: 1.0.0, 2.1.3, 0.5.2

Components:
├── MAJOR: Breaking changes in extension API or functionality
├── MINOR: New features and enhancements
└── PATCH: Bug fixes and minor improvements
```

### **Extension Versioning Rules**

#### **1. Independent Versioning**
- **Extensions start at version 0.0.1** when first created
- **Each extension maintains its own version history** independent of other extensions
- **Extension versions are not tied to site versions** - they can be updated independently
- **Multiple extensions can have different versions** simultaneously

#### **2. Version Compatibility**
```
Site Version: 0.0.1.0
├── Extension A: 2.1.0 (Latest version)
├── Extension B: 1.5.2 (Stable version)
├── Extension C: 0.8.1 (Development version)
└── Extension D: 3.0.0 (Major update)
```

#### **3. Extension Release Strategy**
- **Alpha Releases**: 0.x.x versions for development and testing
- **Beta Releases**: 1.x.x versions for feature-complete testing
- **Stable Releases**: 2.x.x+ versions for production use
- **Major Updates**: Breaking changes require major version increment

### **Extension Version Management**

#### **Version File Structure**
```json
{
  "name": "islamwiki/dashboard-extension",
  "version": "2.1.0",
  "description": "Role-based dashboard system",
  "compatibility": {
    "islamwiki": ">=0.0.1.0",
    "php": ">=8.1"
  }
}
```

#### **Version Update Process**
1. **Feature Development**: Implement new features in development branch
2. **Version Planning**: Determine appropriate version increment
3. **Testing**: Comprehensive testing with new version
4. **Release**: Tag new version and update documentation
5. **Distribution**: Release through extension repository

### **Extension Compatibility Matrix**

#### **Core Platform Compatibility**
```
Extension Version | Site Version | Status
-----------------|---------------|--------
1.0.0            | 0.0.1.0      | ✅ Compatible
1.5.0            | 0.0.1.0      | ✅ Compatible
2.0.0            | 0.0.1.0      | ⚠️ May have breaking changes
2.1.0            | 0.0.1.0      | ✅ Compatible
3.0.0            | 0.0.1.0      | ❌ Breaking changes
```

### **Extension Version Best Practices**

#### **1. Semantic Versioning**
- **Follow semantic versioning strictly** for extension releases
- **Document breaking changes** clearly in release notes
- **Maintain backward compatibility** within major versions
- **Use pre-release tags** for alpha/beta versions

#### **2. Compatibility Testing**
- **Test with multiple site versions** before release
- **Verify extension interoperability** with other extensions
- **Maintain compatibility matrix** for users
- **Provide migration guides** for breaking changes

#### **3. Release Communication**
- **Clear release notes** for each version
- **Breaking change warnings** prominently displayed
- **Migration instructions** for major updates
- **Compatibility information** clearly stated

---

## 🔄 **Site vs Extension Versioning**

### **Key Differences**

| Aspect | Site Versioning | Extension Versioning |
|--------|-----------------|---------------------|
| **Scope** | Entire platform | Individual extension |
| **Frequency** | Planned releases | Independent releases |
| **Dependencies** | Affects all components | Minimal platform impact |
| **Breaking Changes** | Major version increments | Extension-specific increments |
| **Compatibility** | Platform-wide | Extension-specific |

### **Version Synchronization**

#### **When to Sync**
- **Major platform updates** that affect extension APIs
- **Security updates** that require extension updates
- **Architecture changes** that impact extension development
- **Breaking changes** in core systems

#### **When to Stay Independent**
- **Feature additions** to individual extensions
- **Bug fixes** specific to extension functionality
- **Performance improvements** within extension scope
- **UI/UX enhancements** that don't affect core systems

### **Best Practices Summary**

1. **Keep extensions independent** unless core changes require updates
2. **Document compatibility** clearly for each extension version
3. **Test thoroughly** before releasing extension updates
4. **Communicate changes** clearly to extension users
5. **Maintain backward compatibility** within major versions

---

## 📋 **Version Planning Process**

### **Release Cycle**
```
Development Cycle:
├── Planning Phase (2 weeks)
│   ├── Feature planning
│   ├── Architecture review
│   └── Community feedback
├── Development Phase (6-8 weeks)
│   ├── Feature implementation
│   ├── Testing and QA
│   └── Documentation updates
├── Release Phase (2 weeks)
│   ├── Final testing
│   ├── Release preparation
│   └── Community announcement
```

### **Phase Transition Criteria**
- **0.0.0.x → 0.0.1.x**: When major restructuring begins
- **0.0.1.x → 0.0.2.x**: When restructuring is complete and feature development begins
- **0.0.2.x → 0.1.0.x**: When architecture is stable and production-ready
- **0.1.x.x → 1.0.0.x**: When ready for production release

---

## 🎯 **Current Status**

### **Current Version**: 0.0.1.0
- **Phase**: Restructuring & Major Changes
- **Status**: Documentation restructuring completed
- **Next**: Site restructuring begins (0.0.1.1)
- **Stability**: Unstable, breaking changes expected

### **Development Focus**
- **Complete site restructuring** to match new architecture
- **Implement core systems** with Islamic naming conventions
- **Modernize extension system** architecture
- **Prepare for feature development** phase (0.0.2.x)

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Versioning Strategy Complete ✅ 