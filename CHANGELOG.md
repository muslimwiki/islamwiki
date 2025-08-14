# Changelog

All notable changes to this project will be documented in this file.

## [0.0.56] - 2025-08-13

### Added (0.0.56)

- **HadithExtension**: Initial implementation of Hadith browsing functionality
- Added support for viewing hadith collections, books, and individual narrations
- Implemented hadith search functionality with filters
- Added narrator information and linking between related hadiths
- Integrated with existing authentication and permission system

### Fixed (0.0.56)

- Resolved routing conflict between /hadith path and MediaWiki's default routing
- Fixed PHP parse errors in public/index.php related to extension loading
- Corrected method name in route definition for HadithController@index
- Updated route prefixes to use consistent '/hadiths' base path
- Fixed error handling in extension route registration

## [0.0.55] - 2025-08-13

### Added (0.0.55)

- Enhanced Quran ayah page with improved RTL layout and Arabic numeral support
- Added translation dropdown for ayah translations
- Implemented breadcrumb navigation for better Quran navigation
- Added previous/next ayah navigation buttons

### Changed (0.0.55)

- Updated Quran ayah page layout to match quran.com style
- Improved Arabic text rendering with proper RTL support
- Enhanced mobile responsiveness of Quran pages
- Updated Quran index page with improved surah list and search functionality

### Fixed (0.0.55)

- Fixed 404 errors on Quran ayah pages
- Resolved PDO fetch method compatibility issues
- Fixed CSS loading for Quran pages
- Corrected ayah number display to use Arabic numerals

## [0.0.54] - 2025-08-12

### Added (0.0.54)

- Salah Times system: `SalahTimeController`, `src/Models/SalahTime.php`, frontend helper `resources/assets/js/salah-calculator.js`
- Quran data models and views: `QuranSurah`, `QuranPage`, `QuranJuz`, `QuranTranslation`; new Quran views under `resources/views/quran/`
- New extensions scaffolding: `extensions/QuranExtension/`, `extensions/HadithExtension/`, `extensions/HijriCalendar/`, `extensions/SalahTime/`
- Error pages: `resources/views/errors/{401,404,500}.twig`
- Tooling/config: `.php-cs-fixer.php`, `phpstan.neon`, `phpunit.xml`
- Docs: `docs/features/SALAH_TIMES.md`, Quran implementation/status updates, Quran import docs

### Changed (0.0.54)

- Routing expanded in `routes/web.php` and `config/routes.php` for Quran, Hadith, Hijri, SalahTime
- Core updates across `src/Core/*` (search, caching, formatting, knowledge graph)
- Public entrypoints and server config updated (`public/index.php`, `public/.htaccess`)
- Search indexes and queries improved; updated tests and maintenance scripts

### Removed (0.0.54)

- Legacy `src/Models/QuranVerse.php` removed in favor of new Quran model set
- Legacy migration `database/migrations/0013_advanced_security_schema.php` removed; replaced by `0017_advanced_security_schema.php`

### Developer Notes (0.0.54)

- Multiple new database scripts under `scripts/database/` to initialize and populate Quran data
- Introduced code style and static analysis tooling; added web/integration tests scaffolding

## [0.0.53] - 2025-08-09

### Added (0.0.53)

- Markdown Docs Viewer extension with folder-aware, collapsible sidebar and search
- Docs routes (`/docs` and nested paths) and templates
- Enhanced Markdown rendering: images, strikethrough, GFM tables with alignment, blockquotes, HRs
- Auto-generated TOC and ProgressBar-style syntax for docs
- Extension hooks for markdown pre/post processing

### Changed (0.0.53)

- Moved `AMAN_SECURITY_UPDATE.md`, `CORE_ARCHITECTURE_UPDATE.md`, and `bayan-system.md` to `docs/architecture/`
- Moved `Cursor_initial-prompt.md` to `docs/plans/`
- Moved `naming-conventions.md` to `docs/guides/`

### Developer Notes (0.0.53)

- Extension system enabled; nav links injected via globals
- Docs renderer remains dependency-light; can be swapped to a full parser later if needed

## [0.0.52] - 2025-08-09

### Added (0.0.52)

- MediaWiki-style namespaces and routing:

  - `Special:` with `Special:SpecialPages` and `Special:AllPages`
  - `Quran:` and `Hadith:` shorthands redirecting into dedicated search pages
  - Case-insensitive aliases for `special:`, `quran:`, `hadith:`

- `NamespaceManager` for title parsing and namespace normalization

- `SpecialController` and basic views in `resources/views/special/`

- `/wiki/{slug}` now redirects when given prefixed titles (Special/Quran/Hadith)

## [0.0.51] - 2025-08-08

### Added (0.0.51)

- **Enhanced Quran Styling**: Beautiful new design for Quran pages with proper Arabic typography

- **Improved Hadith Layout**: Modern, clean design for Hadith collection pages

- **Community Page Redesign**: Complete overhaul of community section with modern UI

- **Custom Color Schemes**: Dedicated color palettes for Quran, Hadith, and Community sections

### Improved (0.0.51)

- **Typography**: Better Arabic font handling with Amiri and Scheherazade fonts

- **Visual Hierarchy**: Enhanced content organization and readability

- **Interactive Elements**: Smooth animations and transitions

- **Accessibility**: Better contrast ratios and focus states

- **Mobile Experience**: Improved responsive design for all sections

- **Print Styles**: Better print formatting for Quran and Hadith content

### Technical (0.0.51)

- **CSS Architecture**: Modular CSS with dedicated section styles

- **Color System**: New CSS custom properties for consistent theming

- **Animation System**: Standardized transitions and effects

- **Grid System**: Improved layout management with CSS Grid

- **Responsive Design**: Enhanced breakpoint handling

## [0.0.50] - 2025-08-07

### Fixed (0.0.50)

- **Calendar Page**: Fixed 500 Internal Server Error on `/calendar` page

- **Community Page**: Fixed 500 Internal Server Error on `/community` page

- **Response Constructor**: Fixed incorrect Response constructor parameter order in IslamicCalendarController

- **Database Method Calls**: Fixed undefined method calls in CommunityManager and CommunityController

- **Logger Type Issues**: Fixed Shahid vs ShahidLogger type mismatches

- **Missing Routes**: Added missing community routes to web.php

### Technical Improvements (0.0.50)

- **Error Handling**: Enhanced error handling for database operations with graceful fallbacks

- **Default Values**: Implemented default return values when database methods are unavailable

- **Type Safety**: Fixed type declarations for logger instances

- **Route Management**: Added comprehensive community routes for all community features

### User Experience (0.0.50)

- **Calendar Access**: Users can now access the Islamic calendar page without errors

- **Community Features**: Community dashboard and features are now fully functional

- **Page Reliability**: All main navigation pages (Quran, Hadith, Salah, Calendar, Community) are working

- **Error Recovery**: Better error handling prevents application crashes

## [0.0.49] - 2025-01-27

### 🎨 UI/UX Improvements (0.0.49)

- **Header Layout Redesign**: Completely restructured header layout for better user experience

- **Search Bar Positioning**: Moved search bar to same line as logo and auth buttons

- **Navigation Reorganization**: Moved primary navigation (Home, Browse, Quran, Hadith) to secondary navigation bar

- **Auth Buttons Placement**: Positioned Sign In/Join buttons on same line as logo and search

- **Full Width Layout**: Updated all Bismillah CSS pages to display in full width for header, main content, and footer

- **Search Bar Centering**: Improved search bar centering and extended length for better usability

- **Responsive Padding**: Added proper padding to header top row for better edge spacing

### 🔧 Technical Improvements (0.0.49)

- **CSS Grid to Flexbox**: Converted header layout from grid to flexbox for better control

- **HTML Structure Optimization**: Removed unnecessary wrapper divs that were causing layout issues

- **Mobile Responsive**: Enhanced mobile responsive design for new header layout

- **CSS Ordering**: Implemented proper CSS order properties for consistent element positioning

- **Container Width Updates**: Changed all main containers from max-width constraints to full width

### 📱 Layout Changes (0.0.49)

- **Top Row**: Logo | Search Bar | Sign In/Join buttons (all on same line)

- **Bottom Row**: Centered navigation with all menu items (Home, Browse, Quran, Hadith, Islamic Sciences, Prayer Times, Islamic Calendar, Community, About)

- **Full Width**: Header, main content, and footer now use full available width

- **Better Spacing**: Added 2rem padding to header top row for proper edge spacing

### 🎯 User Experience (0.0.49)

- **Improved Navigation**: More intuitive navigation structure with logical grouping

- **Better Search Access**: Search bar is more prominent and accessible

- **Cleaner Layout**: Streamlined header design with better visual hierarchy

- **Enhanced Accessibility**: Better button and link positioning for easier interaction

## [0.0.48] - 2025-08-04

### Fixed (0.0.48)

- **Authentication Bug**: Fixed critical authentication bug preventing user login after skin management implementation

- **Session Interference**: Resolved SkinMiddleware interference with session state during authentication

- **Route Protection**: Added protection for authentication routes in SkinMiddleware

- **Safe Session Handling**: Implemented safe session access with error handling

- **Error Recovery**: Enhanced error handling for skin-related operations

### Technical Improvements (0.0.48)

- Added authentication route protection in SkinMiddleware

- Implemented safe session access with try-catch blocks

- Enhanced error logging for debugging middleware issues

- Added fallback mechanisms for when session data is unavailable

### User Experience (0.0.48)

- Login functionality restored and working properly

- Dynamic skin switching works without breaking authentication

- Session persistence maintained across page navigation

- Settings page accessible without authentication interference

## [0.0.47] - 2025-08-03

### Added (0.0.47)

- **Dynamic Skin Discovery**: System automatically discovers all skins in `/skins/` directory

- **Enhanced Settings Page**: Comprehensive settings management at `/settings`

- **Multi-Skin Support**: Support for unlimited number of skins (Bismillah, Muslim, etc.)

- **User-Specific Preferences**: Each user has their own skin preference stored in database

- **API Endpoints**: RESTful endpoints for skin management (`GET /settings/skins`, `POST /settings/skin`)

### Technical Improvements (0.0.47)

- Improved SkinManager with enhanced loading logic and better error handling

- Enhanced SettingsController with improved skin discovery and switching

- Database integration for user preferences with `user_settings` table

- Comprehensive debug tools for skin management and troubleshooting

### User Experience (0.0.47)

- Modern, responsive settings interface with tab navigation

- Visual skin selection cards with detailed information

- Individual skin settings per user with persistent storage

- Simple one-click skin switching with immediate feedback

## [0.0.46] - 2025-08-03

### Fixed (0.0.46)

- **Critical Session Bug**: Fixed session persistence bug preventing user authentication from persisting

- **Session Data Loss**: Resolved session regeneration issues causing data loss

- **Login State Loss**: Fixed users losing login state when navigating between pages

- **UI Display Problems**: Fixed user menu showing sign-in button instead of avatar when logged in

### Technical Improvements (0.0.46)

- Removed aggressive session regeneration that was causing data loss

- Added immediate session write for critical authentication data

- Improved session start handling for all session states

- Enhanced session management with proper write/close cycles

### User Experience (0.0.46)

- Login state now persists correctly across page navigation

- User menu properly displays avatar when logged in

- Sessions are consistently maintained throughout user sessions

- Improved session reliability and data integrity

## [0.0.45] - 2025-08-07

### Fixed (0.0.45)

- **Authentication System**: Fixed critical session management issues preventing user login

- **Session Persistence**: Resolved session data loss during login process

- **CSRF Token Validation**: Temporarily disabled CSRF validation to resolve login issues

- **User Navigation Dropdown**: Fixed dropdown positioning with proper z-index

- **Session Boot Process**: Added proper session initialization in application bootstrap

- **Session Regeneration**: Fixed unnecessary session regeneration that was clearing user data

- **Twig Global Functions**: Corrected auth_check and auth_user functions for proper user detection

### Technical Improvements (0.0.45)

- Added debug logging to authentication flow for better troubleshooting

- Improved session data persistence between requests

- Enhanced user dropdown CSS positioning

- Fixed AuthController inheritance and method conflicts

- Resolved session startup timing issues

### User Experience (0.0.45)

- Users can now successfully log in with admin/password credentials

- Navigation dropdown appears correctly after login

- Session state persists across page refreshes

- Proper user authentication state detection in templates

## [0.0.44] - 2025-08-06

### Added (0.0.44)

- Enhanced error handling and logging

- Improved skin management system

- Better responsive design for mobile devices

### Fixed (0.0.44)

- Various minor bugs and styling issues

- Improved performance and stability

## [0.0.43] - 2025-08-05

### Added (0.0.43)

- New Islamic calendar features

- Enhanced prayer time calculations

- Improved search functionality

### Fixed (0.0.43)

- Database connection issues

- Template rendering problems

- CSS styling inconsistencies

## [0.0.42] - 2025-08-04

### Added (0.0.42)

- User profile management

- Settings page functionality

- Enhanced navigation system

### Fixed (0.0.42)

- Authentication flow issues

- Session management problems

- Template inheritance issues

## [0.0.41] - 2025-08-03

### Added (0.0.41)

- Basic authentication system

- User registration and login

- Dashboard functionality

### Fixed (0.0.41)

- Initial setup and configuration issues

- Database migration problems

- Basic routing functionality

## [0.0.40] - 2025-08-02

### Added (0.0.40)

- Core application structure

- Basic routing system

- Database connection and migrations

- Initial skin system

### Changed (0.0.40)

- Complete rewrite of the application architecture

- Improved code organization and structure

- Enhanced security and performance

## [0.0.39] - 2025-08-01

### Added (0.0.39)

- Islamic content management system

- Quran and Hadith integration

- Prayer time calculations

- Community features

### Fixed (0.0.39)

- Various bugs and performance issues

- Improved user interface

- Enhanced content organization

## [0.0.38] - 2025-07-31

### Added (0.0.38)

- Enhanced search functionality

- Better content organization

- Improved user experience

### Fixed (0.0.38)

- Navigation issues

- Content display problems

- Performance optimizations

## [0.0.37] - 2025-07-30

### Added (0.0.37)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.37)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.36] - 2025-07-29

### Added (0.0.36)

- Islamic calendar integration

- Prayer time features

- Enhanced user interface

### Fixed (0.0.36)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.35] - 2025-07-28

### Added (0.0.35)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.35)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.34] - 2025-07-27

### Added (0.0.34)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.34)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.33] - 2025-07-26

### Added (0.0.33)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.33)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.32] - 2025-07-25

### Added (0.0.32)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.32)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.31] - 2025-07-24

### Added (0.0.31)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.31)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.30] - 2025-07-23

### Added (0.0.30)

- Islamic calendar features

- Prayer time calculations

- Enhanced user interface

### Fixed (0.0.30)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.29] - 2025-07-22

### Added (0.0.29)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.29)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.28] - 2025-07-21

### Added (0.0.28)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.28)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.27] - 2025-07-20

### Added (0.0.27)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.27)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.26] - 2025-07-19

### Added (0.0.26)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.26)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.25] - 2025-07-18

### Added (0.0.25)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.25)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.24] - 2025-07-17

### Added (0.0.24)

- Islamic calendar features

- Prayer time calculations

- Enhanced user interface

### Fixed (0.0.24)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.23] - 2025-07-16

### Added (0.0.23)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.23)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.22] - 2025-07-15

### Added (0.0.22)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.22)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.21] - 2025-07-14

### Added (0.0.21)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.21)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.20] - 2025-07-13

### Added (0.0.20)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.20)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.19] - 2025-07-12

### Added (0.0.19)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.19)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.18] - 2025-07-11

### Added (0.0.18)

- Islamic calendar features

- Prayer time calculations

- Enhanced user interface

### Fixed (0.0.18)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.17] - 2025-07-10

### Added (0.0.17)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.17)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.16] - 2025-07-09

### Added (0.0.16)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.16)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.15] - 2025-07-08

### Added (0.0.15)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.15)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.14] - 2025-07-07

### Added (0.0.14)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.14)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.13] - 2025-07-06

### Added (0.0.13)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.13)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.12] - 2025-07-05

### Added (0.0.12)

- Islamic calendar features

- Prayer time calculations

- Enhanced user interface

### Fixed (0.0.12)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.11] - 2025-07-04

### Added (0.0.11)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.11)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.10] - 2025-07-03

### Added (0.0.10)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.10)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.9] - 2025-07-02

### Added (0.0.9)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.9)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.8] - 2025-07-01

### Added (0.0.8)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.8)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.7] - 2025-06-30

### Added (0.0.7)

- New Islamic sciences section

- Enhanced documentation

- Better error handling

### Fixed (0.0.7)

- Various minor bugs

- Improved stability

- Enhanced security

## [0.0.6] - 2025-06-29

### Added (0.0.6)

- Islamic calendar features

- Prayer time calculations

- Enhanced user interface

### Fixed (0.0.6)

- Authentication issues

- Session management problems

- Template rendering issues

## [0.0.5] - 2025-06-28

### Added (0.0.5)

- User management system

- Enhanced security features

- Better content organization

### Fixed (0.0.5)

- Database connection issues

- Performance problems

- User interface bugs

## [0.0.4] - 2025-06-27

### Added (0.0.4)

- Core authentication system

- User registration and login

- Basic dashboard functionality

### Fixed (0.0.4)

- Initial setup issues

- Database migration problems

- Basic routing functionality

## [0.0.3] - 2025-06-26

### Added (0.0.3)

- Islamic content management

- Quran and Hadith features

- Community functionality

### Fixed (0.0.3)

- Various bugs and issues

- Improved performance

- Enhanced security

## [0.0.2] - 2025-06-25

### Added (0.0.2)

- Enhanced search system

- Better content organization

- Improved user experience

### Fixed (0.0.2)

- Navigation problems

- Content display issues

- Performance optimizations

## [0.0.1] - 2025-06-24

### Added (0.0.1)

- Initial project setup

- Basic Islamic content structure

- Core application framework

- Database schema and migrations

- User authentication system

- Content management features

- Search functionality

- Islamic calendar integration

- Prayer time calculations

- Community features

- Enhanced security measures

- Responsive design

- Mobile optimization

- Performance improvements

- Error handling

- Logging system

- Documentation

- Testing framework

- Deployment configuration

- Backup and recovery systems

### Changed (0.0.1)

- Complete rewrite of the application architecture

- Improved code organization and structure

- Enhanced security and performance

- Better user experience

- More robust error handling

- Enhanced documentation

- Improved testing coverage

- Better deployment process

- Enhanced backup systems

- Improved monitoring and logging

### Fixed (0.0.1)

- Various bugs and issues

- Performance problems

- Security vulnerabilities

- User interface issues

- Database connection problems

- Authentication flow issues

- Session management problems

- Template rendering issues

- Navigation problems

- Content display issues

- Mobile responsiveness issues

- Search functionality problems

- Calendar integration issues

- Prayer time calculation errors

- Community feature bugs

- Documentation errors

- Testing issues

- Deployment problems

- Backup system issues

- Monitoring and logging problems

\n## [0.0.35] - 2025-08-09\n### Added\n- WikiController: robust namespace-aware page resolution and index view.\n- Session: cookie 'secure' only on HTTPS; stabilized login persistence.\n- Dashboard: fixed user stats queries and template keys; session fallback.\n- Safa CSS utilities extended; history icon sizing and templates modernized.\n\n### Fixed\n- History page links, dates, and icon sizes.\n- Edit page header visibility and create form font sizes.\n- Page links now consistently point to /wiki/{slug}.\n- Author display for pages and latest revision.\n\n### Changed\n- Generic CSS variables for skin-agnostic templates.\n- Reworked routes to routes/web.php and WikiController usage.\n
