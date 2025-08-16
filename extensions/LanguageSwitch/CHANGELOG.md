# LanguageSwitch Extension Changelog

All notable changes to the LanguageSwitch extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Hybrid translation system with Google Translate API integration
- Translation memory and quality scoring
- User feedback system for translations
- Advanced caching and performance optimization
- Support for additional languages (Urdu, Turkish, Persian)

## [0.0.2] - 2024-12-19

### Added
- **Complete Arabic text translation system** with 200+ translations
- **Arabic Translation Plugin** (`arabic-translations.js`) for comprehensive interface translation
- **Enhanced Language Switch** component with translation integration
- **Dynamic content translation** using MutationObserver for new content
- **Translation status indicators** showing when translations are activated
- **Fallback system** for graceful degradation if plugins fail to load
- **Demo pages** (`arabic-demo.html`, `test.html`) for testing functionality
- **Comprehensive documentation** including implementation summaries
- **Self-contained component** with embedded CSS and JavaScript for immediate functionality

### Changed
- **Language switch template** moved to `resources/views/extensions/LanguageSwitch/` for proper Twig loading
- **Component architecture** enhanced to support translation plugins
- **RTL support** improved with better layout handling and Arabic typography
- **Event system** enhanced with detailed language change events
- **Performance optimization** with efficient translation application and caching

### Fixed
- **Template loading errors** by correcting file paths for Twig template loader
- **Resource loading issues** by embedding CSS and JavaScript in template
- **Extension system integration** problems with proper hook registration
- **RTL layout issues** with comprehensive CSS support for Arabic content

### Technical Details
- **File structure** reorganized for better maintainability
- **Translation coverage** expanded to include navigation, forms, buttons, and system messages
- **Plugin architecture** designed for extensibility to additional languages
- **Error handling** improved with comprehensive fallback mechanisms
- **Accessibility** enhanced with proper ARIA labels and keyboard navigation

## [0.0.1] - 2024-12-19

### Added
- **Initial release** of LanguageSwitch extension
- **Basic language switching** between English and Arabic
- **RTL layout support** for Arabic language
- **Beautiful Islamic-themed UI** with smooth animations
- **Persistent language preferences** using localStorage
- **Mobile responsive design** for all device sizes
- **Accessibility features** including keyboard navigation and screen reader support
- **Extension architecture** following IslamWiki extension standards
- **Configuration system** for customizable language options
- **Hook system** integration with IslamWiki core

### Technical Details
- **Extension class** extending `IslamWiki\Core\Extensions\Extension`
- **Hook registration** for ContentParse, PageDisplay, and ComposeViewGlobals
- **Resource management** for CSS, JavaScript, and template files
- **Configuration management** with default settings and customization options
- **Template integration** with main application layout

---

## Release Notes

### Version 1.0.0 - Major Feature Release
This release introduces a comprehensive Arabic translation system that goes beyond simple RTL layout changes. The extension now provides full text translation of the interface, making IslamWiki truly accessible to Arabic-speaking users.

**Key Improvements:**
- Complete interface translation from English to Arabic
- Enhanced user experience with translation status indicators
- Robust fallback system for reliability
- Comprehensive documentation and testing tools

**Breaking Changes:**
- Template path changed from `extensions/LanguageSwitch/templates/language-switch.twig` to `extensions/LanguageSwitch/language-switch.twig`
- Component class renamed from `LanguageSwitch` to `EnhancedLanguageSwitch`

**Migration Guide:**
Update any custom includes to use the new template path:
```twig
<!-- Old path (no longer works) -->
{% include 'extensions/LanguageSwitch/templates/language-switch.twig' %}

<!-- New path -->
{% include 'extensions/LanguageSwitch/language-switch.twig' %}
```

### Version 0.0.1 - Initial Release
The initial release provides basic language switching functionality with RTL support and beautiful Islamic-themed design. This version focuses on core functionality and user experience.

**Features:**
- Language switching between English and Arabic
- RTL layout support for Arabic content
- Persistent language preferences
- Mobile responsive design
- Full accessibility support

---

## Contributing

When contributing to this extension, please:

1. **Update this changelog** with your changes
2. **Follow semantic versioning** for releases
3. **Document breaking changes** clearly
4. **Include migration guides** when necessary
5. **Test thoroughly** before submitting changes

## Version History

- **0.0.2** - Major feature release with Arabic translation system
- **0.0.1** - Initial release with basic language switching functionality

---

*This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format and is maintained by the IslamWiki development team.* 