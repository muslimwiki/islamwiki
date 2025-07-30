# Wiki Page System

**Version:** 0.2.1

The Wiki Page System is the core functionality of IslamWiki, providing complete page creation, viewing, editing, and management capabilities.

## Overview

The wiki page system provides:
- **Page Model**: Eloquent-like model with relationships and revision tracking
- **Page Controller**: Full CRUD operations with proper template rendering
- **Content Rendering**: Basic wiki text parsing and HTML conversion
- **View Count Tracking**: Analytics with database updates and user tracking
- **Page Permissions**: Edit, delete, and lock permissions based on user roles
- **Page History**: Revision tracking and history viewing functionality

## Page Model

The `Page` model provides an Eloquent-like interface for database interactions:

```php
use IslamWiki\Models\Page;

// Find page by slug
$page = Page::findBySlug('welcome', $connection);

// Create new page
$page = new Page($connection, [
    'title' => 'My Page',
    'slug' => 'my-page',
    'content' => '# My Page\n\nContent here...',
    'content_format' => 'markdown',
    'namespace' => 'main'
]);

// Save page
$page->save();

// Check if page is locked
if ($page->isLocked()) {
    // Handle locked page
}

// Get page URL
$url = $page->getUrl(); // Returns /wiki/my-page
```

### Model Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | int | Primary key |
| `title` | string | Page title |
| `slug` | string | URL-friendly identifier |
| `content` | text | Page content |
| `content_format` | string | Content format (markdown, html) |
| `namespace` | string | Page namespace |
| `is_locked` | boolean | Whether page is locked |
| `view_count` | int | Number of page views |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Last update timestamp |

## Page Controller

The `PageController` handles all page-related operations:

### Show Page
```php
// GET /{slug}
public function show(Request $request, string $slug): Response
```

Displays a page with:
- Content rendering
- View count tracking
- Permission checks
- Revision information

### Create Page
```php
// GET /pages/create
public function create(Request $request): Response
```

Shows the page creation form with:
- Title and content fields
- Namespace selection
- Permission validation

### Store Page
```php
// POST /pages
public function store(Request $request): Response
```

Creates a new page with:
- Input validation
- Slug generation
- Permission checks
- Revision creation

### Edit Page
```php
// GET /{slug}/edit
public function edit(Request $request, string $slug): Response
```

Shows the edit form with:
- Pre-filled content
- Permission validation
- Lock status checking

### Update Page
```php
// PUT /{slug}
public function update(Request $request, string $slug): Response
```

Updates an existing page with:
- Input validation
- Permission checks
- Revision creation
- View count preservation

## Content Rendering

The system includes comprehensive markdown parsing with syntax highlighting:

```php
protected function parseWikiText(string $text): string
{
    // First, escape any existing HTML to prevent XSS
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Process code blocks first (before other markdown)
    $text = $this->parseCodeBlocks($text);
    
    // Process headers
    $text = $this->parseHeaders($text);
    
    // Process emphasis (bold and italic)
    $text = $this->parseEmphasis($text);
    
    // Process links
    $text = $this->parseLinks($text);
    
    // Process lists
    $text = $this->parseLists($text);
    
    // Process blockquotes
    $text = $this->parseBlockquotes($text);
    
    // Process horizontal rules
    $text = $this->parseHorizontalRules($text);
    
    // Convert line breaks to <br> tags
    $text = nl2br($text);
    
    return $text;
}
```

### Supported Markdown Features

- **Headers**: `# H1`, `## H2`, `### H3`
- **Emphasis**: `**bold**`, `*italic*`
- **Code**: `` `inline code` ``, ````language code blocks````
- **Links**: `[text](url)`, auto-linked URLs
- **Lists**: `- unordered`, `1. ordered`
- **Blockquotes**: `> quoted text`
- **Horizontal Rules**: `---`
- **Syntax Highlighting**: Language-specific code highlighting with Prism.js

## View Count Tracking

Pages automatically track view counts:

```php
// Skip view count for AJAX requests
$skipViewCount = $request->isXmlHttpRequest() || 
                $request->hasHeader('X-PJAX') ||
                $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

if (!$skipViewCount) {
    // Update view count
    $this->db->table('pages')
        ->where('id', '=', $page->getAttribute('id'))
        ->update([
            'view_count' => $newViewCount,
            'last_viewed_at' => date('Y-m-d H:i:s'),
            'last_viewed_by' => $userId,
        ]);
}
```

## Page Permissions

The system includes comprehensive permission checking:

### Can Edit Page
```php
protected function canEditPage(Page $page, Request $request): bool
{
    $user = $this->user($request);
    
    // Admins can edit any page
    if ($this->isAdmin($request)) {
        return true;
    }
    
    // Check if page is locked
    if ($page->isLocked()) {
        return false;
    }
    
    // Authenticated users can edit unlocked pages
    return $user !== null;
}
```

### Can Delete Page
```php
protected function canDeletePage(Page $page, Request $request): bool
{
    // Only admins can delete pages
    return $this->isAdmin($request);
}
```

### Can Lock Page
```php
protected function canLockPage(Request $request): bool
{
    // Only admins can lock pages
    return $this->isAdmin($request);
}
```

## Page Templates

### Show Template (`pages/show.twig`)

```twig
{% extends "layouts/app.twig" %}

{% block title %}{{ page.title }} - IslamWiki{% endblock %}

{% block content %}
<div class="page-header">
    <h1>{{ page.title }}</h1>
    <div class="page-meta">
        <span class="page-namespace">{{ page.namespace }}</span>
        <span class="page-views">{{ page.view_count }} views</span>
        <span class="page-last-edited">Last edited: {{ page.updated_at|date('M j, Y') }}</span>
    </div>
</div>

<div class="page-content">
    {{ content|raw }}
</div>

<div class="page-actions">
    {% if canEdit %}
        <a href="/{{ page.slug }}/edit" class="btn btn-primary">Edit Page</a>
    {% endif %}
    
    {% if canDelete %}
        <a href="/{{ page.slug }}/delete" class="btn btn-danger">Delete Page</a>
    {% endif %}
    
    {% if canLock %}
        {% if page.is_locked %}
            <a href="/{{ page.slug }}/unlock" class="btn btn-warning">Unlock Page</a>
        {% else %}
            <a href="/{{ page.slug }}/lock" class="btn btn-warning">Lock Page</a>
        {% endif %}
    {% endif %}
    
    <a href="/{{ page.slug }}/history" class="btn btn-secondary">View History</a>
</div>
{% endblock %}
```

## Routes

The wiki page system uses the following routes:

```php
// Page listing
$router->get('/pages', 'PageController@index');

// Page creation
$router->get('/pages/create', 'PageController@create');
$router->post('/pages', 'PageController@store');

// Individual page routes
$router->get('/{slug}', 'PageController@show');
$router->get('/{slug}/edit', 'PageController@edit');
$router->put('/{slug}', 'PageController@update');
$router->delete('/{slug}', 'PageController@destroy');

// Page history
$router->get('/{slug}/history', 'PageController@history');

// Page locking
$router->post('/{slug}/lock', 'PageController@lock');
$router->post('/{slug}/unlock', 'PageController@unlock');
```

## Database Schema

### Pages Table
```sql
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT,
    content_format VARCHAR(50) DEFAULT 'markdown',
    namespace VARCHAR(100) DEFAULT 'main',
    is_locked BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    last_viewed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### Page Revisions Table
```sql
CREATE TABLE page_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    content_format VARCHAR(50) DEFAULT 'markdown',
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES pages(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Testing

The wiki page system includes comprehensive testing:

```bash
# Test page model
php scripts/test_page_view.php

# Test page controller
php scripts/test_page_controller_web.php

# Test page routes
php scripts/test_page_route.php
```

## Future Enhancements

### Planned for 0.2.1
- **Enhanced Content Rendering**: Full markdown support with syntax highlighting
- **Rich Text Editor**: WYSIWYG editor for page editing
- **Media Support**: Image and file upload handling
- **Search Functionality**: Full-text search across pages
- **API Endpoints**: RESTful API for external integration

### Planned for 0.3.0
- **Advanced Permissions**: Role-based access control
- **Page Categories**: Tagging and categorization system
- **Page Templates**: Pre-defined page templates
- **Bulk Operations**: Mass page operations for admins
- **Export/Import**: Page backup and restore functionality 