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
# Home Controller

## Overview

The `HomeController` handles the main entry point of the application, serving the home page.

## Version
0.0.1

## Methods

### `index(Request $request): Response`

Serves the application's home page.

**Parameters:**
- `Request $request`: The HTTP request object

**Returns:**
- `Response`: HTTP response with the home page content

**Example:**
```php
// Example route definition
Router::map('GET', '/', 'IslamWiki\Http\Controllers\HomeController@index');
```

## Dependencies
- `IslamWiki\Core\Database\Connection`: Database connection
- `IslamWiki\Core\Container`: Dependency injection container
- `Psr\Log\LoggerInterface`: Logging interface

## Template
- Uses `resources/views/pages/home.twig`
- Extends `resources/views/layouts/app.twig`

## Error Handling
- Logs all access attempts
- Handles errors gracefully with appropriate HTTP status codes

## Version History
- **0.0.1 (2025-07-26)**: Initial implementation with basic home page
