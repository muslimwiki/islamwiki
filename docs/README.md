<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
# IslamWiki Documentation

Welcome to the IslamWiki documentation. This guide covers all aspects of the application, from installation to development and deployment.

## Table of Contents

### Getting Started
- [Installation Guide](installation.md)
- [Quick Start](quick-start.md)
- [Configuration](configuration.md)

### Architecture
- [Overview](architecture/overview.md)
- [Core Components](architecture/components.md)
- [Service Providers](architecture/service-providers.md)
- [Dependency Injection](architecture/dependency-injection.md)

### Development
- [Routing](development/routing.md)
- [Controllers](development/controllers.md)
- [Views & Templates](development/views.md)
- [Frontend (Alpine.js)](development/frontend.md)
- [Error Handling](development/error-handling.md)
- [Logging](development/logging.md)

### Security
- [Session Management](security/session-management.md)
- [Authentication](security/authentication.md)
- [CSRF Protection](security/csrf-protection.md)

### Features
- [Homepage](features/homepage.md)
- [Dashboard](features/dashboard.md)
- [Wiki Pages](features/wiki-pages.md)
- [Interactive Components](features/interactive-components.md)

### Deployment
- [Production Setup](deployment/production.md)
- [Performance](deployment/performance.md)
- [Security](deployment/security.md)

### Contributing
- [Development Guidelines](contributing/guidelines.md)
- [Testing](contributing/testing.md)
- [Code Standards](contributing/standards.md)

---

## Quick Reference

### Current Version: 0.2.0
- **Status**: Wiki page system complete, individual page viewing working
- **Architecture**: PHP 8.1+, Twig templates, Alpine.js frontend, secure sessions
- **License**: AGPL-3.0

### Key Technologies
- **Backend**: PHP 8.1+, FastRoute, Twig, PSR-7
- **Frontend**: Alpine.js, Modern CSS, Progressive Enhancement
- **Development**: Composer, Service Providers, Dependency Injection

### Getting Help
- Check the [changelog](../CHANGELOG.md) for recent changes
- Review [error logs](../storage/logs/) for debugging
- Create an issue on GitHub for bugs or feature requests

---

## Version History

### 0.2.0 (Current)
- ✅ Wiki page system with complete CRUD operations
- ✅ Page model with Eloquent-like relationships
- ✅ PageController with full template rendering
- ✅ Content rendering with basic wiki text parsing
- ✅ View count tracking and analytics
- ✅ Page permissions (edit, delete, lock)
- ✅ Page history and revision tracking
- ✅ Dynamic homepage with recent pages
- ✅ Session management with secure HTTP-only cookies
- ✅ CSRF protection on all forms
- ✅ User authentication (registration, login, logout)
- ✅ Authentication middleware for route protection
- ✅ Database foundation with migration system
- ✅ Remember me functionality
- ✅ Alpine.js integration for lightweight interactivity
- ✅ Twig templating with proper layouts
- ✅ Comprehensive error handling and logging
- ✅ PSR-7 compatible HTTP handling
- ✅ Dependency injection container

### Planned Features (0.2.1)
- 🔄 Enhanced content rendering (markdown, syntax highlighting)
- 🔄 Search functionality with full-text search
- 🔄 User profiles and contribution tracking
- 🔄 Rich text editor for page editing
- 🔄 API endpoints for external integration
- 🔄 Media upload and management
