# Changelog

All notable changes to IslamWiki will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.35] - 2025-08-02

### Added
- **Complete User Authentication System** - Full login/logout functionality with session management
  - `AuthManager` - Complete authentication system with database integration
  - `SessionManager` - Secure session handling with proper configuration
  - User profile pages with private and public viewing
  - User dropdown menu with ZamZam.js integration
- **Navigation & UI Improvements** - Enhanced user interface
  - Search bar in top navigation with comprehensive functionality
  - User dropdown menu with Dashboard, Profile, Settings, and Logout links
  - Responsive design with mobile-friendly navigation
  - Proper authentication state detection and display
- **Project Organization** - Improved file structure and organization
  - Moved 73 test files from `public/` to `tests/web/`
  - Moved 20 debug files from `public/` to `debug/`
  - Clean public directory with only web-accessible files
  - Proper web application structure following best practices
- **Service Provider System** - Enhanced dependency injection
  - `SkinServiceProvider` - Properly registered skin management system
  - Settings binding for all service providers
  - Unified container management across the application
  - All service providers properly registered and booted
- **Security Improvements** - Enhanced security features
  - Session security with proper configuration
  - Password hashing with bcrypt
  - CSRF protection for forms
  - Input validation and sanitization
  - Development files moved out of web-accessible directory

### Changed
- Updated version to 0.0.35
- Reorganized project structure for better security and organization
- Enhanced service provider system with proper registration
- Improved authentication system with secure session handling
- Updated file organization following web application best practices

### Fixed
- **Critical Fixes**
  - Profile page error: "No binding found for [skin.manager]"
  - Settings binding missing for LoggingServiceProvider
  - Session regeneration warnings
  - Container conflicts in dependency injection
- **Navigation Fixes**
  - Missing user dropdown for authenticated users
  - Authentication state detection and display
  - Search bar functionality in top navigation
  - Mobile navigation display issues
- **File Organization**
  - Test files properly organized in `tests/web/`
  - Debug files properly organized in `debug/`
  - Clean public directory structure
  - Updated .gitignore for new organization

### Security
- Moved development files out of web-accessible directory
- Enhanced session security with proper configuration
- Implemented secure password hashing
- Added CSRF protection for forms
- Improved input validation and sanitization

## [0.0.34] - 2025-08-01

### Added
- **Bayan Knowledge Graph System** - Complete knowledge graph implementation for Islamic concepts
  - `BayanManager` - Main orchestrator for the knowledge graph system
  - `NodeManager` - Handles knowledge graph nodes (concepts, verses, hadith, etc.)
  - `EdgeManager` - Manages relationships between nodes
  - `QueryManager` - Handles complex graph queries and traversals
- **Database Schema** - 6 new tables for knowledge graph functionality
  - `bayan_nodes` - Stores knowledge graph nodes
  - `bayan_edges` - Stores relationships between nodes
  - `bayan_node_types` - Predefined node types
  - `bayan_edge_types` - Predefined relationship types
  - `bayan_graph_metrics` - Cached graph metrics
  - `bayan_search_index` - Full-text search index
- **Web Interface** - Modern, responsive UI for knowledge graph management
  - Dashboard with real-time statistics
  - Search interface with filters
  - Node creation form with validation
  - Relationship visualization
  - Graph metrics display
- **API Endpoints** - RESTful API for programmatic access
  - `POST /bayan/create` - Create new node
  - `POST /bayan/relationship` - Create relationship
  - `GET /bayan/statistics` - Get graph statistics
  - `GET /bayan/paths` - Find paths between nodes
- **Service Provider** - `BayanServiceProvider` for system registration
- **Controller** - `BayanController` for HTTP request handling
- **Database Migration** - Migration 0016 for complete schema creation
- **Views** - Twig templates for web interface
  - `bayan/index.twig` - Main dashboard
  - `bayan/search.twig` - Search interface
  - `bayan/create.twig` - Node creation
- **Routes** - New routes for knowledge graph functionality
- **Testing** - Comprehensive test scripts
  - `test-bayan.php` - System test script
  - `debug-bayan.php` - Debug script
  - `bayan-test.php` - Web interface test
- **Default Data** - 10 node types and 10 relationship types pre-configured
- **Graph Features** - Advanced graph functionality
  - Path finding between nodes
  - Hub node identification
  - Centrality calculations
  - Full-text search capabilities
  - Relationship-aware search
  - Statistics and metrics

### Changed
- Updated version to 0.0.34
- Enhanced service provider system with Bayan integration
- Improved controller architecture with RESTful design
- Enhanced database migration system

### Fixed
- Service provider registration issues
- Container binding problems
- Database migration execution
- Error handling in knowledge graph operations

### Security
- Input validation and sanitization for node creation
- SQL injection prevention
- XSS protection
- Error handling without information disclosure

## [0.0.33] - 2025-07-31

### Added
- Enhanced search functionality with advanced filters
- Improved user interface with better responsive design
- Additional security features for user authentication

### Changed
- Updated database schema for better performance
- Improved error handling throughout the application

### Fixed
- Several minor bugs in the authentication system
- Performance issues with large datasets

## [0.0.32] - 2025-07-30

### Added
- New prayer times calculation system
- Enhanced Islamic calendar features
- Improved hadith search functionality

### Changed
- Updated Quran integration with better verse handling
- Enhanced user profile system

### Fixed
- Issues with prayer time calculations in certain locations
- Problems with hadith search accuracy

## [0.0.31] - 2025-07-29

### Added
- Comprehensive Islamic calendar system
- Advanced hadith collection management
- Enhanced scholar profile system

### Changed
- Improved database structure for better performance
- Enhanced security measures

### Fixed
- Calendar display issues in different timezones
- Hadith search performance problems

## [0.0.30] - 2025-07-28

### Added
- Quran verse integration system
- Enhanced user authentication
- Improved search functionality

### Changed
- Updated database schema for better data organization
- Enhanced security protocols

### Fixed
- Authentication issues in certain browsers
- Search result accuracy problems

## [0.0.29] - 2025-07-27

### Added
- Advanced configuration management system
- Enhanced security features
- Improved user interface

### Changed
- Updated version numbering system
- Enhanced error handling

### Fixed
- Configuration loading issues
- Security vulnerabilities

## [0.0.28] - 2025-07-26

### Added
- Enhanced skin system with new themes
- Improved user customization options
- Better mobile responsiveness

### Changed
- Updated skin loading mechanism
- Enhanced user experience

### Fixed
- Skin switching issues
- Mobile display problems

## [0.0.27] - 2025-07-25

### Added
- New user profile system
- Enhanced community features
- Improved search capabilities

### Changed
- Updated user interface design
- Enhanced database performance

### Fixed
- User profile display issues
- Search functionality problems

## [0.0.26] - 2025-07-24

### Added
- Advanced logging system
- Enhanced error handling
- Improved debugging tools

### Changed
- Updated logging configuration
- Enhanced error reporting

### Fixed
- Logging issues in production environment
- Error handling problems

## [0.0.25] - 2025-07-23

### Added
- Enhanced database migration system
- Improved data integrity checks
- Better backup and restore functionality

### Changed
- Updated database schema
- Enhanced data validation

### Fixed
- Migration execution issues
- Data integrity problems

## [0.0.24] - 2025-07-22

### Added
- New extension system
- Enhanced plugin architecture
- Improved modularity

### Changed
- Updated extension loading mechanism
- Enhanced plugin management

### Fixed
- Extension loading issues
- Plugin compatibility problems

## [0.0.23] - 2025-07-21

### Added
- Advanced routing system
- Enhanced URL handling
- Improved navigation

### Changed
- Updated routing configuration
- Enhanced URL generation

### Fixed
- Routing issues in certain scenarios
- URL generation problems

## [0.0.22] - 2025-07-20

### Added
- Enhanced session management
- Improved user authentication
- Better security features

### Changed
- Updated session handling
- Enhanced security protocols

### Fixed
- Session management issues
- Authentication problems

## [0.0.21] - 2025-07-19

### Added
- New configuration system
- Enhanced settings management
- Improved system administration

### Changed
- Updated configuration handling
- Enhanced settings interface

### Fixed
- Configuration loading issues
- Settings management problems

## [0.0.20] - 2025-07-18

### Added
- Enhanced database connection system
- Improved data access layer
- Better query optimization

### Changed
- Updated database architecture
- Enhanced query performance

### Fixed
- Database connection issues
- Query performance problems

## [0.0.19] - 2025-07-17

### Added
- New error handling system
- Enhanced debugging capabilities
- Improved error reporting

### Changed
- Updated error handling mechanism
- Enhanced debugging tools

### Fixed
- Error handling issues
- Debugging problems

## [0.0.18] - 2025-07-16

### Added
- Enhanced view rendering system
- Improved template engine
- Better UI/UX

### Changed
- Updated view rendering mechanism
- Enhanced template system

### Fixed
- View rendering issues
- Template problems

## [0.0.17] - 2025-07-15

### Added
- New container system
- Enhanced dependency injection
- Improved service management

### Changed
- Updated container architecture
- Enhanced service registration

### Fixed
- Container issues
- Service management problems

## [0.0.16] - 2025-07-14

### Added
- Enhanced HTTP request/response system
- Improved middleware architecture
- Better request handling

### Changed
- Updated HTTP handling mechanism
- Enhanced middleware system

### Fixed
- HTTP handling issues
- Middleware problems

## [0.0.15] - 2025-07-13

### Added
- New routing system
- Enhanced URL handling
- Improved navigation

### Changed
- Updated routing mechanism
- Enhanced URL generation

### Fixed
- Routing issues
- URL generation problems

## [0.0.14] - 2025-07-12

### Added
- Enhanced application bootstrap
- Improved initialization process
- Better startup handling

### Changed
- Updated bootstrap mechanism
- Enhanced initialization

### Fixed
- Bootstrap issues
- Initialization problems

## [0.0.13] - 2025-07-11

### Added
- New core application structure
- Enhanced framework architecture
- Improved modularity

### Changed
- Updated application architecture
- Enhanced modular design

### Fixed
- Architecture issues
- Modularity problems

## [0.0.12] - 2025-07-10

### Added
- Enhanced database schema
- Improved data modeling
- Better data organization

### Changed
- Updated database structure
- Enhanced data relationships

### Fixed
- Database schema issues
- Data modeling problems

## [0.0.11] - 2025-07-09

### Added
- New project structure
- Enhanced file organization
- Improved codebase organization

### Changed
- Updated project structure
- Enhanced file organization

### Fixed
- Project structure issues
- File organization problems

## [0.0.10] - 2025-07-08

### Added
- Initial project setup
- Basic framework structure
- Core application files

### Changed
- N/A

### Fixed
- N/A

---

## Version History

- **0.0.34** - Bayan Knowledge Graph System (Current)
- **0.0.33** - Enhanced Search and Security
- **0.0.32** - Prayer Times and Islamic Calendar
- **0.0.31** - Islamic Calendar and Hadith Management
- **0.0.30** - Quran Integration and Authentication
- **0.0.29** - Configuration Management
- **0.0.28** - Enhanced Skin System
- **0.0.27** - User Profile System
- **0.0.26** - Advanced Logging
- **0.0.25** - Database Migration System
- **0.0.24** - Extension System
- **0.0.23** - Advanced Routing
- **0.0.22** - Session Management
- **0.0.21** - Configuration System
- **0.0.20** - Database Connection
- **0.0.19** - Error Handling
- **0.0.18** - View Rendering
- **0.0.17** - Container System
- **0.0.16** - HTTP System
- **0.0.15** - Routing System
- **0.0.14** - Application Bootstrap
- **0.0.13** - Core Architecture
- **0.0.12** - Database Schema
- **0.0.11** - Project Structure
- **0.0.10** - Initial Setup
