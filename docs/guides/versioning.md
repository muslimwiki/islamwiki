# Versioning Strategy

This document explains the semantic versioning approach used in IslamWiki.

## Overview

IslamWiki follows **Semantic Versioning (SemVer)** with a logical progression that reflects the development stages of the project.

## Version Format

```
MAJOR.MINOR.PATCH
```

- **MAJOR**: Breaking changes or major feature additions
- **MINOR**: New features in a backwards-compatible manner
- **PATCH**: Backwards-compatible bug fixes

## Development Stages

### 🔧 **Core Infrastructure (0.0.x)**

The foundation phase focuses on building a stable, secure core system.

#### **0.0.1** - Foundation
- Basic routing and templating system
- Error handling and logging
- PSR-7 compatibility
- Dependency injection container
- Service provider system

#### **0.0.2** - Authentication & Security
- User authentication system
- Session management with secure cookies
- CSRF protection
- Database foundation and migrations
- User registration and login

#### **0.0.6** - Security Middleware
- Enterprise-level security protection
- Rate limiting and input validation
- SQL injection and XSS prevention
- Comprehensive security headers
- Professional error handling

#### **0.0.7** - Application Stability
- Environment variable fixes
- Application bootstrap improvements
- Enhanced error handling
- Robust environment variable access

### 📝 **Wiki Features (0.1.x)**

The wiki functionality phase adds core wiki capabilities.

#### **0.1.0** - Wiki Page System
- Complete page CRUD operations
- Page model with relationships
- Revision tracking and history
- Page permissions and locking
- View count analytics

#### **0.1.1** - Content Rendering
- Enhanced markdown support
- Syntax highlighting with Prism.js
- Auto-linking and code blocks
- Professional content styling
- Comprehensive markdown parsing

#### **0.1.2** - Pages Index & Browsing
- Complete pages listing
- Search and filter capabilities
- Professional grid layout
- Pagination support
- Page actions and navigation

### 🚀 **Future Progression**

#### **0.2.x** - Major Features
- Quran integration and search
- Hijri calendar functionality
- Advanced user management
- Enhanced content features

#### **0.3.x** - Additional Major Features
- Advanced wiki features
- Community features
- Performance optimizations

#### **1.0.0** - Production Ready
- Complete, stable wiki system
- Production-ready features
- Comprehensive documentation
- Full testing coverage

## Versioning Rules

### When to Increment MAJOR (x.0.0)
- Breaking changes to APIs
- Major architectural changes
- Incompatible database schema changes
- Complete feature rewrites

### When to Increment MINOR (0.x.0)
- New major features added
- Significant functionality improvements
- New major subsystems
- Backwards-compatible enhancements

### When to Increment PATCH (0.0.x)
- Bug fixes
- Security patches
- Performance improvements
- Documentation updates
- Minor feature additions

## Current Status

**Current Version**: 0.1.2

**Stage**: Wiki Features Phase
- ✅ Core infrastructure complete and stable
- ✅ Basic wiki functionality implemented
- ✅ Content rendering with markdown support
- ✅ Pages browsing and search functionality

**Next Milestone**: 0.2.0 - Major Features
- Quran integration
- Hijri calendar
- Advanced user features

## Version History

### 0.1.x Series (Wiki Features)
- **0.1.0**: Wiki Page System
- **0.1.1**: Content Rendering
- **0.1.2**: Pages Index & Browsing

### 0.0.x Series (Core Infrastructure)
- **0.0.1**: Foundation
- **0.0.2**: Authentication & Security
- **0.0.6**: Security Middleware
- **0.0.7**: Application Stability

## Best Practices

1. **Always document breaking changes** in the changelog
2. **Test thoroughly** before releasing new versions
3. **Update documentation** with each release
4. **Follow semantic versioning** strictly
5. **Communicate changes** clearly to users

## Release Process

1. **Development**: Work on features in development branch
2. **Testing**: Comprehensive testing of all changes
3. **Documentation**: Update changelog and documentation
4. **Version Update**: Increment appropriate version number
5. **Tag Release**: Create git tag for the release
6. **Deploy**: Deploy to production environment

## Migration Guidelines

### From 0.0.x to 0.1.x
- No breaking changes expected
- New wiki features available
- Enhanced content rendering

### From 0.1.x to 0.2.x
- May include breaking changes
- Major new features added
- Database migrations may be required

### From 0.x.x to 1.0.0
- Production-ready release
- Stable API and features
- Complete documentation 