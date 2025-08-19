# Error Handling System

This document describes the comprehensive error handling system implemented in IslamWiki, providing robust error management, debugging capabilities, and user-friendly error responses.

## Overview

The error handling system consists of multiple components working together to provide:
- **Exception Catching**: All exceptions are caught and handled gracefully
- **Debug Information**: Detailed error information in development mode
- **User-Friendly Pages**: Professional error pages with helpful navigation
- **Performance Monitoring**: Request timing and memory usage tracking
- **Comprehensive Logging**: All errors logged with full context

## Components

### ErrorHandlingMiddleware

The main error handling middleware that catches and processes all exceptions.

#### Features
- **Exception Catching**: Catches all `Throwable` exceptions
- **HTTP Exception Handling**: Special handling for `HttpException`
- **Debug Mode**: Shows detailed error information in development
- **Performance Tracking**: Monitors request processing time
- **Memory Monitoring**: Tracks memory usage and peak memory

#### Error Response Types

**HTTP Exceptions (4xx, 5xx):**
```php
// 404 Not Found
throw new HttpException(404, 'Page not found');

// 403 Forbidden
throw new HttpException(403, 'Access denied');

// 429 Too Many Requests
throw new HttpException(429, 'Rate limit exceeded');
```

**General Exceptions:**
```php
// All other exceptions return 500 Internal Server Error
// In development: Shows detailed error information
// In production: Shows user-friendly error message
```

#### Error Page Features
- **Professional Styling**: Clean, modern error pages
- **Navigation Links**: Helpful links to homepage and other pages
- **Debug Information**: Stack traces and server info in development
- **Responsive Design**: Works on all device sizes
- **Accessibility**: Proper semantic HTML and ARIA labels

### ErrorHandler (Core)

The core error handler that manages PHP errors and fatal errors.

#### Features
- **Error Reporting**: Comprehensive error reporting configuration
- **Exception Handling**: Global exception handler
- **Fatal Error Handling**: Shutdown function for fatal errors
- **Log File Management**: Automatic log file creation and rotation
- **Debug Mode**: Environment-aware error handling

#### Configuration
```php
// Initialize error handler
ErrorHandler::initialize($debug = true);

// Set logger
ErrorHandler::setLogger($logger);
```

## Error Pages

### 404 Not Found
- **Title**: "Page Not Found"
- **Message**: "The page you're looking for doesn't exist."
- **Actions**: Links to homepage, pages index, and back button
- **Debug Info**: Request URI and server information

### 403 Forbidden
- **Title**: "Access Denied"
- **Message**: "You don't have permission to access this resource."
- **Actions**: Links to homepage and login page
- **Debug Info**: User information and access details

### 500 Internal Server Error
- **Title**: "Internal Server Error"
- **Message**: "Something went wrong on our end."
- **Actions**: Links to homepage and contact page
- **Debug Info**: Full stack trace and server information

### 429 Too Many Requests
- **Title**: "Too Many Requests"
- **Message**: "You've made too many requests. Please try again later."
- **Actions**: Links to homepage and back button
- **Debug Info**: Rate limit information and retry timing

## Debug Information

### Development Mode
When `APP_DEBUG=true`, error pages include:

**Exception Details:**
- Exception class name
- Error message
- File and line number
- Full stack trace

**Server Information:**
- PHP version
- Server software
- Memory limits
- Upload limits
- Execution time limits

**Request Information:**
- Request method and URI
- User agent
- IP address
- Request timestamp

**Performance Metrics:**
- Memory usage
- Peak memory usage
- Request processing time
- Database query count

### Production Mode
When `APP_DEBUG=false`, error pages show:
- User-friendly error messages
- Helpful navigation links
- No sensitive information
- Contact information for support

## Logging

### Error Logging
All errors are logged with comprehensive context:

```php
$logger->error('Unhandled exception occurred', [
    'ip' => $request->getClientIp(),
    'method' => $request->getMethod(),
    'uri' => $request->getUri()->getPath(),
    'exception_class' => get_class($e),
    'message' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
    'user_agent' => $request->getHeaderLine('User-Agent'),
    'referer' => $request->getHeaderLine('Referer'),
    'server_info' => $this->getServerInfo(),
]);
```

### Performance Logging
Successful requests are logged with performance metrics:

```php
$logger->info('Request completed successfully', [
    'ip' => $request->getClientIp(),
    'method' => $request->getMethod(),
    'uri' => $request->getUri()->getPath(),
    'status_code' => $response->getStatusCode(),
    'processing_time' => round($processingTime * 1000, 2) . 'ms',
    'memory_usage' => $this->formatBytes(memory_get_usage()),
    'peak_memory' => $this->formatBytes(memory_get_peak_usage()),
]);
```

## Configuration

### Environment Variables
```bash
# .env
APP_DEBUG=true          # Enable debug mode
APP_ENV=development     # Environment (development/production)
APP_LOG_LEVEL=debug     # Log level (debug, info, warning, error)
```

### Error Handler Configuration
```php
// Initialize with debug mode
ErrorHandler::initialize(env('APP_DEBUG', false));

// Set log file location
ini_set('error_log', storage_path('logs/php_errors.log'));

// Set error reporting level
error_reporting(E_ALL);
```

## Custom Error Pages

### Creating Custom Error Pages
You can create custom error pages by extending the error handling:

```php
// Custom 404 page
public function renderCustom404(): string
{
    return view('errors.custom-404', [
        'message' => 'Custom 404 message',
        'suggestions' => $this->getSuggestions(),
    ]);
}
```

### Error Page Templates
Error pages use consistent styling and structure:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $title }} - IslamWiki</title>
    <style>
        /* Professional error page styling */
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">{{ $statusCode }}</h1>
        <h2 class="error-title">{{ $title }}</h2>
        <p class="error-message">{{ $message }}</p>
        
        <div class="actions">
            <a href="/" class="btn">Go to Homepage</a>
            <a href="/pages" class="btn">Browse Pages</a>
            <a href="javascript:history.back()" class="btn">Go Back</a>
        </div>
        
        @if($debug && $exception)
            <div class="debug-info">
                <!-- Debug information -->
            </div>
        @endif
    </div>
</body>
</html>
```

## Testing Error Handling

### Test Script
Run the error handling test suite:

```bash
php scripts/test_security_error_handling.php
```

### Manual Testing
Test error scenarios manually:

```bash
# Test 404 error
curl "http://localhost:8000/nonexistent-page"

# Test 500 error
curl "http://localhost:8000/test/error"

# Test rate limiting
for i in {1..70}; do curl "http://localhost:8000/test"; done
```

### Error Simulation
Simulate different error conditions:

```php
// Test exception handling
throw new RuntimeException('Test exception');

// Test HTTP exceptions
throw new HttpException(404, 'Page not found');
throw new HttpException(403, 'Access denied');
throw new HttpException(429, 'Rate limit exceeded');
```

## Best Practices

### Error Handling
- **Catch All Exceptions**: Never let exceptions bubble up to the user
- **Log Everything**: Log all errors with full context
- **User-Friendly Messages**: Show helpful messages to users
- **Debug Information**: Include debug info only in development
- **Performance Monitoring**: Track request timing and memory usage

### Logging
- **Structured Logging**: Use structured data for better analysis
- **Context Information**: Include request details and user information
- **Log Levels**: Use appropriate log levels (debug, info, warning, error)
- **Log Rotation**: Implement log rotation to manage disk space
- **Security**: Never log sensitive information

### Production Considerations
- **Disable Debug Mode**: Set `APP_DEBUG=false` in production
- **Error Monitoring**: Set up error monitoring and alerting
- **Performance Tracking**: Monitor error rates and response times
- **Security**: Ensure error pages don't expose sensitive information
- **Backup Logs**: Implement log backup and retention policies

## Troubleshooting

### Common Issues

**Permission Denied Errors:**
```bash
sudo chown -R www-data:www-data storage/ logs/
sudo chmod -R 755 storage/ logs/
```

**Log File Issues:**
```bash
# Check log directory permissions
ls -la storage/logs/

# Check disk space
df -h

# Check log file sizes
du -sh storage/logs/*
```

**Debug Mode Not Working:**
```bash
# Check environment variables
echo $APP_DEBUG

# Check .env file
cat .env | grep APP_DEBUG
```

**Error Pages Not Styled:**
- Ensure CSS files are accessible
- Check for 404 errors on CSS resources
- Verify Content Security Policy settings

## Monitoring

### Error Metrics
Track these key metrics:
- **Error Rate**: Percentage of requests that result in errors
- **Response Time**: Average response time for error pages
- **Memory Usage**: Peak memory usage during error handling
- **Log File Size**: Monitor log file growth and rotation

### Alerting
Set up alerts for:
- High error rates (>5% of requests)
- 500 errors (server errors)
- Memory usage spikes
- Log file size issues

### Log Analysis
Regular log analysis should include:
- **Error Patterns**: Identify common error patterns
- **Performance Issues**: Track slow error responses
- **Security Events**: Monitor for suspicious error patterns
- **User Impact**: Analyze which errors affect users most

## Integration

### Middleware Stack
Error handling middleware is integrated into the middleware stack:

```php
$middlewareStack
    ->add(new ErrorHandlingMiddleware($logger, $debug, $environment))
    ->add(new SecurityMiddleware($logger))
    ->add(new CsrfMiddleware($sessionManager));
```

### Framework Integration
The error handling system integrates with:
- **FastRouter**: Automatic error handling for all routes
- **Logger**: Comprehensive error logging
- **Container**: Service resolution for error handling
- **Session**: User context for error pages

## Future Enhancements

### Planned Features
- **Error Analytics**: Dashboard for error analysis
- **Error Recovery**: Automatic error recovery mechanisms
- **User Feedback**: Error reporting from users
- **Performance Optimization**: Faster error page rendering
- **Internationalization**: Multi-language error messages

### Monitoring Improvements
- **Real-time Monitoring**: Live error monitoring dashboard
- **Predictive Analysis**: Predict potential errors
- **Automated Fixes**: Automatic error resolution
- **User Impact Analysis**: Measure impact of errors on users

---

*This error handling system provides comprehensive protection and debugging capabilities while maintaining excellent user experience across all error scenarios.*
