# Enhanced Error Pages System

## Overview

The IslamWiki application now features a completely redesigned error handling system with beautiful, informative, and user-friendly error pages. This system provides comprehensive error information while maintaining a professional and engaging user experience.

## ✨ Key Features

### 🎨 Beautiful Design
- **Modern Gradient Backgrounds**: Eye-catching color schemes that vary by error type
- **Smooth Animations**: Floating particles, bouncing icons, and hover effects
- **Professional Typography**: Inter font family with proper hierarchy and spacing
- **Responsive Layout**: Works perfectly on all device sizes
- **Interactive Elements**: Hover effects, transitions, and smooth animations

### 📊 Rich Information Display
- **Request Information**: URL, method, IP address, user agent, timestamp
- **Server Information**: PHP version, server software, memory usage, limits
- **Context-Aware Suggestions**: Helpful tips based on the specific error type
- **Enhanced Debug Information**: Source code context, stack traces, server environment

### 🔧 Smart Error Handling
- **Status Code Specific**: Different colors, icons, and suggestions for each error type
- **Contextual Help**: Relevant suggestions based on the error encountered
- **Professional Messaging**: Clear, helpful error descriptions
- **Actionable Buttons**: Easy navigation to homepage, pages, or back button

## 🎯 Error Types Supported

### 400 - Bad Request
- **Icon**: 🔴
- **Color**: #e74c3c (Red)
- **Suggestions**: Check URL typos, verify parameters, ensure correct HTTP method

### 401 - Unauthorized
- **Icon**: 🚫
- **Color**: #3498db (Blue)
- **Suggestions**: Verify API keys, check credentials, ensure valid session

### 403 - Forbidden
- **Icon**: 🚫
- **Color**: #e67e22 (Orange)
- **Suggestions**: Review permissions, check access rights, verify user role

### 404 - Page Not Found
- **Icon**: 🔍
- **Color**: #2ecc71 (Green)
- **Suggestions**: Check URL for typos, use search function, contact support

### 405 - Method Not Allowed
- **Icon**: ⚠️
- **Color**: #f1c40f (Yellow)
- **Suggestions**: Verify HTTP method, check endpoint support, review API documentation

### 429 - Too Many Requests
- **Icon**: ⚠️
- **Color**: #f39c12 (Orange)
- **Suggestions**: Wait before retrying, check rate limits, reduce request frequency

### 500 - Internal Server Error
- **Icon**: 💥
- **Color**: #c0392b (Dark Red)
- **Suggestions**: Try refreshing, wait and retry, contact support if persistent

### 502 - Bad Gateway
- **Icon**: 💥
- **Color**: #e67e22 (Orange)
- **Suggestions**: Check upstream servers, verify configuration, retry later

### 503 - Service Unavailable
- **Icon**: 💥
- **Color**: #3498db (Blue)
- **Suggestions**: Check maintenance status, wait and retry, verify service status

### 504 - Gateway Timeout
- **Icon**: ⏰
- **Color**: #f39c12 (Orange)
- **Suggestions**: Check upstream servers, verify configuration, retry later

## 🏗️ Architecture

### ErrorHandlingMiddleware
The main middleware that catches and processes all exceptions:

```php
class ErrorHandlingMiddleware
{
    private function renderErrorPage(int $statusCode, string $message, ?Throwable $exception = null): string
    {
        $title = $this->getErrorTitle($statusCode);
        $icon = $this->getErrorIcon($statusCode);
        $color = $this->getErrorColor($statusCode);
        $suggestions = $this->getErrorSuggestions($statusCode);
        
        // Render beautiful error page with all information
    }
}
```

### Twig Templates
Enhanced Twig templates for consistent error page rendering:

- `resources/views/errors/404.twig` - Page not found
- `resources/views/errors/500.twig` - Internal server error
- Additional templates for other status codes

### Fallback System
PHP-based fallback rendering when Twig is not available, ensuring error pages always display.

## 🎨 Design Elements

### Color Scheme
Each error type has its own color palette:
- **Primary Color**: Main accent color for the error type
- **Secondary Color**: Darker shade for gradients and accents
- **Accent Colors**: Complementary colors for buttons and highlights

### Typography
- **Font Family**: Inter (Google Fonts) with system fallbacks
- **Font Weights**: 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-bold), 700 (Bold)
- **Hierarchy**: Clear visual hierarchy with proper spacing and sizing

### Animations
- **Floating Particles**: Subtle background animation for visual interest
- **Bouncing Icons**: Playful icon animations that draw attention
- **Hover Effects**: Smooth transitions on interactive elements
- **Loading States**: Visual feedback for user interactions

### Layout
- **Grid System**: Responsive grid layout for information cards
- **Card Design**: Clean, modern card components with hover effects
- **Spacing**: Consistent spacing using a modular scale
- **Shadows**: Subtle shadows for depth and visual hierarchy

## 📱 Responsive Design

### Mobile-First Approach
- **Breakpoints**: 768px for tablet/desktop transition
- **Grid Layout**: Single column on mobile, multi-column on larger screens
- **Touch Targets**: Properly sized buttons and interactive elements
- **Typography**: Readable font sizes on all devices

### Adaptive Elements
- **Container Width**: Responsive container with appropriate margins
- **Button Layout**: Stacked buttons on mobile, inline on desktop
- **Information Cards**: Full-width cards on mobile, grid layout on desktop
- **Spacing**: Adjusted padding and margins for different screen sizes

## 🐛 Debug Information

### Development Mode
When `APP_DEBUG=true`, error pages include:

#### Exception Details
- Exception class name and message
- File path and line number
- Error code and context

#### Source Code Context
- Source code around the error line
- Syntax highlighting for the error line
- Line numbers and file information

#### Request Information
- Complete request details
- Headers and parameters
- Client information

#### Server Environment
- PHP configuration
- Server software and version
- Memory usage and limits
- Loaded extensions

#### Stack Trace
- Complete exception stack trace
- Formatted for readability
- Scrollable container for long traces

### Production Mode
When `APP_DEBUG=false`, error pages show:
- User-friendly error messages
- Helpful suggestions
- Navigation options
- No technical details

## 🧪 Testing

### Test Script
Run the error pages test suite:

```bash
php maintenance/debug/test_error_pages.php
```

### Manual Testing
Test different error scenarios:

```bash
# Test 404 error
curl "http://localhost:8000/nonexistent-page"

# Test 500 error
curl "http://localhost:8000/test/error"

# Test rate limiting
for i in {1..70}; do curl "http://localhost:8000/test"; done
```

### Error Simulation
Simulate different error conditions in your code:

```php
// Test exception handling
throw new RuntimeException('Test exception');

// Test HTTP exceptions
throw new HttpException(404, 'Page not found');
throw new HttpException(403, 'Access denied');
throw new HttpException(429, 'Rate limit exceeded');
```

## 🔧 Configuration

### Environment Variables
```bash
# Enable debug mode for detailed error information
APP_DEBUG=true

# Set application environment
APP_ENV=development
```

### Customization
Error pages can be customized by:

1. **Modifying Templates**: Edit Twig templates in `resources/views/errors/`
2. **Updating Middleware**: Modify `ErrorHandlingMiddleware` for custom logic
3. **Adding New Error Types**: Extend the error handling system for custom status codes
4. **Styling Changes**: Update CSS in the error page renderers

## 📊 Performance Considerations

### Optimizations
- **Minimal Dependencies**: Error pages render without external dependencies
- **Efficient Rendering**: Optimized HTML generation for fast display
- **Conditional Debug Info**: Debug information only loads when needed
- **Responsive Images**: Optimized for different screen densities

### Caching
- **No Caching**: Error pages are never cached to ensure fresh information
- **Dynamic Content**: All information is generated dynamically
- **Real-time Data**: Server information is always current

## 🚀 Future Enhancements

### Planned Features
- **Internationalization**: Multi-language error page support
- **Custom Themes**: User-selectable error page themes
- **Analytics Integration**: Error tracking and reporting
- **A/B Testing**: Different error page designs for optimization

### Extensibility
The system is designed to be easily extensible:
- **Plugin System**: Add custom error handlers
- **Template Engine**: Support for different template engines
- **Custom Styling**: Easy theme customization
- **Error Reporting**: Integration with external error reporting services

## 📚 Best Practices

### Error Handling
- **Always Catch Exceptions**: Never let exceptions bubble up to users
- **Log Everything**: Log all errors with full context
- **User-Friendly Messages**: Show helpful messages to users
- **Debug Information**: Include debug info only in development

### User Experience
- **Clear Messaging**: Use simple, understandable language
- **Helpful Actions**: Provide clear next steps for users
- **Consistent Design**: Maintain consistent styling across error types
- **Accessibility**: Ensure error pages are accessible to all users

### Performance
- **Fast Rendering**: Error pages should load quickly
- **Minimal Dependencies**: Reduce external dependencies
- **Efficient Logging**: Log errors without impacting performance
- **Graceful Degradation**: Handle errors gracefully in all scenarios

## 🔗 Related Documentation

- [Error Handling System](../components/error-handling.md)
- [Middleware Documentation](../components/middleware.md)
- [Template System](../components/templates.md)
- [Security Best Practices](../security/README.md)

---

*This enhanced error page system provides a professional, informative, and user-friendly experience for all error scenarios in the IslamWiki application.* 