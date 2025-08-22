# Wiki Developer Guide - IslamWiki

**Version:** 0.0.2.1  
**Last Updated:** 2025-01-20  
**Status:** Complete Developer Guide ✅  

## 🎯 **Overview**

This developer guide provides comprehensive information for developers working with the IslamWiki WikiExtension. It covers architecture, development practices, API usage, and customization options.

## 🏗️ **Architecture Overview**

### **System Architecture**
```
WikiExtension/
├── 📁 Controllers/           # Request handling and business logic
├── 📁 Models/               # Data access and business logic
├── 📁 Services/             # Business logic and external integrations
├── 📁 Templates/            # Twig templates for views
├── 📁 Assets/               # CSS, JavaScript, and media files
├── 📁 Database/             # Migrations and database schemas
├── 📄 WikiExtension.php     # Main extension class
└── 📄 extension.json        # Extension configuration
```

### **Core Components**

#### **Controllers**
- **WikiController**: Main wiki functionality and homepage
- **PageController**: Individual page management (CRUD operations)
- **CategoryController**: Category management and organization
- **SearchController**: Search functionality and results
- **HistoryController**: Page revision history and management

#### **Models**
- **WikiPage**: Page data operations and business logic
- **WikiCategory**: Category management and relationships
- **WikiRevision**: Version control and revision history
- **WikiTag**: Tag system and content organization

#### **Services**
- **WikiService**: Core wiki business logic
- **SearchService**: Advanced search functionality
- **NotificationService**: User notification management
- **AnalyticsService**: Usage statistics and insights

## 🚀 **Getting Started with Development**

### **Prerequisites**
- **PHP 8.1+**: Modern PHP with strict typing
- **Composer**: Dependency management
- **MySQL 8.0+**: Database system
- **Twig**: Template engine
- **IslamWiki Core**: Core framework components

### **Development Environment Setup**
1. **Clone Repository**: Get the latest code
2. **Install Dependencies**: Run `composer install`
3. **Database Setup**: Run migrations and seed data
4. **Configuration**: Set up environment variables
5. **Testing**: Run the test suite

### **Quick Start Commands**
```bash
# Install dependencies
composer install

# Run database migrations
php database/migrate_wiki_tables.php

# Seed sample data
php database/seed_wiki_data.php

# Run tests
php tests/WikiExtension/TestRunner.php

# Start development server
php -S localhost:8000 -t public/
```

## 🔧 **Development Workflow**

### **Code Standards**
- **PSR-12**: Follow PSR-12 coding standards
- **Strict Typing**: Use `declare(strict_types=1)`
- **Islamic Naming**: Follow Islamic naming conventions
- **Documentation**: PHPDoc for all public methods
- **Error Handling**: Proper exception handling

### **Git Workflow**
```bash
# Create feature branch
git checkout -b feature/wiki-enhancement

# Make changes and commit
git add .
git commit -m "feat: Add advanced search functionality"

# Push and create pull request
git push origin feature/wiki-enhancement
```

### **Testing Strategy**
- **Unit Tests**: Test individual components
- **Integration Tests**: Test component interactions
- **Feature Tests**: Test complete user workflows
- **Performance Tests**: Load and stress testing

## 📚 **API Reference**

### **WikiController API**

#### **GET /wiki**
Display wiki homepage with featured content.

**Response:**
```json
{
  "featured_pages": [
    {
      "id": 1,
      "title": "The Golden Age of Islam",
      "slug": "golden-age-of-islam",
      "excerpt": "Overview of Islamic Golden Age...",
      "category": "Islamic History",
      "view_count": 150,
      "created_at": "2025-01-20T10:00:00Z"
    }
  ],
  "recent_pages": [...],
  "categories": [...],
  "statistics": {
    "total_pages": 100,
    "total_categories": 5,
    "total_views": 5000
  }
}
```

#### **GET /wiki/{slug}**
Display individual wiki page.

**Parameters:**
- `slug` (string): Page URL slug

**Response:**
```json
{
  "page": {
    "id": 1,
    "title": "The Golden Age of Islam",
    "slug": "golden-age-of-islam",
    "content": "# The Golden Age of Islam...",
    "meta_description": "Explore the remarkable achievements...",
    "category": {
      "id": 1,
      "name": "Islamic History",
      "slug": "islamic-history"
    },
    "tags": ["History", "Culture", "Science"],
    "creator": {
      "id": 1,
      "username": "admin",
      "display_name": "Administrator"
    },
    "created_at": "2025-01-20T10:00:00Z",
    "updated_at": "2025-01-20T10:00:00Z",
    "view_count": 150,
    "revision_count": 3
  },
  "related_pages": [...],
  "revision_history": [...]
}
```

### **PageController API**

#### **POST /wiki/create**
Create a new wiki page.

**Request Body:**
```json
{
  "title": "New Page Title",
  "content": "# Page Content\n\nPage content here...",
  "meta_description": "Brief description of the page",
  "category_id": 1,
  "content_type": "article",
  "tags": "tag1, tag2, tag3",
  "status": "published"
}
```

**Response:**
```json
{
  "success": true,
  "page_id": 123,
  "slug": "new-page-title",
  "message": "Page created successfully"
}
```

#### **PUT /wiki/{slug}**
Update existing wiki page.

**Request Body:**
```json
{
  "title": "Updated Page Title",
  "content": "# Updated Content\n\nNew content here...",
  "meta_description": "Updated description",
  "category_id": 2,
  "tags": "updated, tags, here",
  "edit_comment": "Updated content and improved formatting"
}
```

**Response:**
```json
{
  "success": true,
  "revision_id": 456,
  "message": "Page updated successfully"
}
```

#### **DELETE /wiki/{slug}**
Delete wiki page.

**Response:**
```json
{
  "success": true,
  "message": "Page deleted successfully"
}
```

### **CategoryController API**

#### **GET /wiki/categories**
List all wiki categories.

**Response:**
```json
{
  "categories": [
    {
      "id": 1,
      "name": "Islamic History",
      "slug": "islamic-history",
      "description": "Articles about Islamic history...",
      "icon": "fas fa-landmark",
      "color": "#007cba",
      "page_count": 25,
      "is_featured": true
    }
  ],
  "total_categories": 5,
  "featured_categories": [...]
}
```

#### **POST /wiki/category/create**
Create new category.

**Request Body:**
```json
{
  "name": "New Category",
  "description": "Category description",
  "icon": "fas fa-folder",
  "color": "#28a745",
  "parent_id": null,
  "is_featured": false
}
```

### **SearchController API**

#### **GET /wiki/search**
Search wiki content.

**Query Parameters:**
- `q` (string): Search query
- `type` (string): Search type (general, title, content, category)
- `category_id` (int): Filter by category
- `sort` (string): Sort order (relevance, date, title)
- `page` (int): Page number for pagination

**Response:**
```json
{
  "query": "islamic mathematics",
  "results": [
    {
      "id": 2,
      "title": "Islamic Contributions to Mathematics",
      "slug": "islamic-contributions-mathematics",
      "excerpt": "Islamic scholars made significant...",
      "relevance_score": 0.95,
      "category": "Islamic Sciences",
      "tags": ["Mathematics", "Science", "History"]
    }
  ],
  "total_results": 15,
  "current_page": 1,
  "total_pages": 2,
  "filters": {
    "categories": [...],
    "content_types": [...],
    "date_ranges": [...]
  }
}
```

### **HistoryController API**

#### **GET /wiki/{slug}/history**
Get page revision history.

**Response:**
```json
{
  "page": {
    "id": 1,
    "title": "The Golden Age of Islam",
    "slug": "golden-age-of-islam"
  },
  "revisions": [
    {
      "id": 1,
      "revision_number": 3,
      "content": "# The Golden Age of Islam...",
      "edit_comment": "Added new section about art",
      "is_current": true,
      "creator": {
        "id": 1,
        "username": "admin"
      },
      "created_at": "2025-01-20T15:30:00Z",
      "changes": {
        "added_lines": 15,
        "removed_lines": 3,
        "modified_sections": ["Art and Architecture"]
      }
    }
  ],
  "total_revisions": 3
}
```

## 🗄️ **Database Schema**

### **Core Tables**

#### **wiki_pages**
```sql
CREATE TABLE wiki_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    meta_description TEXT NULL,
    content_type ENUM('page', 'article', 'guide', 'reference') DEFAULT 'page',
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    is_featured BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    revision_count INT DEFAULT 1,
    creator_id BIGINT UNSIGNED NULL,
    last_editor_id BIGINT UNSIGNED NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **wiki_categories**
```sql
CREATE TABLE wiki_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(100) DEFAULT 'fas fa-folder',
    color VARCHAR(7) DEFAULT '#007cba',
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT TRUE,
    creator_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **wiki_revisions**
```sql
CREATE TABLE wiki_revisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    revision_number INT NOT NULL,
    content TEXT NOT NULL,
    edit_comment VARCHAR(500) NULL,
    is_minor BOOLEAN DEFAULT FALSE,
    is_current BOOLEAN DEFAULT FALSE,
    changes JSON NULL,
    creator_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Relationship Tables**

#### **wiki_page_categories**
```sql
CREATE TABLE wiki_page_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_page_category (page_id, category_id)
);
```

#### **wiki_page_tags**
```sql
CREATE TABLE wiki_page_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_page_tag (page_id, tag_id)
);
```

## 🎨 **Template System**

### **Template Structure**
```
templates/
├── 📄 index.twig          # Wiki homepage
├── 📄 show.twig           # Individual page display
├── 📄 edit.twig           # Page editing form
├── 📄 category.twig       # Category display
├── 📄 search.twig         # Search interface
├── 📄 history.twig        # Revision history
├── 📄 categories.twig     # Category listing
├── 📄 create-category.twig # Category creation form
└── 📄 revision.twig       # Specific revision display
```

### **Template Inheritance**
```twig
{# Base template #}
{% extends "layouts/app.twig" %}

{# Page-specific blocks #}
{% block title %}{{ page.title }} - Wiki - IslamWiki{% endblock %}

{% block content %}
    {# Page content here #}
{% endblock %}

{% block styles %}
    {# Page-specific styles #}
    <link rel="stylesheet" href="/extensions/WikiExtension/assets/css/wiki.css">
{% endblock %}

{% block scripts %}
    {# Page-specific scripts #}
    <script src="/extensions/WikiExtension/assets/js/wiki.js"></script>
{% endblock %}
```

### **Template Variables**
Common variables available in templates:
- `page`: Current page data
- `categories`: Available categories
- `tags`: Available tags
- `user`: Current user information
- `search_results`: Search results (if applicable)
- `revisions`: Page revisions (if applicable)

## 🎯 **Customization and Extension**

### **Creating Custom Controllers**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Database\Connection;

class CustomController extends Controller
{
    public function __construct(Connection $db)
    {
        parent::__construct($db);
    }

    public function customAction(): string
    {
        // Custom logic here
        return $this->view('custom-template', [
            'data' => 'Custom data'
        ]);
    }
}
```

### **Adding Custom Models**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Models;

use IslamWiki\Core\Database\Connection;

class CustomModel
{
    private Connection $db;
    private string $table = 'custom_table';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getCustomData(): array
    {
        $query = "SELECT * FROM {$this->table}";
        return $this->db->query($query)->fetchAll();
    }
}
```

### **Custom Templates**
```twig
{# custom-template.twig #}
{% extends "layouts/app.twig" %}

{% block title %}Custom Page - Wiki{% endblock %}

{% block content %}
<div class="wiki-container">
    <h1>Custom Content</h1>
    <p>{{ data }}</p>
    
    {# Custom template logic here #}
</div>
{% endblock %}
```

### **Adding Custom Routes**
```php
// In config/routes.php
$app->get('/wiki/custom', [$customController, 'customAction']);
$app->post('/wiki/custom', [$customController, 'customPostAction']);
```

## 🔒 **Security and Permissions**

### **Authentication Middleware**
```php
// Apply authentication middleware to protected routes
$app->get('/wiki/create', [$pageController, 'create'])
    ->middleware($authMiddleware);

$app->post('/wiki/create', [$pageController, 'store'])
    ->middleware($authMiddleware);
```

### **Permission Checking**
```php
public function edit(string $slug): string
{
    $page = $this->wikiPageModel->getBySlug($slug);
    
    if (!$page) {
        return $this->renderNotFound('Page not found');
    }
    
    // Check if user can edit this page
    if (!$this->canEditPage($page)) {
        return $this->renderForbidden('You do not have permission to edit this page');
    }
    
    return $this->view('edit', ['page' => $page]);
}

private function canEditPage(array $page): bool
{
    // Check user permissions
    if (!$this->user) {
        return false;
    }
    
    // Check if page is locked
    if ($page['is_locked']) {
        return false;
    }
    
    // Check user role permissions
    return $this->user->hasPermission('wiki.edit');
}
```

### **Input Validation**
```php
public function store(Request $request): Response
{
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string|min:10',
        'meta_description' => 'nullable|string|max:500',
        'category_id' => 'nullable|integer|exists:wiki_categories,id',
        'content_type' => 'required|in:page,article,guide,reference',
        'tags' => 'nullable|string|max:255'
    ]);
    
    // Sanitize content
    $data['content'] = $this->sanitizeContent($data['content']);
    
    // Create page
    $pageId = $this->wikiPageModel->create($data);
    
    return redirect("/wiki/{$pageId}");
}
```

## 🧪 **Testing and Quality Assurance**

### **Unit Testing**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Tests\WikiExtension\Unit;

use PHPUnit\Framework\TestCase;
use IslamWiki\Extensions\WikiExtension\Models\WikiPage;

class WikiPageTest extends TestCase
{
    public function testGetBySlugReturnsPageData(): void
    {
        // Test implementation
        $this->assertTrue(true);
    }
}
```

### **Integration Testing**
```php
public function testPageCreationWorkflow(): void
{
    // Test complete page creation workflow
    $response = $this->post('/wiki/create', [
        'title' => 'Test Page',
        'content' => 'Test content',
        'content_type' => 'page'
    ]);
    
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertDatabaseHas('wiki_pages', [
        'title' => 'Test Page'
    ]);
}
```

### **Performance Testing**
```php
public function testSearchPerformance(): void
{
    $startTime = microtime(true);
    
    // Perform search operation
    $results = $this->searchService->search('islamic mathematics');
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    // Assert performance requirements
    $this->assertLessThan(0.5, $executionTime, 'Search should complete within 500ms');
    $this->assertNotEmpty($results);
}
```

## 📊 **Performance Optimization**

### **Database Optimization**
```sql
-- Add indexes for common queries
CREATE INDEX idx_wiki_pages_status_published ON wiki_pages(status, published_at);
CREATE INDEX idx_wiki_pages_featured_status ON wiki_pages(is_featured, status);
CREATE INDEX idx_wiki_pages_creator_created ON wiki_pages(creator_id, created_at);

-- Optimize search queries
CREATE FULLTEXT INDEX idx_wiki_pages_content_search ON wiki_pages(title, content, meta_description);
```

### **Caching Strategy**
```php
public function getFeaturedPages(int $limit = 6): array
{
    $cacheKey = "wiki:featured_pages:{$limit}";
    
    return $this->cache->remember($cacheKey, 3600, function () use ($limit) {
        return $this->wikiPageModel->getFeaturedPages($limit);
    });
}
```

### **Asset Optimization**
```php
// Combine and minify CSS/JS files
public function optimizeAssets(): void
{
    $cssFiles = [
        'extensions/WikiExtension/assets/css/wiki.css',
        'extensions/WikiExtension/assets/css/components.css'
    ];
    
    $this->assetManager->combineAndMinify($cssFiles, 'wiki.min.css');
}
```

## 🚀 **Deployment and Operations**

### **Production Deployment**
```bash
# Deploy to production
git checkout main
git pull origin main
composer install --no-dev --optimize-autoloader
php database/migrate_wiki_tables.php
php database/seed_wiki_data.php

# Clear caches
php artisan cache:clear
php artisan view:clear

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 public/extensions/
```

### **Environment Configuration**
```env
# Wiki Extension Configuration
WIKI_ENABLE_SEARCH=true
WIKI_SEARCH_INDEX_SIZE=1000
WIKI_MAX_PAGE_SIZE=1048576
WIKI_ENABLE_REVISIONS=true
WIKI_MAX_REVISIONS=100
WIKI_ENABLE_ANALYTICS=true
WIKI_CACHE_TTL=3600
```

### **Monitoring and Logging**
```php
// Add logging to important operations
public function createPage(array $data): int
{
    try {
        $pageId = $this->wikiPageModel->create($data);
        
        $this->logger->info('Wiki page created', [
            'page_id' => $pageId,
            'title' => $data['title'],
            'creator_id' => $this->user->id
        ]);
        
        return $pageId;
    } catch (Exception $e) {
        $this->logger->error('Failed to create wiki page', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);
        
        throw $e;
    }
}
```

## 🔮 **Future Enhancements**

### **Planned Features**
- **Advanced Search**: Elasticsearch integration
- **Real-time Collaboration**: Live editing with conflict resolution
- **Media Management**: Advanced image and file handling
- **API Versioning**: Multiple API versions for backward compatibility
- **Mobile App**: Native mobile applications

### **Architecture Improvements**
- **Microservices**: Break down into smaller, focused services
- **Event Sourcing**: Complete audit trail of all changes
- **GraphQL**: Modern API query language
- **WebSockets**: Real-time communication
- **CDN Integration**: Global content delivery

## 📚 **Additional Resources**

### **Related Documentation**
- **[User Guide](wiki-user-guide.md)** - End-user documentation
- **[API Reference](wiki-api-reference.md)** - Complete API documentation
- **[Style Guide](wiki-style-guide.md)** - Code and content standards
- **[Administration Guide](wiki-admin-guide.md)** - System administration

### **External Resources**
- **Twig Documentation**: Template engine reference
- **PHP Documentation**: Language reference and best practices
- **MySQL Documentation**: Database optimization and administration
- **IslamWiki Core**: Core framework documentation

---

**You're now ready to develop with the IslamWiki WikiExtension!** 🚀

This guide covers all the essential development concepts, from basic setup to advanced customization. Remember to:
- Follow coding standards and best practices
- Write comprehensive tests for your code
- Document your changes and additions
- Contribute to the community

**Happy coding!** 💻✨

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.2.1  
**Status:** Complete Developer Guide ✅  
**Next:** API Reference and Style Guide 📋 