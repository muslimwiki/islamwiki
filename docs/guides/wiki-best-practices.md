# Wiki Best Practices Guide - IslamWiki

**Version:** 0.0.2.1  
**Last Updated:** 2025-01-20  
**Status:** Complete Best Practices Guide ✅  

## 🎯 **Overview**

This best practices guide provides comprehensive recommendations for developing, managing, and optimizing the IslamWiki WikiExtension system. It covers development practices, content management, performance optimization, security, and user experience.

## 💻 **Development Best Practices**

### **Code Quality Standards**

#### **PHP Coding Standards**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Extensions\WikiExtension\Models\WikiPage;
use IslamWiki\Extensions\WikiExtension\Models\WikiCategory;

/**
 * Wiki Controller
 * 
 * Handles wiki page operations including display, creation, editing, and deletion.
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author Your Name <your.email@example.com>
 * @version 0.0.2.1
 */
class WikiController extends Controller
{
    private WikiPage $wikiPageModel;
    private WikiCategory $wikiCategoryModel;

    public function __construct(WikiPage $wikiPageModel, WikiCategory $wikiCategoryModel)
    {
        $this->wikiPageModel = $wikiPageModel;
        $this->wikiCategoryModel = $wikiCategoryModel;
    }

    /**
     * Display wiki homepage
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $featuredPages = $this->wikiPageModel->getFeaturedPages();
            $recentPages = $this->wikiPageModel->getRecentPages();
            $categories = $this->wikiCategoryModel->getAll();

            return $this->getView('wiki.index', [
                'featuredPages' => $featuredPages,
                'recentPages' => $recentPages,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Wiki homepage error: ' . $e->getMessage());
            return $this->renderError('Unable to load wiki homepage');
        }
    }
}
```

#### **JavaScript Best Practices**
```javascript
/**
 * Wiki JavaScript Module
 * Handles interactive wiki functionality
 */
class WikiManager {
    constructor() {
        this.initializeEventListeners();
        this.setupAutoSave();
        this.initializeSearch();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Use event delegation for dynamic content
        document.addEventListener('click', (e) => {
            if (e.target.matches('.wiki-edit-btn')) {
                this.handleEditClick(e);
            }
        });

        // Form submission handling
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.wiki-form')) {
                this.handleFormSubmit(e);
            }
        });
    }

    /**
     * Handle edit button clicks
     */
    handleEditClick(event) {
        event.preventDefault();
        const pageId = event.target.dataset.pageId;
        this.loadEditForm(pageId);
    }

    /**
     * Load edit form
     */
    async loadEditForm(pageId) {
        try {
            const response = await fetch(`/api/wiki/pages/${pageId}/edit`);
            const data = await response.json();
            
            if (data.success) {
                this.displayEditForm(data.data);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error loading edit form:', error);
            this.showError('Failed to load edit form');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new WikiManager();
});
```

#### **CSS Best Practices**
```css
/* Use CSS custom properties for consistent theming */
:root {
    --wiki-primary-color: #007cba;
    --wiki-secondary-color: #6c757d;
    --wiki-success-color: #28a745;
    --wiki-danger-color: #dc3545;
    --wiki-warning-color: #ffc107;
    --wiki-info-color: #17a2b8;
    
    --wiki-font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --wiki-font-size-base: 16px;
    --wiki-line-height-base: 1.5;
    
    --wiki-border-radius: 4px;
    --wiki-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    --wiki-transition: all 0.3s ease;
}

/* Use BEM methodology for CSS classes */
.wiki-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.wiki-container__header {
    background: var(--wiki-primary-color);
    color: white;
    padding: 20px 0;
}

.wiki-container__content {
    padding: 40px 0;
}

/* Responsive design with mobile-first approach */
.wiki-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 768px) {
    .wiki-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .wiki-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Accessibility improvements */
.wiki-btn:focus {
    outline: 2px solid var(--wiki-primary-color);
    outline-offset: 2px;
}

.wiki-btn:focus:not(:focus-visible) {
    outline: none;
}

/* Print styles */
@media print {
    .wiki-navigation,
    .wiki-sidebar,
    .wiki-actions {
        display: none;
    }
    
    .wiki-content {
        font-size: 12pt;
        line-height: 1.4;
    }
}
```

### **Database Design Best Practices**

#### **Table Structure**
```sql
-- Use consistent naming conventions
-- Prefix all wiki tables with 'wiki_'
-- Use snake_case for column names
-- Use descriptive names for tables and columns

CREATE TABLE wiki_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Page title',
    slug VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL slug',
    content TEXT NOT NULL COMMENT 'Page content in Markdown',
    meta_description TEXT NULL COMMENT 'SEO meta description',
    content_type ENUM('page', 'article', 'guide', 'reference') DEFAULT 'page',
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Featured page flag',
    is_locked BOOLEAN DEFAULT FALSE COMMENT 'Page protection flag',
    view_count INT DEFAULT 0 COMMENT 'Page view counter',
    revision_count INT DEFAULT 1 COMMENT 'Revision counter',
    creator_id BIGINT UNSIGNED NULL COMMENT 'User who created the page',
    last_editor_id BIGINT UNSIGNED NULL COMMENT 'User who last edited',
    published_at TIMESTAMP NULL COMMENT 'Publication timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_title (title),
    INDEX idx_slug (slug),
    INDEX idx_status_published (status, published_at),
    INDEX idx_content_type_status (content_type, status),
    INDEX idx_featured_status (is_featured, status),
    INDEX idx_creator_created (creator_id, created_at),
    INDEX idx_editor_updated (last_editor_id, updated_at),
    
    -- Foreign key constraints
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (last_editor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Wiki pages table';
```

#### **Query Optimization**
```php
// Use prepared statements for security
public function getBySlug(string $slug): ?array
{
    $stmt = $this->connection->prepare("
        SELECT p.*, 
               c.name as category_name,
               c.slug as category_slug,
               u.username as creator_username,
               u.display_name as creator_display_name
        FROM wiki_pages p
        LEFT JOIN wiki_categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.creator_id = u.id
        WHERE p.slug = ? AND p.status = 'published'
    ");
    
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// Use pagination for large result sets
public function getRecentPages(int $limit = 10, int $offset = 0): array
{
    $stmt = $this->connection->prepare("
        SELECT p.*, c.name as category_name
        FROM wiki_pages p
        LEFT JOIN wiki_categories c ON p.category_id = c.id
        WHERE p.status = 'published'
        ORDER BY p.updated_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

// Use transactions for data integrity
public function create(array $data): int
{
    try {
        $this->connection->beginTransaction();
        
        // Insert page
        $stmt = $this->connection->prepare("
            INSERT INTO wiki_pages (title, slug, content, meta_description, 
                                  category_id, content_type, creator_id, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['meta_description'] ?? null,
            $data['category_id'] ?? null,
            $data['content_type'] ?? 'page',
            $data['creator_id']
        ]);
        
        $pageId = $this->connection->lastInsertId();
        
        // Create initial revision
        $this->createRevision($pageId, $data['content']);
        
        $this->connection->commit();
        return $pageId;
        
    } catch (\Exception $e) {
        $this->connection->rollBack();
        throw $e;
    }
}
```

### **Security Best Practices**

#### **Input Validation**
```php
/**
 * Validate wiki page data
 */
public function validatePageData(array $data): array
{
    $rules = [
        'title' => [
            'required' => true,
            'min_length' => 3,
            'max_length' => 255,
            'pattern' => '/^[a-zA-Z0-9\s\-_.,!?()]+$/'
        ],
        'content' => [
            'required' => true,
            'min_length' => 10,
            'max_length' => 50000,
            'allowed_tags' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'strong', 'em', 'code', 'pre', 'blockquote', 'a', 'img', 'table', 'tr', 'td', 'th']
        ],
        'meta_description' => [
            'required' => false,
            'max_length' => 500
        ],
        'category_id' => [
            'required' => false,
            'type' => 'integer',
            'exists' => 'wiki_categories,id'
        ],
        'tags' => [
            'required' => false,
            'type' => 'string',
            'max_length' => 500
        ]
    ];
    
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        if ($rule['required'] && empty($data[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
            continue;
        }
        
        if (!empty($data[$field])) {
            $value = $data[$field];
            
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
            }
            
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = ucfirst($field) . ' must not exceed ' . $rule['max_length'] . ' characters';
            }
            
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = ucfirst($field) . ' contains invalid characters';
            }
            
            if (isset($rule['type']) && $rule['type'] === 'integer' && !is_numeric($value)) {
                $errors[$field] = ucfirst($field) . ' must be a number';
            }
        }
    }
    
    return $errors;
}
```

#### **Output Escaping**
```php
/**
 * Sanitize content for safe display
 */
public function sanitizeContent(string $content): string
{
    // Remove potentially dangerous HTML
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><code><pre><blockquote><a><img><table><tr><td><th>';
    
    $content = strip_tags($content, $allowedTags);
    
    // Sanitize attributes
    $content = preg_replace('/<([^>]+) on\w+\s*=\s*["\'][^"\']*["\']/i', '<$1', $content);
    $content = preg_replace('/<([^>]+) javascript:/i', '<$1', $content);
    
    // Sanitize links
    $content = preg_replace('/<a([^>]+) href\s*=\s*["\']javascript:/i', '<a$1 href="#"', $content);
    
    return $content;
}
```

#### **Authentication and Authorization**
```php
/**
 * Check user permissions for wiki operations
 */
public function checkPermission(string $operation, ?int $userId = null): bool
{
    if (!$userId) {
        $userId = $this->getCurrentUserId();
    }
    
    if (!$userId) {
        return false;
    }
    
    $user = $this->userModel->getById($userId);
    if (!$user) {
        return false;
    }
    
    $permissions = [
        'wiki.read' => [1, 2, 3, 4, 5], // All users
        'wiki.create' => [2, 3, 4, 5],   // Contributors and above
        'wiki.edit' => [2, 3, 4, 5],     // Contributors and above
        'wiki.delete' => [4, 5],          // Administrators only
        'wiki.moderate' => [3, 4, 5],    // Moderators and above
        'wiki.admin' => [5]               // Super administrators only
    ];
    
    if (!isset($permissions[$operation])) {
        return false;
    }
    
    return in_array($user['role_level'], $permissions[$operation]);
}

/**
 * Require permission or throw exception
 */
public function requirePermission(string $operation, ?int $userId = null): void
{
    if (!$this->checkPermission($operation, $userId)) {
        throw new ForbiddenException('Insufficient permissions for operation: ' . $operation);
    }
}
```

## 📚 **Content Management Best Practices**

### **Content Creation Guidelines**

#### **Page Structure Standards**
```markdown
# Page Title

## Overview
Brief introduction and summary of the topic (2-3 sentences).

## Main Content
### Section 1
Content for the first main section with clear explanations.

### Section 2
Content for the second main section with examples.

## Key Points
- Important point 1 with brief explanation
- Important point 2 with brief explanation
- Important point 3 with brief explanation

## Examples
Provide practical examples to illustrate concepts.

## Related Topics
- [Related Page 1](wiki-page-1)
- [Related Page 2](wiki-page-2)
- [Related Category](wiki-categories/category-name)

## References
1. **Author Name**, *Book Title* (Publisher, Year)
2. **Author Name**, "Article Title," *Journal Name* (Year)
3. **Website Name**, "Page Title," URL (Accessed: Date)
```

#### **Content Quality Standards**
- **Accuracy**: Ensure all information is factually correct
- **Completeness**: Cover topics comprehensively
- **Clarity**: Use clear, understandable language
- **Relevance**: Ensure content is relevant to the wiki's purpose
- **Currency**: Keep content up-to-date
- **Consistency**: Maintain consistent formatting and style

#### **Content Review Process**
1. **Self-Review**: Author reviews their own content
2. **Peer Review**: Another contributor reviews the content
3. **Moderator Review**: Moderator reviews for quality and compliance
4. **Publication**: Content is published after approval
5. **Ongoing Review**: Regular review and updates

### **Category and Tag Management**

#### **Category Organization Principles**
```
Islamic History
├── Early Islamic Period (7th-8th centuries)
│   ├── Prophet Muhammad Era (570-632 CE)
│   ├── Rashidun Caliphate (632-661 CE)
│   └── Umayyad Dynasty (661-750 CE)
├── Golden Age (8th-14th centuries)
│   ├── Abbasid Caliphate (750-1258 CE)
│   ├── Islamic Sciences
│   └── Cultural Achievements
└── Modern Era (15th century-present)
    ├── Colonial Period
    ├── Independence Movements
    └── Contemporary Issues
```

#### **Tagging Best Practices**
- **Use Descriptive Tags**: Tags should clearly describe content
- **Maintain Consistency**: Use consistent naming conventions
- **Avoid Over-tagging**: Use 3-8 relevant tags per page
- **Regular Maintenance**: Clean up unused or duplicate tags
- **User Education**: Teach users proper tagging practices

## 🚀 **Performance Optimization Best Practices**

### **Database Optimization**

#### **Indexing Strategy**
```sql
-- Primary indexes for common queries
CREATE INDEX idx_wiki_pages_status_published ON wiki_pages(status, published_at);
CREATE INDEX idx_wiki_pages_category_status ON wiki_pages(category_id, status);
CREATE INDEX idx_wiki_pages_creator_created ON wiki_pages(creator_id, created_at);

-- Full-text search indexes
CREATE FULLTEXT INDEX idx_wiki_pages_search ON wiki_pages(title, content, meta_description);
CREATE FULLTEXT INDEX idx_wiki_categories_search ON wiki_categories(name, description);

-- Composite indexes for complex queries
CREATE INDEX idx_wiki_pages_complex ON wiki_pages(status, is_featured, published_at, category_id);
```

#### **Query Optimization**
```php
// Use efficient queries
public function getFeaturedPages(int $limit = 10): array
{
    // Use single query with JOIN instead of multiple queries
    $stmt = $this->connection->prepare("
        SELECT p.*, 
               c.name as category_name,
               c.slug as category_slug,
               c.color as category_color,
               u.username as creator_username,
               u.display_name as creator_display_name,
               COUNT(r.id) as revision_count
        FROM wiki_pages p
        LEFT JOIN wiki_categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.creator_id = u.id
        LEFT JOIN wiki_revisions r ON p.id = r.page_id
        WHERE p.status = 'published' 
          AND p.is_featured = 1
        GROUP BY p.id
        ORDER BY p.published_at DESC
        LIMIT ?
    ");
    
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Use pagination for large result sets
public function searchPages(string $query, int $page = 1, int $perPage = 20): array
{
    $offset = ($page - 1) * $perPage;
    
    $stmt = $this->connection->prepare("
        SELECT p.*, c.name as category_name
        FROM wiki_pages p
        LEFT JOIN wiki_categories c ON p.category_id = c.id
        WHERE p.status = 'published'
          AND MATCH(p.title, p.content, p.meta_description) AGAINST(? IN BOOLEAN MODE)
        ORDER BY MATCH(p.title, p.content, p.meta_description) AGAINST(?) DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$query, $query, $perPage, $offset]);
    return $stmt->fetchAll();
}
```

### **Caching Strategy**

#### **Multi-Level Caching**
```php
/**
 * Implement multi-level caching strategy
 */
class WikiCacheManager
{
    private $redis;
    private $fileCache;
    private $memoryCache;
    
    public function getPage(string $slug): ?array
    {
        // Level 1: Memory cache (fastest)
        $page = $this->memoryCache->get("page:{$slug}");
        if ($page) {
            return $page;
        }
        
        // Level 2: Redis cache (fast)
        $page = $this->redis->get("page:{$slug}");
        if ($page) {
            $this->memoryCache->set("page:{$slug}", $page, 300);
            return $page;
        }
        
        // Level 3: File cache (slower)
        $page = $this->fileCache->get("page:{$slug}");
        if ($page) {
            $this->redis->setex("page:{$slug}", 3600, $page);
            $this->memoryCache->set("page:{$slug}", $page, 300);
            return $page;
        }
        
        // Level 4: Database (slowest)
        $page = $this->loadFromDatabase($slug);
        if ($page) {
            $this->cachePage($slug, $page);
        }
        
        return $page;
    }
    
    private function cachePage(string $slug, array $page): void
    {
        $this->fileCache->set("page:{$slug}", $page, 86400);    // 24 hours
        $this->redis->setex("page:{$slug}", 3600, $page);       // 1 hour
        $this->memoryCache->set("page:{$slug}", $page, 300);    // 5 minutes
    }
}
```

#### **Cache Invalidation**
```php
/**
 * Smart cache invalidation
 */
public function invalidatePageCache(string $slug): void
{
    // Invalidate all cache levels
    $this->memoryCache->delete("page:{$slug}");
    $this->redis->del("page:{$slug}");
    $this->fileCache->delete("page:{$slug}");
    
    // Invalidate related caches
    $this->invalidateCategoryCache($slug);
    $this->invalidateSearchCache();
    $this->invalidateHomepageCache();
}

public function invalidateCategoryCache(string $slug): void
{
    $page = $this->getPage($slug);
    if ($page && $page['category_id']) {
        $categorySlug = $this->getCategorySlug($page['category_id']);
        $this->redis->del("category:{$categorySlug}");
        $this->fileCache->delete("category:{$categorySlug}");
    }
}
```

### **Asset Optimization**

#### **CSS and JavaScript Optimization**
```bash
# Build optimized assets
npm run build

# Minify CSS
npm run css:minify

# Minify JavaScript
npm run js:minify

# Generate source maps for debugging
npm run build:dev
```

#### **Image Optimization**
```php
/**
 * Image optimization service
 */
class ImageOptimizer
{
    public function optimizeImage(string $imagePath): void
    {
        $imageInfo = getimagesize($imagePath);
        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                $this->optimizeJpeg($imagePath);
                break;
            case 'image/png':
                $this->optimizePng($imagePath);
                break;
            case 'image/gif':
                $this->optimizeGif($imagePath);
                break;
        }
    }
    
    private function optimizeJpeg(string $imagePath): void
    {
        $image = imagecreatefromjpeg($imagePath);
        
        // Resize if too large
        $maxWidth = 1200;
        $maxHeight = 800;
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = $width * $ratio;
            $newHeight = $height * $ratio;
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            imagejpeg($resized, $imagePath, 85);
            imagedestroy($resized);
        }
        
        imagedestroy($image);
    }
}
```

## 🛡️ **Security Best Practices**

### **Authentication Security**

#### **Password Security**
```php
/**
 * Secure password handling
 */
class PasswordManager
{
    private const HASH_ALGO = PASSWORD_ARGON2ID;
    private const HASH_OPTIONS = [
        'memory_cost' => 65536,    // 64MB
        'time_cost' => 4,          // 4 iterations
        'threads' => 3             // 3 threads
    ];
    
    public function hashPassword(string $password): string
    {
        return password_hash($password, self::HASH_ALGO, self::HASH_OPTIONS);
    }
    
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::HASH_ALGO, self::HASH_OPTIONS);
    }
    
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
}
```

#### **Session Security**
```php
/**
 * Secure session management
 */
class Session
{
    public function __construct()
    {
        $this->configureSession();
    }
    
    private function configureSession(): void
    {
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', 3600);
        ini_set('session.cookie_lifetime', 3600);
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    public function createSecureSession(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['created_at'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    public function validateSession(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session age
        if (time() - $_SESSION['created_at'] > 3600) {
            $this->destroySession();
            return false;
        }
        
        // Check IP address
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->destroySession();
            return false;
        }
        
        // Check user agent
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroySession();
            return false;
        }
        
        return true;
    }
}
```

### **Content Security**

#### **XSS Prevention**
```php
/**
 * XSS protection service
 */
class XSSProtection
{
    private array $allowedTags = [
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'strong', 'em', 'code', 'pre',
        'blockquote', 'a', 'img', 'table', 'tr', 'td', 'th'
    ];
    
    private array $allowedAttributes = [
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'table' => ['border', 'cellpadding', 'cellspacing'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan']
    ];
    
    public function sanitizeContent(string $content): string
    {
        // Remove all HTML tags first
        $content = strip_tags($content);
        
        // Allow only specific tags
        $content = strip_tags($content, '<' . implode('><', $this->allowedTags) . '>');
        
        // Sanitize attributes
        $content = $this->sanitizeAttributes($content);
        
        // Remove dangerous content
        $content = $this->removeDangerousContent($content);
        
        return $content;
    }
    
    private function sanitizeAttributes(string $content): string
    {
        foreach ($this->allowedAttributes as $tag => $attributes) {
            $pattern = "/<{$tag}([^>]*)>/i";
            $content = preg_replace_callback($pattern, function($matches) use ($attributes) {
                $tagContent = $matches[1];
                $cleanAttributes = [];
                
                // Extract and validate attributes
                preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $tagContent, $attrMatches, PREG_SET_ORDER);
                
                foreach ($attrMatches as $attrMatch) {
                    $attrName = strtolower($attrMatch[1]);
                    $attrValue = $attrMatch[2];
                    
                    if (in_array($attrName, $attributes)) {
                        // Validate specific attributes
                        if ($attrName === 'href') {
                            $attrValue = $this->validateUrl($attrValue);
                        } elseif ($attrName === 'src') {
                            $attrValue = $this->validateUrl($attrValue);
                        }
                        
                        if ($attrValue !== false) {
                            $cleanAttributes[] = "{$attrName}=\"{$attrValue}\"";
                        }
                    }
                }
                
                return "<{$tag} " . implode(' ', $cleanAttributes) . ">";
            }, $content);
        }
        
        return $content;
    }
    
    private function validateUrl(string $url): string|false
    {
        // Allow relative URLs
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }
        
        // Validate external URLs
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (in_array($scheme, ['http', 'https'])) {
                return $url;
            }
        }
        
        return false;
    }
}
```

## 📱 **User Experience Best Practices**

### **Responsive Design**

#### **Mobile-First Approach**
```css
/* Base styles for mobile */
.wiki-container {
    padding: 10px;
}

.wiki-navigation {
    flex-direction: column;
}

.wiki-content {
    font-size: 16px;
    line-height: 1.6;
}

/* Tablet styles */
@media (min-width: 768px) {
    .wiki-container {
        padding: 20px;
    }
    
    .wiki-navigation {
        flex-direction: row;
    }
    
    .wiki-content {
        font-size: 18px;
    }
}

/* Desktop styles */
@media (min-width: 1024px) {
    .wiki-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px;
    }
    
    .wiki-content {
        font-size: 20px;
    }
}
```

#### **Touch-Friendly Interface**
```css
/* Touch-friendly buttons */
.wiki-btn {
    min-height: 44px;
    min-width: 44px;
    padding: 12px 20px;
    border-radius: 8px;
    touch-action: manipulation;
}

/* Touch-friendly forms */
.wiki-form-input {
    min-height: 44px;
    font-size: 16px; /* Prevents zoom on iOS */
    padding: 12px;
}

/* Touch-friendly navigation */
.wiki-nav-item {
    padding: 12px 16px;
    margin: 4px 0;
}
```

### **Accessibility**

#### **WCAG Compliance**
```html
<!-- Semantic HTML structure -->
<main role="main" aria-label="Wiki content">
    <article>
        <header>
            <h1 id="page-title">Page Title</h1>
            <nav aria-label="Page navigation">
                <ul>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#content">Content</a></li>
                    <li><a href="#references">References</a></li>
                </ul>
            </nav>
        </header>
        
        <section id="overview" aria-labelledby="overview-heading">
            <h2 id="overview-heading">Overview</h2>
            <p>Content here...</p>
        </section>
    </article>
</main>
```

#### **Screen Reader Support**
```css
/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Focus indicators */
.wiki-btn:focus,
.wiki-link:focus {
    outline: 2px solid #007cba;
    outline-offset: 2px;
}

/* Skip links */
.skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: #007cba;
    color: white;
    padding: 8px;
    text-decoration: none;
    z-index: 1000;
}

.skip-link:focus {
    top: 6px;
}
```

## 🔄 **Maintenance Best Practices**

### **Regular Maintenance Tasks**

#### **Daily Tasks**
```bash
#!/bin/bash
# Daily maintenance script

echo "Starting daily maintenance..."

# Check system health
php artisan wiki:health-check

# Clean up old sessions
php artisan session:gc

# Process email queue
php artisan queue:work --once

# Check disk space
df -h | grep -E '^/dev/'

# Check log file sizes
du -sh /var/log/wiki/*

echo "Daily maintenance completed"
```

#### **Weekly Tasks**
```bash
#!/bin/bash
# Weekly maintenance script

echo "Starting weekly maintenance..."

# Optimize database
mysql -u username -p database_name -e "OPTIMIZE TABLE wiki_pages, wiki_categories, wiki_revisions;"

# Clean up old log files
find /var/log/wiki/ -name "*.log" -mtime +30 -delete

# Update search index
php artisan wiki:rebuild-index

# Generate weekly report
php artisan wiki:generate-report weekly

echo "Weekly maintenance completed"
```

### **Monitoring and Alerting**

#### **System Monitoring**
```php
/**
 * System health monitoring
 */
class SystemMonitor
{
    public function checkSystemHealth(): array
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'performance' => $this->checkPerformance()
        ];
        
        $overallHealth = !in_array(false, $health);
        
        if (!$overallHealth) {
            $this->sendAlert('System health check failed', $health);
        }
        
        return $health;
    }
    
    private function checkDatabase(): bool
    {
        try {
            $start = microtime(true);
            $this->connection->query('SELECT 1');
            $time = microtime(true) - $start;
            
            return $time < 1.0; // Response time under 1 second
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function checkStorage(): bool
    {
        $diskUsage = disk_free_space('/') / disk_total_space('/');
        return $diskUsage > 0.1; // At least 10% free space
    }
}
```

## 📈 **Performance Monitoring Best Practices**

### **Application Performance Monitoring**

#### **Performance Metrics**
```php
/**
 * Performance monitoring service
 */
class PerformanceMonitor
{
    public function startTimer(string $operation): void
    {
        $this->timers[$operation] = microtime(true);
    }
    
    public function endTimer(string $operation): float
    {
        if (!isset($this->timers[$operation])) {
            return 0.0;
        }
        
        $duration = microtime(true) - $this->timers[$operation];
        unset($this->timers[$operation]);
        
        // Record performance metric
        $this->recordMetric($operation, $duration);
        
        return $duration;
    }
    
    private function recordMetric(string $operation, float $duration): void
    {
        $metrics = [
            'operation' => $operation,
            'duration' => $duration,
            'timestamp' => time(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        // Store in database or send to monitoring service
        $this->storeMetric($metrics);
    }
}
```

#### **Performance Thresholds**
```php
/**
 * Performance threshold monitoring
 */
class PerformanceThresholds
{
    private const THRESHOLDS = [
        'page_load' => 2.0,      // 2 seconds
        'search_query' => 1.0,   // 1 second
        'database_query' => 0.5, // 0.5 seconds
        'file_upload' => 5.0     // 5 seconds
    ];
    
    public function checkThreshold(string $operation, float $duration): bool
    {
        $threshold = self::THRESHOLDS[$operation] ?? 10.0;
        
        if ($duration > $threshold) {
            $this->logSlowOperation($operation, $duration, $threshold);
            return false;
        }
        
        return true;
    }
    
    private function logSlowOperation(string $operation, float $duration, float $threshold): void
    {
        $message = sprintf(
            'Slow operation detected: %s took %.3f seconds (threshold: %.3f)',
            $operation,
            $duration,
            $threshold
        );
        
        \Log::warning($message);
    }
}
```

---

**You're now equipped with comprehensive best practices for wiki development!** 🚀

This best practices guide covers:
- ✅ **Development Standards**: Code quality, database design, and security
- ✅ **Content Management**: Creation guidelines and quality standards
- ✅ **Performance Optimization**: Database, caching, and asset optimization
- ✅ **Security Practices**: Authentication, authorization, and content security
- ✅ **User Experience**: Responsive design and accessibility
- ✅ **Maintenance**: Regular tasks and monitoring
- ✅ **Performance Monitoring**: Metrics and threshold monitoring

**Happy developing!** 💻✨

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.2.1  
**Status:** Complete Best Practices Guide ✅  
**Next:** Case Studies and Advanced Topics 📋 