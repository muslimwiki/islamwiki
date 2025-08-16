# IslamWiki Terminology

## Core Concepts

### 1. Extensions
**Definition**: Self-contained components that add specific functionality to IslamWiki. Extensions are modular and can be enabled/disabled without affecting core functionality.

**Characteristics**:
- Can function independently
- Have their own namespace
- Can define hooks for plugins
- Can include database schemas
- Can provide APIs

**Examples**:
- `SalahTimes` - Prayer times calculator
- `QuranViewer` - Quran text and translations
- `Ummah` - Community features

### 2. Plugins
**Definition**: Lightweight additions that modify or extend existing functionality, typically hooking into extension or core systems.

**Characteristics**:
- Depend on extensions or core
- Don't have their own namespace
- Lightweight and focused
- Can't function independently

**Examples**:
- `DarkMode` - Modifies the active skin
- `SocialShare` - Adds sharing buttons to content
- `RelatedContent` - Shows related articles

### 3. Skins
**Definition**: Control the visual presentation of the site. A skin can have multiple variations and settings.

**Characteristics**:
- Control layout and styling
- Can have settings
- Can include custom templates
- Can be extended by plugins

**Examples**:
- `Bismillah` - Default Islamic theme
- `Minimal` - Lightweight theme
- `DarkMode` - Dark variant of active skin

### 4. Hooks
**Definition**: Points in the code where plugins can register callbacks to modify behavior or add functionality.

**Types**:
- **Action Hooks**: Execute code at specific points
- **Filter Hooks**: Modify data before it's used
- **Event Hooks**: Listen for and respond to events

**Example**:
```php
// Registering a hook
$hookManager->addAction('after_content_render', function($content) {
    return $content . "<div class='related-content'>...</div>";
});
```

## Content Types

### 1. Articles
Structured content with support for:
- Categories and tags
- Featured images
- Custom fields
- Revisions
- Comments (optional)

### 2. Fatwas
Specialized content type for Islamic rulings:
- Question/Answer format
- Scholar attribution
- References to Quran/Hadith
- Verification status

### 3. Sahaba
Profiles of the Prophet's companions:
- Biographical information
- Contributions
- Related hadith
- Timeline of events

### 4. Quran
Complete Quranic text:
- Multiple translations
- Tafsir integration
- Word-by-word analysis
- Recitation audio

## Technical Terms

### 1. Namespaces
Logical divisions of content and functionality:
- `Core:` System functionality
- `User:` User-generated content
- `Media:` Uploaded files
- `Template:` Reusable templates
- `Category:` Content categories
- `Help:` Documentation
- `Module:` Extensions and plugins

### 2. Parser
Processes content from markup to HTML:
- Supports multiple syntaxes (Markdown, WikiText)
- Extensible through plugins
- Cached output for performance

### 3. Cache System
Multi-layer caching:
- Page cache
- Object cache
- Fragment cache
- Opcode cache

### 4. API Endpoints
RESTful endpoints for:
- Content management
- User authentication
- Search
- Extensions

## Security Terms

### 1. Roles & Permissions
- **Roles:** Groups of permissions (Admin, Editor, Contributor, Subscriber)
- **Capabilities:** Specific actions users can perform
- **Access Control:** Fine-grained permission system

### 2. CSRF Protection
Security measures to prevent Cross-Site Request Forgery attacks.

### 3. Rate Limiting
Controls the number of requests a user can make in a given time period.

### 4. Input Validation
Ensuring all user input is properly sanitized and validated.

## Community Features (Ummah Extension)

### 1. User Profiles
- Customizable profiles
- Activity streams
- Follow system
- Privacy controls

### 2. Social Features
- Private messaging
- Forums
- News feed
- Friends system
- Groups

### 3. Engagement
- Reactions
- Comments
- Bookmarks
- Notifications
- Badges & achievements

## Development Terms

### 1. Composer
Dependency manager for PHP used to manage:
- Core dependencies
- Extensions
- Plugins
- Development tools

### 2. Migration
Database schema versioning and updates.

### 3. Hook System
Event-driven architecture for extending functionality.

### 4. Service Container
Dependency injection container for managing class dependencies.
