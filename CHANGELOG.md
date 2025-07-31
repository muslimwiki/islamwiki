# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.28] - 2025-07-31

### Added
- **User Skin System**: Complete implementation of modular skin architecture
  - User-facing skins stored in `/skins/` directory
  - Backend skin management in `src/Skins/`
  - JSON-based skin configuration (`skin.json`)
  - Case-insensitive skin lookup and switching
  - Automatic skin discovery and loading
  - Integration with LocalSettings.php for skin activation
  - Support for CSS, JavaScript, and layout template customization
  - View helpers for skin functionality (`skin_css`, `skin_js`, `skin_name`, etc.)

### Changed
- **Architecture**: Moved from source-based skins to user-facing skin system
  - Skins now stored in `/skins/` instead of `src/Skins/`
  - Configuration via JSON instead of PHP classes
  - Simplified skin creation and management
  - All styling now comes from active skin, not local templates

### Technical Details
- Added `SkinManager` class for skin discovery and management
- Added `UserSkin` class for handling user-defined skins
- Added `SkinServiceProvider` for application integration
- Updated `TwigRenderer` to support global variables
- Enhanced `Application` to register skin service provider
- Updated `LocalSettings.php` with skin configuration options

### Documentation
- Comprehensive skin system documentation in `docs/skins/`
- User skin architecture guide
- Skin creation tutorials and examples
- Testing scripts for skin system validation

## [0.0.27] - 2025-07-30

### Added
- Enhanced error handling and logging system
- Improved database connection management
- Better configuration system integration

## [0.0.26] - 2025-07-29

### Added
- Islamic calendar integration
- Prayer times calculation system
- Enhanced user authentication

## [0.0.25] - 2025-07-28

### Added
- Quran and Hadith database integration
- Search functionality improvements
- Community features

## [0.0.24] - 2025-07-27

### Added
- Basic wiki functionality
- User management system
- Content management features

## [0.0.23] - 2025-07-26

### Added
- Initial application structure
- Core framework components
- Database schema implementation

## [0.0.22] - 2025-07-25

### Added
- Project initialization
- Basic routing system
- Template engine integration

## [0.0.21] - 2025-07-24

### Added
- Foundation setup
- Development environment configuration
- License and documentation structure

## [0.0.20] - 2025-07-23

### Added
- Initial project structure
- Core application framework
- Basic documentation

## [0.0.19] - 2025-07-22

### Added
- Project initialization
- Development setup
- Basic framework structure

## [0.0.18] - 2025-07-21

### Added
- Initial commit
- Project foundation
- Basic structure and documentation
