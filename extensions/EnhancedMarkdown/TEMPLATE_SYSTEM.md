# Template System Documentation

## Overview

IslamWiki's Enhanced Markdown system follows MediaWiki's approach to templates: **templates are stored as namespace pages** that can be modified by users without changing code.

## How It Works

### 1. Template Storage
- Templates are stored as pages in the `Template:` namespace
- Example: `{{Good article}}` is stored at `/wiki/Template:Good_article`
- Templates use Enhanced Markdown syntax with special parameter placeholders

### 2. Template Lookup
When `{{TemplateName}}` is encountered:
1. System looks for `/wiki/Template:TemplateName`
2. If found, loads the template content from the database
3. If not found, falls back to built-in template rendering
4. Parameters are substituted into the template

### 3. Parameter Substitution
Templates use MediaWiki-style parameter placeholders:
- `{{{1}}}` - First positional parameter
- `{{{2}}}` - Second positional parameter  
- `{{{param}}}` - Named parameter
- `{{{param|default}}}` - Parameter with default value

## Template Examples

### Good Article Template
**Location**: `/wiki/Template:Good_article`

**Content**:
```markdown
<div class="template article-quality">
    <i class="fas fa-star"></i> This is a good article
</div>
```

**Usage**:
```markdown
{{Good article}}
```

### About Template
**Location**: `/wiki/Template:About`

**Content**:
```markdown
<div class="template about-template">
    <p>This article is about <strong>{{{1}}}</strong>
    {{#if:{{{3}}}|. For other uses, see <a href="/wiki/{{{3}}}">{{{3}}}</a>|}}.</p>
</div>
```

**Usage**:
```markdown
{{About|the religion||Islam (disambiguation)}}
```

## Creating Templates

### 1. Create Template Page
Go to `/wiki/Template:YourTemplateName` and create a new page.

### 2. Write Template Content
Use Enhanced Markdown with parameter placeholders:

```markdown
<div class="template your-template">
    <h3>{{{title|Default Title}}}</h3>
    <p>{{{1}}}</p>
    <div class="template-footer">{{{footer}}}</div>
</div>
```

### 3. Use the Template
```markdown
{{YourTemplateName|Main content|title=Custom Title|footer=Additional info}}
```

## Template Parameters

### Positional Parameters
```markdown
{{Template|First|Second|Third}}
```
- `{{{1}}}` = "First"
- `{{{2}}}` = "Second"  
- `{{{3}}}` = "Third"

### Named Parameters
```markdown
{{Template|param1=value1|param2=value2}}
```
- `{{{param1}}}` = "value1"
- `{{{param2}}}` = "value2"

### Mixed Parameters
```markdown
{{Template|First|Second|param1=value1}}
```
- `{{{1}}}` = "First"
- `{{{2}}}` = "Second"
- `{{{param1}}}` = "value1"

### Default Values
```markdown
{{{param|default value}}}
```
If `param` is not provided, uses "default value"

## Template Features

### 1. User-Editable
- Templates can be modified by any user with edit permissions
- Changes automatically apply to all pages using the template
- Version history is maintained

### 2. Caching
- Templates are cached for performance
- Cache is cleared when templates are updated
- Automatic cache invalidation

### 3. Fallback System
- If a template doesn't exist, falls back to built-in rendering
- Built-in templates provide consistent behavior
- Unknown templates show links to create/edit them

### 4. Parameter Validation
- Templates can validate required parameters
- Default values for optional parameters
- Error handling for malformed templates

## Built-in Templates

The system includes built-in templates for common use cases:

- **Page Information**: `{{About}}`, `{{Good article}}`, `{{pp-semi-indef}}`
- **Navigation**: `{{Main}}`, `{{See also}}`, `{{Further information}}`
- **Content**: `{{Infobox}}`, `{{Cquote}}`, `{{Warning}}`
- **References**: `{{reflist}}`, `{{Portal}}`

## Template Management

### Viewing Templates
- List all templates: `/wiki/Special:Templates`
- View template usage: `/wiki/Special:WhatLinksHere/Template:TemplateName`

### Editing Templates
- Go to `/wiki/Template:TemplateName`
- Click "Edit" tab
- Modify template content
- Save changes

### Creating New Templates
- Go to `/wiki/Template:NewTemplateName`
- Click "Create" or "Edit"
- Write template content
- Save the page

### Template Documentation
- Each template should include usage examples
- Document all parameters and their purposes
- Provide sample output
- Include related templates

## Best Practices

### 1. Template Naming
- Use descriptive names: `{{Good article}}` not `{{ga}}`
- Capitalize first letter: `{{Infobox}}` not `{{infobox}}`
- Use spaces, not underscores: `{{Page protection}}` not `{{Page_protection}}`

### 2. Parameter Design
- Use named parameters for clarity: `{{Infobox|title=Title|content=Content}}`
- Provide sensible defaults: `{{{title|Article Title}}}`
- Document all parameters

### 3. Template Content
- Keep templates simple and focused
- Use semantic HTML classes for styling
- Include error handling for missing parameters
- Make templates accessible

### 4. Documentation
- Document every template thoroughly
- Include usage examples
- Explain parameter meanings
- Show sample output

## Technical Implementation

### Database Schema
Templates are stored in the `pages` table:
```sql
CREATE TABLE pages (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    namespace VARCHAR(50),
    content TEXT,
    content_format VARCHAR(20),
    slug VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Template Lookup
```php
// Load template from database
$template = $templateManager->loadTemplate('Good article');

// Substitute parameters
$rendered = str_replace('{{{1}}}', $param1, $template);

// Process remaining wiki syntax
$final = $wikiProcessor->process($rendered);
```

### Caching
```php
// Check cache first
if (isset($this->templateCache[$templateName])) {
    return $this->templateCache[$templateName];
}

// Load from database and cache
$content = $this->loadFromDatabase($templateName);
$this->templateCache[$templateName] = $content;
```

## Migration from MediaWiki

### Template Conversion
1. Export templates from MediaWiki
2. Convert MediaWiki syntax to Enhanced Markdown
3. Update parameter placeholders: `{{{1}}}` instead of `{{{1}}}`
4. Test templates with sample content

### Parameter Mapping
- MediaWiki: `{{{param|default}}}` → Enhanced Markdown: `{{{param|default}}}`
- MediaWiki: `{{#if:condition|then|else}}` → Enhanced Markdown: Custom logic needed

### Template Functions
- MediaWiki parser functions need custom implementation
- Consider using Enhanced Markdown's built-in features
- Implement common functions like `{{#if}}`, `{{#switch}}`

## Future Enhancements

### 1. Advanced Parser Functions
- Conditional logic: `{{#if:condition|then|else}}`
- Loops: `{{#foreach:array|item}}`
- String manipulation: `{{#replace:text|find|replace}}`

### 2. Template Inheritance
- Base templates with overridable sections
- Template composition and nesting
- Template versioning and rollback

### 3. Template Validation
- Parameter type checking
- Required parameter validation
- Template syntax validation
- Performance optimization

---

*This documentation covers the template system for IslamWiki's Enhanced Markdown. Templates follow MediaWiki's approach while leveraging Enhanced Markdown's capabilities.* 