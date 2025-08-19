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
# Page Controller Documentation

## Overview
The `PageController` is a core controller in the IslamWiki application that handles all operations related to wiki pages, including viewing, creating, editing, and managing page revisions, permissions, and locks.

## Table of Contents
1. [Methods](#methods)
   - [index](#index)
   - [show](#show)
   - [create](#create)
   - [store](#store)
   - [edit](#edit)
   - [update](#update)
   - [history](#history)
   - [showRevision](#showrevision)
   - [revert](#revert)
   - [lock](#lock)
   - [unlock](#unlock)
2. [Helper Methods](#helper-methods)
   - [isAdmin](#isadmin)
   - [canCreatePage](#cancreatepage)
   - [canEditPage](#caneditpage)
   - [canDeletePage](#candeletepage)
   - [logPageView](#logpageview)
   - [parseWikiText](#parsewikitext)
   - [generateSlug](#generateslug)
3. [Error Handling](#error-handling)
4. [Logging](#logging)
5. [Permissions](#permissions)

## Methods

### index
`public function index(Request $request): Response`

Displays a paginated list of all wiki pages with sorting and filtering options.

**Parameters:**
- `Request $request`: The HTTP request object

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)
- `namespace`: Filter by namespace
- `q`: Search query
- `sort`: Field to sort by (title, updated_at, views)
- `order`: Sort order (asc, desc)

**Returns:**
- `Response`: Rendered view with paginated page list

---

### show
`public function show(Request $request, string $slug): Response`

Displays a specific wiki page by its slug.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: URL-friendly page identifier

**Behavior:**
1. Attempts to find page by exact slug
2. If not found, checks 'Main' namespace
3. Handles redirects and 404s
4. Manages page view counting
5. Handles locked pages and permissions

**Returns:**
- `Response`: Rendered page view or redirect

---

### create
`public function create(Request $request): Response`

Shows the form for creating a new wiki page.

**Parameters:**
- `Request $request`: The HTTP request object

**Query Parameters:**
- `title`: Pre-filled page title
- `namespace`: Pre-selected namespace

**Returns:**
- `Response`: Page creation form

---

### store
`public function store(Request $request): Response`

Stores a newly created page in the database.

**Parameters:**
- `Request $request`: The HTTP request with form data

**Form Data:**
- `title`: Page title (required)
- `namespace`: Page namespace
- `content`: Page content (required)
- `comment`: Edit comment
- `is_minor_edit`: Boolean for minor edit flag
- `watch`: Boolean to watch the page

**Returns:**
- `Response`: Redirect to the new page or back with errors

---

### edit
`public function edit(Request $request, string $slug): Response`

Shows the form for editing an existing page.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug to edit

**Returns:**
- `Response`: Page edit form or 404 if not found

---

### update
`public function update(Request $request, string $slug): Response`

Updates an existing page in the database.

**Parameters:**
- `Request $request`: The HTTP request with form data
- `string $slug`: Page slug to update

**Form Data:**
- `content`: Updated page content
- `comment`: Edit comment
- `content_format`: Content format (e.g., markdown)

**Returns:**
- `Response`: Redirect to the updated page

---

### history
`public function history(Request $request, string $slug): Response`

Displays the revision history of a page.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug to view history for

**Returns:**
- `Response`: Page history view

---

### showRevision
`public function showRevision(Request $request, string $slug, int $revisionId): Response`

Displays a specific revision of a page.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug
- `int $revisionId`: Revision ID to display

**Returns:**
- `Response`: Revision view or 404 if not found

---

### revert
`public function revert(Request $request, string $slug, int $revisionId): Response`

Reverts a page to a specific revision.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug to revert
- `int $revisionId`: Target revision ID

**Returns:**
- `Response`: Redirect to the reverted page

---

### lock
`public function lock(Request $request, string $slug): Response`

Locks a page to prevent further edits.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug to lock

**Returns:**
- `Response`: Redirect back to the page with status message

---

### unlock
`public function unlock(Request $request, string $slug): Response`

Unlocks a page to allow edits.

**Parameters:**
- `Request $request`: The HTTP request object
- `string $slug`: Page slug to unlock

**Returns:**
- `Response`: Redirect back to the page with status message

## Helper Methods

### isAdmin
`protected function isAdmin(Request $request): bool`

Checks if the current user is an administrator.

---

### canCreatePage
`protected function canCreatePage(Request $request): bool`

Checks if the current user can create pages.

---

### canEditPage
`protected function canEditPage(Page $page, Request $request): bool`

Checks if the current user can edit the specified page.

---

### canDeletePage
`protected function canDeletePage(Page $page, Request $request): bool`

Checks if the current user can delete the specified page.

---

### logPageView
`protected function logPageView(Page $page, Request $request): void`

Logs detailed information about a page view for analytics.

---

### parseWikiText
`protected function parseWikiText(string $text): string`

Converts wiki text to HTML.

---

### generateSlug
`protected function generateSlug(string $namespace, string $title): string`

Generates a URL-friendly slug from a title and namespace.

## Error Handling
The controller implements comprehensive error handling:
- Validates all input data
- Handles database errors gracefully
- Provides appropriate HTTP status codes
- Logs all errors with detailed context

## Logging
The controller logs important events including:
- Page views and edits
- Permission denials
- System errors and exceptions
- Administrative actions

## Permissions
Permission checks are implemented for all sensitive operations:
- Page creation
- Editing
- Deletion
- Reverting
- Locking/Unlocking

Permissions can be extended by modifying the relevant helper methods.

---

*Documentation generated on July 25, 2025*
