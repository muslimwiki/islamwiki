# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2025-07-30

### Added
- **Wiki Page System**: Complete page creation, viewing, and management
  - Page model with Eloquent-like relationships and revision tracking
  - PageController with full CRUD operations for wiki pages
  - Page templates for display, editing, and history viewing
  - Content rendering with basic wiki text parsing and HTML conversion
  - View count tracking with database analytics and user tracking
  - Page permissions system (edit, delete, lock) based on user roles
  - Page history and revision tracking functionality
  - Page locking system for admin-only content protection
  - Dynamic homepage with recent pages display and excerpts
- **Request Enhancements**: Added `isXmlHttpRequest()` method for AJAX detection
- **Template System**: New page templates with proper Twig inheritance

### Changed
- **PageController**: Fixed template path resolution (`pages.show` → `pages/show`)
- **Database Operations**: Improved query builder usage and error handling
- **View Count System**: Enhanced page view tracking with proper database updates
- **Template Rendering**: Enhanced Twig template structure and inheritance

### Fixed
- **Page Viewing**: Resolved 500 errors when accessing individual wiki pages
- **Template Paths**: Fixed template resolution issues in PageController
- **Database Queries**: Fixed raw SQL method calls and view count updates
- **Container Binding**: Resolved view renderer binding in test environment
- **Session Management**: Fixed session warnings in CLI environment
- **Request Class**: Added missing `isXmlHttpRequest()` method for AJAX detection

## [0.1.0] - 2025-07-30

### Added
- **Session Management System**: Complete session handling with secure configuration
  - SessionManager class with HTTP-only, SameSite cookies
  - Session regeneration for security against session fixation
  - User authentication state management
  - Remember me functionality with secure tokens
- **CSRF Protection**: Comprehensive cross-site request forgery protection
  - CsrfMiddleware for form protection
  - CSRF token generation and verification
  - User-friendly error pages for token mismatches
  - Automatic token inclusion in login/register forms
- **Authentication Middleware**: Route protection for authenticated users
  - AuthenticationMiddleware for protected routes
  - Automatic redirect to login with return URL preservation
  - Session-based authentication checks
- **Database Foundation**: Complete database setup and migration system
  - Custom migration framework with proper schema management
  - User authentication tables with proper relationships
  - Sample data creation for development
  - Auto-increment fixes for primary keys
- **User Authentication**: Complete user management system
  - User registration with validation
  - Secure login with password hashing
  - User model with proper database interactions
  - Password verification and user lookup
- **Homepage Enhancement**: Dynamic homepage with recent pages
  - Database-driven recent pages display
  - Page excerpts and creation dates
  - Professional styling for page listings
  - Navigation improvements with page browsing

### Fixed
- **Database Schema Issues**: Resolved auto-increment problems in migrations
- **User Model Integration**: Fixed password field mapping and verification
- **Session Configuration**: Proper session startup and cookie settings
- **Container Dependencies**: Resolved service provider registration issues
- **Form Security**: Added CSRF tokens to all authentication forms

### Changed
- **Version Bump**: Major version increment to 0.1.0 for session management milestone
- **Authentication Flow**: Updated to use secure session management
- **Form Security**: All forms now include CSRF protection
- **Homepage**: Now displays dynamic content from database
- **Session Handling**: Replaced basic session handling with robust SessionManager

### Technical Details
- **Session Security**: HTTP-only cookies, SameSite=Lax, secure session regeneration
- **CSRF Protection**: Token-based protection with proper validation
- **Database**: MySQL with proper migrations and sample data
- **Authentication**: Session-based with remember me support
- **Middleware**: Authentication and CSRF middleware for route protection

## [0.0.1] - 2025-07-29

## [0.0.1] - 2025-07-29

### Added
- **Foundation Reset**: Project version reset to 0.0.1 as new baseline for all future microchanges
- **Alpine.js Integration**: Lightweight frontend interactivity framework for progressive enhancement
- **Twig Templating**: Server-side template rendering with proper layouts and inheritance
- **Modern UI Components**: Responsive design with clean styling and interactive elements
- **Service Provider System**: ViewServiceProvider for TwigRenderer registration
- **Comprehensive Error Handling**: 404/500 pages, pretty error output, robust file logging
- **PSR-7 Compatibility**: Proper HTTP request/response handling throughout the application
- **Dependency Injection**: Container properly configured for all controller dependencies

### Fixed
- **Router Issues**: Fixed FastRouter dependency injection and controller instantiation
- **Request Type Mismatch**: Resolved PSR-7 ServerRequest vs custom Request class conflicts
- **Template Rendering**: Fixed Twig template path resolution and caching issues
- **File Permissions**: Resolved logging and cache directory permission issues
- **Service Registration**: Fixed ViewServiceProvider registration in Application bootstrap
- **Controller Dependencies**: Ensured all controllers receive proper dependencies via container

### Changed
- **Homepage**: Now uses Twig templates with Alpine.js interactive demo
- **Dashboard**: Interactive dashboard with dynamic stats, activity feed, and watchlist management
- **Layout System**: New responsive layout with proper navigation and styling
- **Error Pages**: Comprehensive error handling with detailed debug information
- **Documentation**: Updated README and documentation structure for new architecture

### Technical Details
- **Routing**: FastRouter with ControllerFactory for proper dependency injection
- **Templating**: TwigRenderer with disabled caching in development mode
- **Frontend**: Alpine.js 3.x for lightweight JavaScript interactivity
- **Styling**: Modern CSS with responsive design and component-based architecture
- **Logging**: File-based logging with proper error handling and debug information

### Features
- **Interactive Counter**: Alpine.js counter with increment/decrement/reset functionality
- **Dynamic Messaging**: Real-time message editing with Alpine.js data binding
- **Alert System**: Success/error/info alerts with dismiss functionality
- **Dashboard Stats**: Interactive statistics with refresh simulation
- **Activity Feed**: Collapsible recent activity with user actions
- **Watchlist Management**: Add/remove pages from watchlist with Alpine.js
- **Responsive Navigation**: Clean navigation between homepage and dashboard
