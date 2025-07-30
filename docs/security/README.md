# Security Documentation

This document outlines the comprehensive security features implemented in IslamWiki to protect against common web vulnerabilities and ensure enterprise-level security.

## Overview

IslamWiki implements a multi-layered security approach with comprehensive protection against:
- SQL Injection attacks
- Cross-Site Scripting (XSS)
- Cross-Site Request Forgery (CSRF)
- Directory Traversal attacks
- Rate limiting and abuse prevention
- Input validation and sanitization

## Security Middleware

### SecurityMiddleware

The `SecurityMiddleware` provides comprehensive security features:

#### Rate Limiting
- **Requests per minute**: 60 requests
- **Burst limit**: 10 requests per second
- **Storage**: In-memory (production should use Redis)
- **Configuration**: Configurable limits in middleware

#### Input Validation & Sanitization
- **Null bytes**: Removed from all input
- **Control characters**: Stripped except newlines and tabs
- **Line endings**: Normalized to Unix format
- **Character encoding**: UTF-8 validation

#### Attack Pattern Detection

**SQL Injection Patterns:**
```php
'/union\s*select/i',
'/union\+select/i',
'/union%20select/i',
'/drop\s+table/i',
'/delete\s+from/i',
'/insert\s+into/i',
'/update\s+set/i',
'/exec\s*\(/i',
'/eval\s*\(/i',
```

**XSS Patterns:**
```php
'/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
'/javascript:/i',
'/vbscript:/i',
'/onload\s*=/i',
'/onerror\s*=/i',
```

**Directory Traversal:**
- Blocks `..` and `//` patterns in URLs

#### Security Headers
```php
'X-Content-Type-Options' => 'nosniff',
'X-Frame-Options' => 'DENY',
'X-XSS-Protection' => '1; mode=block',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;",
'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
```

## Error Handling Middleware

### ErrorHandlingMiddleware

Provides comprehensive error handling and debugging:

#### Features
- **Exception Catching**: All exceptions caught and logged
- **Debug Information**: Detailed error info in development mode
- **User-Friendly Pages**: Professional error pages with navigation
- **Performance Monitoring**: Request timing and memory usage
- **Graceful Responses**: Proper HTTP status codes and messages

#### Error Pages
- **404 Not Found**: Page not found with helpful navigation
- **403 Forbidden**: Access denied with explanation
- **500 Internal Server Error**: Server error with debug info in development
- **429 Too Many Requests**: Rate limit exceeded
- **Custom Error Pages**: Professional styling with action buttons

## CSRF Protection

### CsrfMiddleware

Protects against Cross-Site Request Forgery attacks:

#### Features
- **Token Validation**: Verifies CSRF tokens for state-changing requests
- **Flexible Sources**: Checks POST data, headers, and Laravel-style tokens
- **User-Friendly Errors**: Clear explanations for token mismatches
- **Automatic Generation**: Tokens generated for forms
- **Session Integration**: Works with session management

#### Token Sources
1. POST parameter: `_token`
2. Header: `X-CSRF-TOKEN`
3. Header: `X-XSRF-TOKEN` (Laravel style)

#### Excluded Routes
- `/api/` - API endpoints
- `/webhook/` - Webhook endpoints

## Enhanced Logging

### Logger Class

PSR-3 compliant logging with enhanced features:

#### Specialized Methods
```php
$logger->security('SQL injection attempt', ['ip' => '192.168.1.1']);
$logger->userAction('page_edit', ['user_id' => 123, 'page_id' => 456]);
$logger->performance('database_query', 0.123, ['query' => 'SELECT * FROM pages']);
$logger->query('SELECT * FROM pages WHERE id = ?', 0.045, ['params' => [1]]);
$logger->exception($e, ['context' => 'user_action']);
```

#### Log Rotation
- **Max file size**: 10MB (configurable)
- **Files to keep**: 5 (configurable)
- **Automatic rotation**: Based on date and size
- **Compression**: Old logs can be compressed

#### Context Information
- **Request details**: IP, method, URI, user agent
- **Performance metrics**: Timing, memory usage
- **User information**: User ID, session data
- **Server information**: PHP version, memory limits

## Middleware Stack

### MiddlewareStack

Manages the execution order of middleware components:

#### Execution Order
1. Error Handling Middleware
2. Security Middleware
3. CSRF Middleware
4. Route Handler

#### Features
- **Ordered Execution**: Middleware executed in defined order
- **Error Handling**: Graceful handling of middleware failures
- **Debug Logging**: Comprehensive logging of middleware execution
- **Request Conversion**: Proper PSR-7 to internal Request conversion

## Testing Security Features

### Test Script
Run the comprehensive security test suite:

```bash
php scripts/test_security_error_handling.php
```

### Test Coverage
- **Database Connection**: Verifies database connectivity
- **Container Services**: Tests service registration and resolution
- **Security Middleware**: Tests attack pattern detection
- **Error Handling**: Tests exception catching and logging
- **CSRF Protection**: Tests token validation
- **Middleware Stack**: Tests middleware execution
- **Enhanced Logging**: Tests specialized logging methods
- **Log File Creation**: Verifies log file generation
- **Security Headers**: Tests header addition

### Manual Testing
Test attack patterns manually:

```bash
# SQL Injection (should be blocked)
curl "http://localhost:8000/test?q=union+select"

# XSS (should be blocked)
curl "http://localhost:8000/test?q=<script>alert('xss')</script>"

# Directory Traversal (should be blocked)
curl "http://localhost:8000/test?q=../../../etc/passwd"

# Rate Limiting (should be blocked after 60 requests/minute)
for i in {1..70}; do curl "http://localhost:8000/test"; done
```

## Production Considerations

### Security Headers
Ensure all security headers are properly configured for your environment:

```php
// Content Security Policy
"default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;"

// Strict Transport Security
"max-age=31536000; includeSubDomains"
```

### Rate Limiting
For production, consider using Redis for rate limiting:

```php
// Replace in-memory storage with Redis
private static array $rateLimitStore = []; // Change to Redis
```

### Logging
Configure appropriate log levels for production:

```php
// Development
$logger = new Logger($logDir, 'debug');

// Production
$logger = new Logger($logDir, 'warning');
```

### Error Handling
Disable debug mode in production:

```php
// .env
APP_DEBUG=false
APP_ENV=production
```

## Security Best Practices

### Input Validation
- Always validate and sanitize user input
- Use prepared statements for database queries
- Implement proper access controls
- Log security events for monitoring

### Session Security
- Use secure session configuration
- Implement proper session timeout
- Regenerate session IDs after login
- Use HTTP-only cookies

### Error Handling
- Never expose sensitive information in error messages
- Log errors for debugging but show user-friendly messages
- Implement proper error boundaries
- Monitor error rates and patterns

### Monitoring
- Monitor security logs regularly
- Set up alerts for suspicious activity
- Track performance metrics
- Review access logs periodically

## Compliance

### Security Standards
- **OWASP Top 10**: Protection against common vulnerabilities
- **PSR-3**: Standardized logging interface
- **PSR-7**: HTTP message interface compliance
- **PSR-15**: HTTP middleware interface compliance

### Data Protection
- **Input Sanitization**: All user input is sanitized
- **Output Encoding**: All output is properly encoded
- **Access Control**: Proper permission checking
- **Audit Logging**: Comprehensive activity logging

## Troubleshooting

### Common Issues

**Permission Denied Errors:**
```bash
sudo chown -R www-data:www-data storage/ logs/
sudo chmod -R 755 storage/ logs/
```

**Rate Limiting Too Strict:**
Adjust limits in `SecurityMiddleware`:
```php
'requests_per_minute' => 60,  // Increase if needed
'burst_limit' => 10,          // Increase if needed
```

**CSRF Token Issues:**
Ensure forms include CSRF tokens:
```html
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

**Log File Issues:**
Check log directory permissions and disk space:
```bash
ls -la storage/logs/
df -h
```

## Support

For security issues or questions:
1. Check the logs for detailed error information
2. Run the security test suite to verify functionality
3. Review the configuration files for proper settings
4. Contact the development team for assistance

Remember: Security is an ongoing process. Regularly update dependencies, monitor logs, and stay informed about new security threats.
