# SalahTime Extension Changelog

All notable changes to the SalahTime extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Advanced prayer time calculations with astronomical algorithms
- Integration with external prayer time APIs
- Enhanced location services with GPS support
- Prayer time notifications and reminders
- Advanced qibla direction calculations
- Hijri calendar integration improvements

## [0.0.2] - 2024-12-19

### Added
- **Enhanced prayer time calculations** with multiple calculation methods
- **Improved location services** with better timezone handling
- **Advanced qibla direction** calculations with compass integration
- **Lunar phase display** with accurate moon phase calculations
- **Hijri calendar integration** for Islamic date display
- **Prayer time widgets** for easy integration into pages
- **Admin interface** for managing prayer time settings
- **User profile integration** for personal prayer time preferences

### Changed
- **Calculation accuracy** improved for all prayer times
- **Location detection** enhanced with better geolocation support
- **Widget system** optimized for better performance
- **Template system** improved for better customization

### Fixed
- **Timezone calculation** issues in certain regions
- **Prayer time accuracy** problems for high latitude locations
- **Widget display** issues on mobile devices
- **Location update** problems in admin interface

### Technical Details
- **Prayer calculation algorithms** updated to latest standards
- **Database schema** optimized for better performance
- **API endpoints** improved for better reliability
- **Error handling** enhanced with comprehensive logging

## [0.0.1] - 2024-12-19

### Added
- **Initial release** of SalahTime extension
- **Basic prayer time calculations** using standard Islamic methods
- **Location-based prayer times** with timezone support
- **Simple prayer time display** templates
- **Basic qibla direction** calculations
- **Core extension architecture** following IslamWiki standards
- **Hook system integration** for content and page display
- **Resource management** for CSS, JavaScript, and templates
- **Configuration system** for customizable prayer time settings
- **Basic admin interface** for managing prayer time data

### Technical Details
- **Extension class** extending `IslamWiki\Core\Extensions\Extension`
- **Hook registration** for ContentParse, PageDisplay, and SearchIndex
- **Resource management** for CSS, JavaScript, and template files
- **Configuration management** with default settings and customization options
- **Template integration** with main application layout

---

## Release Notes

### Version 0.0.2 - Enhanced Prayer Time System
This release introduces significant improvements to prayer time calculations, location services, and user interface. The extension now provides more accurate prayer times and better integration with the IslamWiki platform.

**Key Improvements:**
- Enhanced prayer time calculation accuracy
- Improved location services and timezone handling
- Advanced qibla direction calculations
- Better widget system and admin interface

**Breaking Changes:**
- None - this is a backward-compatible release

**Migration Guide:**
No migration required. Existing prayer time data and settings will be automatically upgraded.

### Version 0.0.1 - Initial Release
The initial release provides basic prayer time functionality with standard Islamic calculation methods and location-based timezone support.

**Features:**
- Basic prayer time calculations
- Location-based timezone support
- Simple prayer time display
- Basic qibla direction calculations
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

- **0.0.2** - Enhanced prayer time system with improved calculations
- **0.0.1** - Initial release with basic prayer time functionality

---

*This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format and is maintained by the IslamWiki development team.* 