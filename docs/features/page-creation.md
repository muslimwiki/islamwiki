# Page Creation System

## Overview

The page creation system in IslamWiki provides a comprehensive workflow for creating and managing wiki pages with support for Markdown content, namespaces, revision tracking, and proper validation.

## Features

### 📝 Content Creation
- **Markdown Support**: Rich text editing with syntax highlighting
- **Multiple Formats**: Support for Markdown, HTML, and WikiText
- **Edit Summaries**: Track changes with descriptive summaries
- **Namespace Organization**: Organize pages with namespaces

### 🏷️ Namespace Support
- **Help**: Documentation and help pages
- **User**: User-specific pages and profiles
- **Template**: Reusable page templates
- **Category**: Category organization pages
- **Main**: Default namespace for general content

### 📚 Revision Tracking
- **Automatic Revisions**: Every page creation creates an initial revision
- **User Attribution**: Track who created each revision
- **Comment Support**: Add descriptive comments to revisions
- **Timestamp Tracking**: Full audit trail of changes

### 🔗 URL Generation
- **Slug Generation**: Automatic creation of URL-friendly slugs
- **Unicode Support**: Proper handling of international characters
- **Namespace Prefixing**: URLs include namespace when applicable
- **Clean URLs**: Human-readable and SEO-friendly

## Technical Implementation

### Database Schema

#### Pages Table
```sql
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    content_format VARCHAR(20) DEFAULT 'markdown',
    namespace VARCHAR(50) DEFAULT '',
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_namespace_slug (namespace, slug)
);
```

#### Page History Table
```sql
CREATE TABLE page_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NULL,
    INDEX idx_page_created (page_id, created_at)
);
```

### Controller Methods

#### PageController::create()
Displays the page creation form with:
- Title and content fields
- Namespace selection
- Content format options
- Edit summary field
- Validation error display

#### PageController::store()
Processes page creation with:
- Input validation
- Slug generation
- Database insertion
- Revision creation
- Redirect to new page

#### PageController::generateSlug()
Creates URL-friendly slugs:
- Converts to lowercase
- Replaces spaces with hyphens
- Removes special characters
- Handles Unicode properly
- Adds namespace prefix

### Validation Rules

#### Title Validation
- Required field
- Allows most characters except dangerous ones
- Prevents HTML injection
- Supports Unicode characters

#### Namespace Validation
- Optional field
- Alphanumeric characters, hyphens, and underscores only
- Empty namespace is allowed

#### Content Validation
- Required field
- Minimum content length
- Format validation based on selected format

## Usage

### Creating a Page

#### Via Web Interface
1. Navigate to `/pages/create`
2. Fill in the page title
3. Select namespace (optional)
4. Enter page content
5. Choose content format
6. Add edit summary (optional)
7. Submit the form

#### Via API
```bash
curl -X POST https://local.islam.wiki/pages \
  -d "title=My Test Page" \
  -d "content=# My Test Page\n\nThis is a test page." \
  -d "namespace=" \
  -d "comment=Initial page creation" \
  -d "content_format=markdown"
```

### Content Formats

#### Markdown (Recommended)
```markdown
# Page Title

## Section 1
Content here with **bold** and *italic* text.

## Section 2
- List item 1
- List item 2

## Code Example
```php
<?php
echo "Hello World!";
?>
```
```

#### HTML
```html
<h1>Page Title</h1>
<p>Content with <strong>bold</strong> and <em>italic</em> text.</p>
<ul>
    <li>List item 1</li>
    <li>List item 2</li>
</ul>
```

#### WikiText
```
= Page Title =

== Section 1 ==
Content here with '''bold''' and ''italic'' text.

== Section 2 ==
* List item 1
* List item 2
```

## Testing

### Test Scripts
- `public/test-page-creation-form.php` - Main test suite
- `public/debug-page-creation.php` - Database operation debugging
- `public/debug-form-data.php` - Form data transmission testing
- `public/debug-auth-page.php` - Authentication debugging

### Test Coverage
- Form accessibility testing
- Form submission testing
- Database integration testing
- Slug generation testing
- Validation testing

### Example Test Results
```
=== Test Page Creation Form ===

1. Testing page creation form...
   HTTP Status Code: 200
   ✅ Page creation form accessible
   ✅ Form contains title field
   ✅ Form contains content field
   ✅ Form uses POST method

2. Testing form submission...
   HTTP Status Code: 302
   Redirect Location: https://local.islam.wiki/wiki/test-page-2025-08-02-014618
   ✅ Form submission successful (redirected)
   📄 Page should be created at: https://local.islam.wiki/wiki/test-page-2025-08-02-014618

3. Checking database for created page...
   ✅ Page found in database
   📊 Page ID: 8
   📊 Page Title: Test Page 2025-08-02 01:46:18
   📊 Page Slug: test-page-2025-08-02-014618
   📊 Content Length: 195 characters
   ✅ Page revision created
   📊 Revision ID: 6
```

## Configuration

### LocalSettings.php
```php
// Page creation settings
$wgPageCreationEnabled = true;
$wgDefaultContentFormat = 'markdown';
$wgAllowedNamespaces = ['', 'Help', 'User', 'Template', 'Category'];
$wgMaxPageTitleLength = 255;
$wgMaxPageContentLength = 65535;
```

### Environment Variables
```bash
# Page creation settings
PAGE_CREATION_ENABLED=true
DEFAULT_CONTENT_FORMAT=markdown
MAX_TITLE_LENGTH=255
MAX_CONTENT_LENGTH=65535
```

## Troubleshooting

### Common Issues

#### Form Not Loading
- Check if `/pages/create` route is properly configured
- Verify template files exist in `resources/views/pages/`
- Check for PHP errors in logs

#### Form Submission Fails
- Verify database tables exist
- Check database connection
- Review validation error logs
- Ensure CSRF protection is properly configured

#### Page Not Created
- Check database permissions
- Verify slug generation logic
- Review error logs for specific issues
- Test database connection directly

#### Validation Errors
- Review validation rules in PageController
- Check input sanitization
- Verify character encoding
- Test with minimal content

### Debug Commands
```bash
# Test page creation form
php public/test-page-creation-form.php

# Debug database operations
php public/debug-page-creation.php

# Test form data transmission
php public/debug-form-data.php

# Check database tables
mysql -u root -e "SHOW TABLES LIKE 'pages';"
mysql -u root -e "SHOW TABLES LIKE 'page_history';"
```

## Future Enhancements

### Planned Features
- **Page Templates**: Pre-defined templates for common page types
- **Media Upload**: Support for images and files
- **Collaborative Editing**: Real-time editing with multiple users
- **Page Categories**: Advanced categorization system
- **Page Permissions**: Granular access control
- **Page Locking**: Prevent concurrent edits

### Technical Improvements
- **Re-enable Authentication**: When session sharing is fixed
- **Re-enable CSRF Protection**: When forms include proper tokens
- **Re-enable Middleware**: When routing issues are resolved
- **Fix Page Model Integration**: Use proper model instead of direct database calls
- **Add Page Templates**: For common page types
- **Implement Page Categories**: For better organization
- **Add Media Upload Support**: For images and files
- **Implement Collaborative Editing**: With real-time updates

## API Reference

### Endpoints

#### GET /pages/create
Display page creation form

**Response**: HTML form with validation

#### POST /pages
Create a new page

**Parameters**:
- `title` (string, required): Page title
- `content` (string, required): Page content
- `namespace` (string, optional): Page namespace
- `content_format` (string, optional): Content format (markdown, html, wikitext)
- `comment` (string, optional): Edit summary

**Response**: 302 redirect to created page

### Error Codes
- `400`: Validation error
- `403`: Permission denied
- `500`: Server error

## Security Considerations

### Input Validation
- All user input is validated and sanitized
- HTML injection is prevented
- XSS protection is implemented
- SQL injection protection via prepared statements

### Access Control
- Authentication is temporarily disabled for testing
- Will be re-enabled when session sharing is fixed
- CSRF protection is temporarily disabled
- Will be re-enabled when forms include proper tokens

### Data Integrity
- Database transactions ensure data consistency
- Revision tracking provides audit trail
- Proper error handling prevents data corruption
- Backup recommendations for production use 