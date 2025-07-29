# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
