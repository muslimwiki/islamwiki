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
# Components Documentation

This directory contains documentation for the various components in the IslamWiki application.

## Available Components

### Core Components
- **Router** - Handles HTTP request routing
- **Request** - Handles HTTP requests
- **Response** - Handles HTTP responses
- **Container** - Dependency injection container
- **View** - Template rendering system

### HTTP Components
- **Controllers** - Request handlers
- **Middleware** - Request/response processing pipeline

### Database Components
- **Connection** - Database connection handler
- **Query Builder** - Database query construction

## Version
0.0.1

## Documentation Index

- [Router](./router.md) - Routing system documentation
- [Request/Response](./request-response.md) - HTTP message handling
- [Container](./container.md) - Dependency injection documentation
- [View](./view.md) - Template system documentation

## Adding New Components

1. Create a new markdown file in this directory
2. Follow the existing documentation style
3. Include version information
4. Update this README with a link to the new documentation

## Version History
- **0.0.2 (2025-07-27)**: Added licensing information
- **0.0.1 (2025-07-26)**: Initial documentation structure
