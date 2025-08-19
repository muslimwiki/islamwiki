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
# Database Models

This document provides detailed documentation of all database models in the IslamWiki application.

## Table of Contents
1. [Core Models](#core-models)
   - [Page](#page)
   - [Revision](#revision)
   - [User](#user)
   - [Category](#category)
   - [Media](#media)
2. [Relationships](#relationships)
3. [Query Scopes](#query-scopes)
4. [Events and Observers](#events-and-observers)
5. [Validation Rules](#validation-rules)
6. [API Resources](#api-resources)

## Core Models

### Page

Represents a wiki page in the system.

#### Table: `pages`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `title` | string | Page title |
| `slug` | string | URL-friendly identifier |
| `namespace` | string | Page namespace (e.g., 'Main', 'User', 'Help') |
| `content` | text | Current page content |
| `is_locked` | boolean | Whether the page is locked from editing |
| `locked_by` | bigint | ID of user who locked the page |
| `locked_at` | timestamp | When the page was locked |
| `view_count` | integer | Number of views |
| `last_viewed_at` | timestamp | When the page was last viewed |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Methods

```php
// Get the page's full title including namespace
public function getFullTitle(): string;

// Get the page's URL
public function getUrl(): string;

// Check if the page is locked
public function isLocked(): bool;

// Get all revisions for this page
public function revisions(): HasMany;

// Get the latest revision
public function latestRevision(): HasOne;

// Get the user who created the page
public function creator(): BelongsTo;

// Get all categories for this page
public function categories(): BelongsToMany;
```

### Revision

Represents a version of a wiki page.

#### Table: `revisions`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `page_id` | bigint | Reference to the page |
| `user_id` | bigint | User who created this revision |
| `content` | text | Page content at this revision |
| `comment` | string | Edit comment |
| `is_minor_edit` | boolean | Whether this was a minor edit |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Methods

```php
// Get the page this revision belongs to
public function page(): BelongsTo;

// Get the user who created this revision
public function user(): BelongsTo;

// Get the previous revision
public function previous(): Revision|null;

// Get the next revision
public function next(): Revision|null;

// Get the difference between this and another revision
public function diff(Revision $other): array;
```

### User

Represents a system user.

#### Table: `users`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `username` | string | Unique username |
| `email` | string | Email address |
| `password` | string | Hashed password |
| `display_name` | string | User's display name |
| `is_admin` | boolean | Whether user is an administrator |
| `email_verified_at` | timestamp | When email was verified |
| `last_login_at` | timestamp | Last login timestamp |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

#### Methods

```php
// Get user's pages
public function pages(): HasMany;

// Get user's revisions
public function revisions(): HasMany;

// Check if user has a specific permission
public function hasPermission(string $permission): bool;

// Check if user is an administrator
public function isAdmin(): bool;

// Get user's recent activity
public function recentActivity(int $limit = 10): Collection;
```

## Relationships

### Page Relationships

- A page has many revisions
- A page belongs to a creator (user)
- A page can have many categories
- A page can have many media items

### Revision Relationships

- A revision belongs to a page
- A revision belongs to a user
- A revision has one previous revision
- A revision has one next revision

### User Relationships

- A user has many pages (as creator)
- A user has many revisions
- A user can watch many pages
- A user can have many preferences

## Query Scopes

### Page Scopes

```php
// Get pages in a specific namespace
public function scopeInNamespace($query, string $namespace);

// Search pages by title or content
public function scopeSearch($query, string $term);

// Get only locked pages
public function scopeLocked($query);

// Get pages created by a specific user
public function scopeByUser($query, int $userId);
```

### Revision Scopes

```php
// Get revisions for a specific page
public function scopeForPage($query, int $pageId);

// Get revisions by a specific user
public function scopeByUser($query, int $userId);

// Get only minor/major edits
public function scopeMinor($query, bool $minor = true);
```

## Events and Observers

### Page Events

- `page.creating`: Before a page is created
- `page.created`: After a page is created
- `page.updating`: Before a page is updated
- `page.updated`: After a page is updated
- `page.deleting`: Before a page is deleted
- `page.deleted`: After a page is deleted

### Revision Events

- `revision.creating`: Before a revision is created
- `revision.created`: After a revision is created

## Validation Rules

### Page Validation

```php
[
    'title' => 'required|string|max:255',
    'namespace' => 'required|string|max:50',
    'content' => 'required|string',
    'is_locked' => 'boolean',
    'categories' => 'array',
    'categories.*' => 'exists:categories,id',
]
```

### Revision Validation

```php
[
    'page_id' => 'required|exists:pages,id',
    'user_id' => 'required|exists:users,id',
    'content' => 'required|string',
    'comment' => 'nullable|string|max:500',
    'is_minor_edit' => 'boolean',
]
```

## API Resources

### Page Resource

```json
{
    "id": 1,
    "title": "Sample Page",
    "slug": "Sample_Page",
    "namespace": "Main",
    "url": "/wiki/Sample_Page",
    "content": "This is the page content...",
    "is_locked": false,
    "view_count": 42,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z",
    "creator": {
        "id": 1,
        "username": "admin",
        "display_name": "Administrator"
    },
    "categories": [
        {"id": 1, "name": "Documentation"},
        {"id": 2, "name": "Guide"}
    ]
}
```

### Revision Resource

```json
{
    "id": 1,
    "page_id": 1,
    "user": {
        "id": 1,
        "username": "admin",
        "display_name": "Administrator"
    },
    "comment": "Initial version",
    "is_minor_edit": false,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

---
*Last Updated: 2025-07-25*
