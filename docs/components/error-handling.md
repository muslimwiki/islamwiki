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
# Error Handling

## Overview

The error handling system in IslamWiki provides a consistent way to handle and display errors and exceptions throughout the application. It includes built-in error pages, custom error views, and detailed debugging information in development mode.

## Version
0.0.5

## Features

- Automatic error page rendering for HTTP status codes
- Custom error pages per status code
- Development-friendly error pages with stack traces
- JSON error responses for API requests
- PSR-3 compatible logging

## Configuration

Error handling is configured in the application bootstrap process. The main configuration options are:

- `APP_DEBUG`: Set to `true` to enable detailed error reporting
- `APP_ENV`: Set to `local` for development, `production` for live
- `DISPLAY_ERRORS`: Controls whether errors are displayed (handled automatically based on environment)

## Usage

### Throwing HTTP Errors

You can throw HTTP exceptions from anywhere in your application:

```php
use IslamWiki\Core\Http\Response;

// Return a 404 response
return Response::error('Page not found', 404);

// Or throw an exception
throw new \RuntimeException('Page not found', 404);
```

### Custom Error Pages

Create custom error pages by adding HTML files to `resources/views/errors/`. Name the files after the status code (e.g., `404.html`, `500.html`).

Example (`resources/views/errors/404.html`):

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Page Not Found</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
        }
        .container { 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #fff; 
            border-radius: 5px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: #dc3545; 
            margin-top: 0; 
        }
        .error { 
            background: #fff5f5; 
            border-left: 4px solid #dc3545; 
            padding: 15px; 
            margin: 20px 0; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 - Page Not Found</h1>
        <div class="error">
            <p>The page you are looking for could not be found.</p>
            <p>Check the URL for typos or <a href="/">return to the homepage</a>.</p>
        </div>
    </div>
</body>
</html>
```

### Available Placeholders

In custom error pages, you can use these placeholders:

- `{{ statusCode }}`: The HTTP status code (e.g., 404)
- `{{ statusText }}`: The status text (e.g., "Not Found")
- `{{ message }}`: The error message
- `{{ title }}`: The page title (status code + status text)

### Debug Information

When `APP_DEBUG=true` or `APP_ENV=local`, error pages include:

- Error message
- File and line number
- Stack trace
- Request details
- Server information

### Logging

All errors are logged using the PSR-3 logger. The default configuration logs to:

- `storage/logs/error.log` for errors
- `storage/logs/debug.log` for debug information

## Best Practices

1. **User-Friendly Messages**: Always show user-friendly messages in production
2. **Detailed Logging**: Log detailed error information for debugging
3. **Custom Error Pages**: Create custom error pages for better user experience
4. **Security**: Don't expose sensitive information in error messages
5. **Monitoring**: Set up error monitoring for production environments

## API Error Responses

For API requests (detected by `Accept: application/json` header), errors are returned as JSON:

```json
{
    "error": {
        "code": 404,
        "message": "Resource not found",
        "details": {
            "resource": "/api/users/123",
            "method": "GET"
        },
        "trace": [] // Only in debug mode
    }
}
```

## Testing Error Pages

To test error pages, you can use these test routes:

- `/test/error/404` - Test 404 page
- `/test/error/500` - Test 500 page
- `/test/error/403` - Test 403 page

These routes are only available in development mode.
