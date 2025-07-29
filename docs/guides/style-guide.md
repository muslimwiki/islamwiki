<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
# IslamWiki Documentation Style Guide

## Table of Contents
1. [Documentation Principles](#documentation-principles)
2. [File and Directory Structure](#file-and-directory-structure)
3. [Markdown Guidelines](#markdown-guidelines)
4. [Code Documentation](#code-documentation)
5. [API Documentation](#api-documentation)
6. [Tutorials and How-Tos](#tutorials-and-how-tos)
7. [Versioning and Updates](#versioning-and-updates)

## Documentation Principles

### 1. Clarity and Accuracy
- Write clear, concise, and accurate documentation
- Keep documentation up-to-date with code changes
- Use simple, direct language
- Avoid jargon without explanation
- Be consistent in terminology

### 2. Audience Awareness
- Write for multiple audiences (new developers, experienced contributors, end users)
- Include both high-level overviews and detailed references
- Provide examples for complex concepts

### 3. Completeness
- Document all public and protected methods
- Include parameter and return type information
- Document error conditions and exceptions
- Include usage examples

## File and Directory Structure

```
docs/
├── architecture/     # System architecture and design decisions
├── components/       # Reusable components documentation
├── config/          # Configuration reference
├── controllers/     # Controller documentation
├── deployment/      # Deployment and operations
├── guides/          # Tutorials and how-to guides
├── models/          # Data models documentation
├── security/        # Security policies and guidelines
├── testing/         # Testing strategy and guidelines
├── views/           # Views and templates documentation
├── README.md        # Main documentation index
└── CONTRIBUTING.md  # Contribution guidelines
```

## Markdown Guidelines

### Headers
```markdown
# H1 - Document Title
## H2 - Main Sections
### H3 - Subsections
#### H4 - Sub-subsections
```

### Code Blocks
Use fenced code blocks with language specification:

````markdown
```php
public function example()
{
    return 'Hello, World!';
}
```
````

### Tables

```markdown
| Parameter | Type   | Description                |
|-----------|--------|----------------------------|
| `name`    | string | The name of the user       |
| `age`     | int    | The age of the user        |
```

### Notes and Warnings

```markdown
> **Note:** This is an important note.

> **Warning:** This is a warning about potential issues.
```

## Code Documentation

### PHP DocBlocks

```php
/**
 * Short description of the method/class.
 *
 * Longer description that explains the purpose, behavior,
 * and any important details about the method/class.
 *
 * @param string $param1 Description of first parameter
 * @param int $param2 Description of second parameter
 * @return string Description of return value
 * @throws \Exception Description of when this exception is thrown
 * @since 1.0.0
 */
public function exampleMethod(string $param1, int $param2): string
{
    // Method implementation
}
```

### Class Documentation

```php
/**
 * Brief description of the class.
 *
 * Detailed description of the class, its purpose, and how it fits into
 * the larger system. Include any important implementation details.
 *
 * @package IslamWiki\Namespace
 * @author Author Name <author@example.com>
 * @copyright Copyright (c) 2025, IslamWiki
 * @license MIT
 * @version 1.0.0
 */
class ExampleClass
{
    // Class implementation
}
```

## API Documentation

### Endpoint Documentation

```markdown
## GET /api/v1/pages/{slug}

Retrieves a page by its slug.

### Parameters

| Name   | In   | Type   | Required | Description          |
|--------|------|--------|----------|----------------------|
| slug   | path | string | Yes      | The page slug        |
| fields | query| string | No       | Comma-separated list of fields to return |

### Responses

#### 200 OK
```json
{
    "id": 123,
    "title": "Example Page",
    "content": "Page content...",
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

#### 404 Not Found
```json
{
    "error": {
        "code": "not_found",
        "message": "The requested page was not found."
    }
}
```

## Tutorials and How-Tos

### Structure
1. **Title**: Clear and descriptive
2. **Overview**: Brief description of what the tutorial covers
3. **Prerequisites**: Any requirements or knowledge needed
4. **Steps**: Numbered, clear steps
5. **Examples**: Code examples and explanations
6. **Troubleshooting**: Common issues and solutions

### Example
```markdown
# How to Create a New Page

## Overview
This guide walks you through creating a new page in IslamWiki.

## Prerequisites
- Basic understanding of Markdown
- Access to the wiki with appropriate permissions

## Steps
1. Navigate to the desired namespace
2. Click "Create Page"
3. Enter the page title and content
4. Add categories and tags
5. Save the page

## Example
```markdown
# My New Page

This is the content of my new page.
```

## Troubleshooting
- If you get a permission error, contact your administrator
- If the page already exists, you'll be prompted to edit it
```

## Versioning and Updates

### Versioning
- Use [Semantic Versioning](https://semver.org/)
- Document changes in `CHANGELOG.md`
- Include version numbers in documentation

### Updating Documentation

### PHPDoc Blocks
- Document all classes, methods, and properties
- Include parameter and return types
- Use `@throws` for exceptions
- Include `@var` for class properties

### Example
```php
/**
 * Handles user authentication
 * 
 * @package IslamWiki\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @var LoggerInterface Logger instance
     */
    private LoggerInterface $_logger;
    
    /**
     * Authenticate a user
     *
     * @param Request $request HTTP request
     * @return Response
     * @throws AuthenticationException If authentication fails
     */
    public function login(Request $request): Response
    {
        // Implementation
    }
}
```

## Version Control

### Branch Naming
- `feature/` - New features
- `bugfix/` - Bug fixes
- `hotfix/` - Critical production fixes
- `release/` - Release preparation

### Commit Messages
- Use present tense ("Add feature" not "Added feature")
- Start with a capital letter
- Keep the first line under 50 characters
- Include details in the body if needed

### Example
```
Add user authentication

- Implement login/logout functionality
- Add password hashing
- Create user session management
```

## Frontend Guidelines

### HTML/CSS
- Use semantic HTML5
- Follow BEM naming convention for CSS classes
- Use CSS variables for theming
- Keep styles modular and scoped to components

### JavaScript
- Use ES6+ syntax
- Use modules for code organization
- Follow Airbnb JavaScript Style Guide
- Use `const` and `let` instead of `var`

### Example
```html
<article class="card card--featured">
  <h2 class="card__title">Welcome to IslamWiki</h2>
  <div class="card__content">
    <p>Your Islamic knowledge base is up and running!</p>
  </div>
</article>
```

## Version History
- **0.0.1 (2025-07-26)**: Initial version of the style guide

## Tools and Resources
- [Markdown Guide](https://www.markdownguide.org/)
- [PHP Documentation Standards](https://docs.phpdoc.org/)
- [OpenAPI Specification](https://swagger.io/specification/)

---
*Last Updated: 2025-07-25*
