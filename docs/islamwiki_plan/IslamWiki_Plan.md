# IslamWiki Development Plan

## Overview
A comprehensive Islamic knowledge platform built with Islamic principles and modern technology, featuring:
- Tightly-coupled, database-centric architecture
- Robust extension and plugin system
- Islamic-first design principles
- High performance and security focus
- Community features with privacy controls

## Core Architecture (أساسيات النظام)

### Design Principles
- **Tightly-Coupled Components**: Seamless integration between core components while maintaining clean interfaces
- **Database-Centric**: MySQL/MariaDB with Redis for caching and performance
- **Extensible Core**: Core functionality implemented as extensions for better maintainability
- **Islamic-First**: Built-in support for Islamic content types, metadata, and features
- **Enterprise Security**: Comprehensive security model with RBAC and regular security audits
- **Performance Focus**: Built-in caching, asset optimization, and scalability features
- **Content-First**: Powerful content management with version control and collaboration
- **Community Integration**: Ummah features for user interaction and engagement

### 1. Sabil Routing System (سبيل) - IMPLEMENTED
- **Features**:
  - RESTful routing with support for all HTTP methods
  - Route grouping and middleware support
  - Clean, SEO-friendly URLs
  - Controller/method and closure support
  - Route parameters and pattern matching
  - Namespace support for controllers
  - Route model binding
  - Rate limiting and throttling
  - CSRF protection
  - Route caching for performance
- **Content Types**: 
  - Core: Articles, Pages, Media
  - Islamic: Fatwas, Sahaba, Quran, Hadith with Islamic metadata
  - Extensible through extensions
- **Template System**:
  - PHP-based templates for maximum performance
  - Twig support via extension
  - Template inheritance and sections
  - Layouts and components
  - Caching at template level

### Implementation Status (آخر تحديث: 15 ذو الحجة 1446)

### ✅ Completed Components
1. **Sabil Router (سبيل)**
   - Core routing functionality (GET, POST, PUT, DELETE, etc.)
   - Middleware pipeline support
   - Controller/method and closure routing
   - Route groups and prefixes
   - Route parameters and pattern matching
   - Namespace support for controllers
   - Basic error handling (404, 405)
   - Test coverage for core routing features

### 🚧 In Progress
1. **Asas Container (أساس)**
   - Basic container implementation
   - Service binding and resolution
   - Singleton support
   - Contextual binding
   - Service providers

2. **Aman Security (أمان)**
   - Authentication system
   - RBAC (Role-Based Access Control)
   - CSRF protection
   - Input validation
   - Rate limiting

3. **Core Content Management**
   - Content type definitions
   - Version control system
   - Revision management
   - Content relationships

4. **Extension Loader**
   - Extension discovery
   - Dependency resolution
   - Lifecycle management
   - Hook system integration

### 📅 Upcoming Milestones

#### Phase 1: Core Infrastructure (Q4 2025)
1. **Asas Container**
   - Service providers
   - Contextual binding
   - Tagged services
   - Extension integration

2. **Aman Security**
   - Two-factor authentication
   - Password policies
   - Session management
   - Security headers

3. **Content Management**
   - WYSIWYG editor
   - Media management
   - Content versioning
   - Search integration

#### Phase 2: Islamic Features (Q1 2026)
1. **Prayer Times**
   - Calculation methods
   - Location-based
   - Notifications
   - Qibla direction

2. **Hijri Calendar**
   - Date conversion
   - Islamic events
   - Moon sighting
   - Calendar widgets

3. **Quran & Hadith**
   - Text and translations
   - Tafsir integration
   - Search functionality
   - Bookmarking

#### Phase 3: Community & Admin (Q2 2026)
1. **Ummah Community**
   - User profiles
   - Private messaging
   - Discussion forums
   - Notifications

2. **Admin Dashboard**
   - System monitoring
   - User management
   - Content moderation
   - Extension management

3. **Theming System**
   - Template engine
   - Asset management
   - Theme customization
   - RTL support

## Extension System

### 1. Core Extensions (Bundled)
- **SalahTimes**: Prayer times and Qibla direction
- **QuranViewer**: Complete Quran with translations
- **HadithDatabase**: Major hadith collections
- **Ummah**: Community features (profiles, messaging, forums)
- **SecurityCenter**: Security and permissions management
- **PerformanceMonitor**: Real-time performance analysis

### 2. Extension Types
- **Content Extensions**: Add new content types
- **Feature Extensions**: Add functionality (e.g., search, comments)
- **Integration Extensions**: Connect with external services
- **Theme Extensions**: Skins and UI modifications

### 3. Plugin System
- Lightweight additions that hook into extensions
- Can modify behavior without changing core/extension code
- Managed through admin panel

### 4. Extension Manager
- One-click installation/updates
- Dependency resolution
- Security scanning
- Performance impact analysis

## Frontend Framework (واجهة المستخدم)

### 1. Safa CSS Framework (صافا)
- Lightweight (3.5KB gzipped)
- Islamic-themed components with RTL support
- Responsive grid system
- Utility-first CSS
- Dark mode support
- Print styles
- High contrast mode for accessibility

### 2. ZamZam.js Framework (زمزم)
- Lightweight (6KB gzipped)
- Reactive data binding
- Component system
- Islamic directives

### 3. Template System
- Twig template engine
- Template inheritance
- Component-based architecture
- Caching system

### 4. Responsive Design
- Mobile-first approach
- Flexible layouts
- Touch-friendly components
- Performance optimized

### 5. Islamic Design System
- Islamic color palette
- RTL support
- Islamic typography
- Theming system

### 6. Accessibility
- WCAG 2.1 AA compliance
- Keyboard navigation
- Screen reader support
- Reduced motion support

## Skin System (السمات)

### 1. Skin Management
- Dynamic skin loading
- Theme switching
- Skin inheritance
- Asset management

### 2. Included Skins
- **Bismillah Skin**: Primary Islamic theme
- **Muslim Skin**: Alternative theme
- **Minimal Skin**: Lightweight option
- **Dark Mode**: Eye-friendly interface

### 3. Skin Features
- Customizable colors
- Font size adjustment
- Layout options
- RTL support

### 4. Asset System
- Asset compilation
- Minification
- Versioning
- CDN support

## Islamic Features (المميزات الإسلامية)

### 1. Salah Times
- Accurate prayer time calculations
- Multiple calculation methods
- Qibla direction
- Adhan player

### 2. Hijri Calendar
- Islamic date conversion
- Important Islamic dates
- Calendar widget
- Event management

### 3. Quran Integration
- Complete Quran text
- Multiple translations
- Tafsir system
- Word-by-word analysis

### 4. Hadith System
- Major collections
- Search and filtering
- Chain of narration
- Grading information

### 5. Scholar Verification
- Scholar profiles
- Fatwa system
- Ijazah tracking
- Credential verification

## Content Management (إدارة المحتوى)

### 1. Content Types
- **Pages**: Hierarchical content (e.g., About, Contact)
- **Articles**: Blog-style posts with categories/tags
- **Custom Types**: Defined by extensions (Fatwas, Sahaba, etc.)
- **Media**: Advanced media library with Islamic metadata

### 2. Editing Experience
- **Default Editor**: Markdown with preview
- **Additional Editors**:
  - WYSIWYG (TinyMCE)
  - WikiText (through extension)
  - Code editor for advanced users
- **Version Control**:
  - Full revision history
  - Diff viewer
  - Rollback capability
  - Contributor tracking

### 3. Media Management
- **Media Library**:
  - Image/Video/Audio/Documents
  - Islamic metadata support
  - Bulk operations
  - Advanced search
- **Embedding**:
  - Quran verses
  - Hadith references
  - Scholar profiles
  - External content (YouTube, etc.)

### 1. Page System
- Wiki-style editing
- Version control
- Content approval workflow
- Scheduled publishing

### 2. Iqra Search Engine (اقرأ)
- Full-text search
- Advanced filters
- Search suggestions
- Search analytics

### 3. Bayan Formatter (بيان)
- Markdown support
- Media embedding
- Table of contents
- Print styles

### 4. Community Features
- User profiles
- Content rating
- Comments system
- Social sharing

### 5. Moderation System
- Content flagging
- User reporting
- Moderation queue
- Automated filters

## User System (نظام المستخدمين)

### 1. Authentication
- Multiple methods:
  - Email/Password
  - Social login
  - Two-factor authentication
  - Phone verification
- Account recovery
- Session management

### 2. Roles & Permissions
- **Predefined Roles**:
  - Super Admin
  - Admin
  - Editor
  - Author
  - Contributor
  - Subscriber
- **Custom Roles**: Create with granular permissions
- **Access Control**:
  - Content-level permissions
  - Section restrictions
  - Time-based access

### 3. Ummah Community Features
- **User Profiles**:
  - Customizable profiles
  - Activity feeds
  - Follow system
  - Privacy controls
- **Social Features**:
  - Private messaging
  - Forums
  - Groups
  - Events
- **Engagement**:
  - Reactions
  - Bookmarks
  - Notifications
  - Badges & achievements

### 1. Authentication
- Email/password
- Social login
- Two-factor authentication
- Account recovery

### 2. User Profiles
- Custom profiles
- Activity feed
- Follow system
- Privacy settings

### 3. Role System
- Role-based access control
- Custom permissions
- User groups
- Content ownership

### 4. User Dashboard
- Personal dashboard
- Reading history
- Saved items
- Notifications

## API System (واجهة برمجة التطبيقات)

### 1. RESTful API
- Resource endpoints
- Authentication
- Rate limiting
- Versioning

### 2. API Documentation
- Interactive docs
- Code samples
- Authentication guide
- Rate limit information

### 3. API Security
- OAuth 2.0
- API keys
- Request signing
- IP whitelisting

## Extension System (نظام الإضافات)

### 1. Extension Framework
- Hook system
- Event listeners
- Service providers
- Dependency injection

### 2. Extension Management
- Installation
- Activation/deactivation
- Updates
- Dependencies

### 3. Extension Types
- Themes
- Plugins
- Widgets
- Language packs

## Database System (قاعدة البيانات)

### 1. Multi-Database Support
- MySQL/MariaDB
- PostgreSQL
- SQLite
- Database sharding

### 2. Migration System
- Version control
- Rollback support
- Seeding
- Schema management

### 3. Query Builder
- Fluent interface
- Query logging
- Prepared statements
- Transaction support

## Development Tools (أدوات المطورين)

### 1. Debug System
- Error handling
- Debug bar
- Query logging
- Performance metrics

### 2. Testing Framework
- Unit tests
- Feature tests
- Browser tests
- Test coverage

### 3. Documentation
- Code documentation
- User guides
- API reference
- Contribution guidelines

## Directory Structure
```
/islamwiki/
  /app/              # Application code
  /bootstrap/        # Framework bootstrap
  /config/           # Configuration files
  /database/         # Database migrations and seeds
  /public/           # Publicly accessible files
  /resources/        # Views, assets, and language files
  /routes/           # Route definitions
  /storage/          # Storage for logs, cache, etc.
  /tests/            # Test files
  /vendor/           # Composer dependencies
  /extensions/       # Extensions
  /themes/           # Theme files
  /content/          # User-generated content
```

## Content Structure (هيكل المحتوى)

### 1. Core Content
```
/content/
  /articles/          # Islamic articles
    /islamic-beliefs/
      tawheed.md
      prophets.md
    /fiqh/
      salah/
        wudu.md
        tayammum.md

  /fatwas/           # Islamic rulings
    /worship/
      salah/
        qunut.md
        qiraat.md
    /transactions/
      zakat/
        zakat-al-fitr.md
        zakat-on-gold.md

  /sahaba/           # Companions of the Prophet (SAW)
    /ashara-mubashara/
      abu-bakr.md
      umar-ibn-khattab.md
    /other-sahaba/
      salman-al-farisi.md
      bilal-ibn-rabah.md

  /quran/            # Quranic content
    /surahs/
      1/             # Surah Al-Fatiha
        1.md         # Ayah 1
        2.md         # Ayah 2
        3.md         # Ayah 3
        4.md         # Ayah 4
        5.md         # Ayah 5
        6.md         # Ayah 6
        7.md         # Ayah 7
      2/             # Surah Al-Baqarah
        1.md
        2.md
        # ...
        255.md       # Ayat al-Kursi
        # ...
    /tafsir/         # Tafsir books
      ibn-kathir/
        1/1.md
        1/2.md
        # ...
      tabari/
        1/1.md
        # ...

  /hadith/           # Hadith collections
    /bukhari/
      /1/            # Book 1
        1.md         # Hadith 1
        2.md
        # ...
    /muslim/
      /1/
        1.md
        # ...
    /abudawud/
      # ...
    /tirmidhi/
      # ...
    /ibnmajah/
      # ...
    /nasai/
      # ...

  /duas/             # Islamic supplications
    /daily/
      morning.md
      evening.md
    /prayer/
      qunut.md
      sujood.md
    /prophetic/
      istikhara.md
      travel.md
```

### 2. Media Structure
```
/media/
  /images/
    /quran/
      /1/            # Surah 1
        1.jpg        # Ayah 1 image
        2.jpg
    /sahaba/
      abu-bakr.jpg
      umar.jpg
  /audio/
    /quran/
      /hafs/
        1/1.mp3      # Surah 1, Ayah 1
        1/2.mp3
    /adhan/
      makkah.mp3
      madinah.mp3
  /videos/
    /lectures/
      tafseer/
        nouman-ali-khan/
          fatiha/
            1-intro.mp4
  /documents/
    /books/
      fiqh-us-sunnah-sayyid-sabiq.pdf
```

### 3. Template Structure
```
/resources/views/
  /layouts/
    app.twig         # Main layout
    auth.twig        # Authentication layout
    admin.twig       # Admin layout
  /partials/         # Reusable components
    header.twig
    footer.twig
    navigation.twig
  /pages/            # Page templates
    home.twig
    about.twig
    contact.twig
  /content/          # Content type templates
    article/
      single.twig
      list.twig
    fatwa/
      single.twig
      list.twig
    sahabi/
      single.twig
      list.twig
  /quran/
    surah.twig
    ayah.twig
    page.twig
  /hadith/
    single.twig
    collection.twig
    book.twig
  /errors/
    404.twig
    500.twig
```

## Development Phases (مراحل التطوير)

### Phase 1: Foundation (الأساس)
- [ ] Core routing system (Sabil)
- [ ] Dependency injection (Asas)
- [ ] Basic security (Aman)
- [ ] Error handling (Shahid)
- [ ] Basic theming (Safa CSS)

### Phase 2: Content Management (إدارة المحتوى)
- [ ] Content types and taxonomies
- [ ] Media management
- [ ] Search functionality (Iqra)
- [ ] Content formatting (Bayan)
- [ ] Basic user system

### Phase 3: Islamic Features (المميزات الإسلامية)
- [ ] Salah times calculation
- [ ] Hijri calendar
- [ ] Quran integration
- [ ] Hadith system
- [ ] Dua collection

### Phase 4: Community & Advanced (المجتمع والمميزات المتقدمة)
- [ ] User profiles and interactions
- [ ] Social features
- [ ] API system (Siraj)
- [ ] Extension framework
- [ ] Performance optimization

## Technical Stack (المكونات التقنية)

### Backend (الخلفية)
- **PHP 8.2+**: Modern PHP with strict types
- **Swoole**: High-performance coroutine server
- **Doctrine DBAL**: Database abstraction layer
- **Twig**: Templating engine
- **Symfony Components**: Foundational PHP components

### Frontend (واجهة المستخدم)
- **Safa CSS**: Islamic-themed CSS framework
- **ZamZam.js**: Lightweight JavaScript framework
- **Alpine.js**: Minimal framework for JavaScript behavior
- **Hijri.js**: Islamic date handling
- **Quran Audio**: Integrated audio player

### Database (قاعدة البيانات)
- **Primary**: MySQL 8.0+
- **Cache**: Redis
- **Search**: MeiliSearch
- **File Storage**: S3-compatible storage

### DevOps (نظام التشغيل)
- **Docker**: Containerization
- **GitHub Actions**: CI/CD
- **Kubernetes**: Orchestration
- **Monitoring**: Prometheus + Grafana
- **Logging**: ELK Stack

## Development Workflow

### Local Development
1. **Environment Setup**
   ```bash
   # Clone the repository
   git clone https://github.com/islamwiki/islamwiki.git
   cd islamwiki
   
   # Install dependencies
   composer install
   npm install
   
   # Configure environment
   cp .env.example .env
   php artisan key:generate
   
   # Run development server
   php artisan serve
   ```

2. **Coding Standards**
   - PSR-12 coding standard
   - PHPDoc blocks for all classes and methods
   - Unit tests for all new features
   - Feature tests for critical paths
   - E2E tests for user flows

3. **Version Control**
   - Feature branches from `develop`
   - Pull requests for code review
   - Semantic versioning (SemVer)
   - Conventional commits

## Development Guidelines (إرشادات التطوير)

### 1. Extension Development
- **Structure**:
  ```
  extension-name/
    config/
    controllers/
    models/
    views/
    assets/
    migrations/
    tests/
    extension.json
    README.md
  ```
- **Hooks System**:
  - Action Hooks: Perform actions at specific points
  - Filter Hooks: Modify data before use
  - Event System: Pub/Sub pattern for decoupled code

### 2. Security Guidelines
- **Input Validation**: All user input must be validated
- **Output Escaping**: Context-aware escaping
- **CSRF Protection**: Required for all state-changing operations
- **Rate Limiting**: Protect against abuse
- **Security Headers**: Implement best practices

### 3. Documentation
- **Inline Documentation**: PHPDoc for all classes/methods
- **User Guides**: Step-by-step tutorials
- **API Documentation**: For all public APIs
- **Examples**: Code samples for common tasks

### 4. Testing
- **Unit Tests**: Test individual components
- **Integration Tests**: Test component interactions
- **Browser Tests**: Test user interactions
- **Performance Tests**: Ensure scalability

### Content Creation (إنشاء المحتوى)
1. **Markdown Formatting**
   - Use CommonMark specification
   - 80-character line length for text
   - One sentence per line for better diffing
   - Reference-style links at the end of document

2. **YAML Front Matter**
   - Required fields: `title`, `date`, `author`
   - Recommended fields: `description`, `tags`, `category`
   - Islamic metadata: `hijri_date`, `scholar_reference`

3. **Naming Conventions**
   - Files: `kebab-case.md`
   - Directories: `snake_case`
   - Images: `descriptive-name-{size}.{ext}`
   - Arabic filenames: Use English transliteration

4. **References**
   - Use footnotes for references
   - Include primary sources (Quran, Hadith)
   - Cite scholarly works properly
   - Include verification status

### Theming (السمات)
1. **Template Structure**
   - Use Twig template inheritance
   - Break down into reusable components
   - Follow BEM methodology for CSS
   - Document template variables

2. **Responsive Design**
   - Mobile-first approach
   - Fluid typography
   - Flexible grids
   - Touch targets (min 44×44px)

3. **Islamic Design**
   - Right-to-left (RTL) support
   - Appropriate color schemes
   - Legible Arabic typography
   - Consider cultural sensitivities

### Performance (الأداء)

### 1. Caching System
- **Page Caching**: Full page caching for anonymous users
- **Object Caching**: Database query caching
- **Fragment Caching**: Partial page caching
- **OPcache**: PHP bytecode caching
- **Browser Caching**: Proper cache headers

### 2. Performance Monitoring
- **Real-time Analysis**:
  - Page load times
  - Database queries
  - Memory usage
  - Cache hit/miss ratios
- **Recommendations**: Automated suggestions for improvement
- **Logging**: Performance metrics over time

### 3. Optimization Features
- **Asset Management**:
  - CSS/JS minification
  - Asset versioning
  - Critical CSS inlining
  - Lazy loading of images/iframes
- **Database Optimization**:
  - Query optimization
  - Index management
  - Regular maintenance tasks

### 4. Parser Caching
- **Smart Parsing**: Only re-parse when content changes
- **Cache Invalidation**: Automatic when content is updated
- **Granular Control**: Cache settings per content type
1. **Caching Strategy**
   - Page caching
   - Fragment caching
   - Object caching
   - OPcache configuration

2. **Asset Optimization**
   - Bundle and minify CSS/JS
   - Image optimization
   - Font subsetting
   - Lazy loading

3. **Database Optimization**
   - Index optimization
   - Query optimization
   - Regular maintenance
   - Read replicas for scaling

## Getting Started (البدء)

### System Requirements (متطلبات النظام)
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **PHP**: 8.2+ with OPcache
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Cache**: Redis 6.0+ or Memcached
- **Node.js**: 18.0+ (for frontend assets)
- **Composer**: 2.0+

### Installation (التثبيت)
1. Clone the repository:
   ```bash
   git clone https://github.com/islamwiki/islamwiki.git
   cd islamwiki
   ```

2. Install PHP dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   npm run production
   ```

4. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Set up database:
   ```bash
   php artisan migrate --seed
   ```

6. Set permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

7. Configure web server:
   - Point document root to `/public`
   - Configure proper rewrite rules
   - Set up SSL certificate

8. Run the queue worker:
   ```bash
   php artisan queue:work
   ```

## Contributing (المساهمة)

### Code Style
- Follow PSR-12 coding standard
- Use type hints and return types
- Write PHPDoc blocks
- Keep methods small and focused

### Git Workflow
1. Fork the repository
2. Create a feature branch:
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. Commit your changes:
   ```bash
   git commit -m 'Add some amazing feature'
   ```
4. Push to the branch:
   ```bash
   git push origin feature/amazing-feature
   ```
5. Open a pull request

### Testing
- Write unit tests for new features
- Ensure all tests pass
- Update documentation as needed
- Test in multiple browsers

## License (الترخيص)

This project is licensed under the **GNU Affero General Public License v3.0 or later** (AGPL-3.0-or-later).

### Key Points:
- You may use, modify, and distribute this software
- You must make the source code available to users
- You must include the license and copyright notice
- You must state all significant changes made to the original software

### Additional Permissions:
- Additional use allowed under terms of Section 7 of AGPL-3.0
- Additional requirements may be stated in the [CONTRIBUTING.md](CONTRIBUTING.md) file

For the full license text, see [LICENSE](LICENSE) file.

## Support (الدعم)

For support, please open an issue on [GitHub](https://github.com/islamwiki/islamwiki/issues) or join our [community forum](https://community.islamwiki.org).

## Acknowledgments (الشكر والتقدير)

We would like to thank all the contributors who have helped make this project possible, as well as the open source community for their invaluable contributions to the tools and libraries we use.
