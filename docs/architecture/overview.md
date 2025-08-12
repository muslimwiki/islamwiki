# Architecture Overview

IslamWiki is built with a modern, modular architecture that emphasizes clean code, dependency injection, and progressive enhancement.

## Core Principles

### 1. **Modular Design**
- Service providers for modular functionality
- Dependency injection for loose coupling
- PSR-7 compatible HTTP handling
- Clean separation of concerns

### 2. **Progressive Enhancement**
- Server-side rendering with Twig templates
- ZamZam.js for lightweight interactivity
- Works without JavaScript
- Responsive design for all devices

### 3. **Development Friendly**
- Comprehensive error handling and logging
- Hot reloading in development mode
- Detailed debug information
- Easy testing and debugging

## Architecture Layers

```
┌─────────────────────────────────────┐
│           Presentation              │
│  (ZamZam.js + Twig Templates)     │
├─────────────────────────────────────┤
│           Controllers              │
│     (Request/Response Handling)    │
├─────────────────────────────────────┤
│           Services                 │
│    (Business Logic + Models)       │
├─────────────────────────────────────┤
│         Infrastructure             │
│  (Routing + DI + Error Handling)  │
└─────────────────────────────────────┘
```

## Key Components

### Application Bootstrap
The `Application` class serves as the main entry point and orchestrates the entire application lifecycle:

```php
$app = new \IslamWiki\Core\Application($basePath);
$app->bootstrap();
```

### Service Container
The dependency injection container manages all service instances and their dependencies:

```php
$container = $app->getContainer();
$router = $container->get('router');
$view = $container->get('view');
```

### Routing System
FastRouter provides high-performance routing with dependency injection:

```php
$router->get('/', 'HomeController@index');
$router->get('/dashboard', 'DashboardController@index');
```

### Template Engine
TwigRenderer handles server-side template rendering with caching:

```php
$content = $this->getView()->render('pages/home', $data);
```

## Request Flow

1. **Entry Point**: `public/index.php` receives HTTP request
2. **Bootstrap**: Application initializes services and container
3. **Routing**: FastRouter matches request to controller action
4. **Controller**: Controller handles request and returns response
5. **Template**: TwigRenderer renders template with data
6. **Response**: HTTP response sent to client

## Service Providers

Service providers register and configure services with the container:

- **ViewServiceProvider**: Configures TwigRenderer
- **LoggingServiceProvider**: Sets up logging system
- **Future providers**: Database, cache, authentication, etc.

## Error Handling

Comprehensive error handling at multiple levels:

- **PHP Errors**: Custom error handler with logging
- **Exceptions**: Exception handler with detailed output
- **HTTP Errors**: Custom 404/500 pages
- **Development**: Detailed debug information

## Development vs Production

### Development Mode
- Error display enabled
- Twig cache disabled
- Detailed logging
- Debug information

### Production Mode
- Error display disabled
- Twig cache enabled
- Minimal logging
- Generic error pages

## Technology Stack

### Backend
- **PHP 8.1+**: Modern PHP with strict typing
- **FastRoute**: High-performance routing
- **Twig**: Server-side templating
- **PSR-7**: HTTP message interfaces
- **Composer**: Dependency management

### Frontend
- **ZamZam.js**: Lightweight JavaScript framework
- **Modern CSS**: Responsive design
- **Progressive Enhancement**: Works without JavaScript

### Development
- **Error Handling**: Comprehensive logging
- **Service Providers**: Modular architecture
- **Dependency Injection**: Clean, testable code

## File Structure

```
islamwiki/
├── public/                 # Web server document root
│   └── index.php          # Application entry point
├── src/                   # Application source code
│   ├── Core/             # Core framework components
│   │   ├── NizamApplication.php
│   │   ├── Container/
│   │   │   └── AsasContainer.php
│   │   ├── Routing/
│   │   ├── Http/
│   │   ├── View/
│   │   ├── Error/
│   │   └── Logging/
│   ├── Http/             # HTTP layer
│   │   └── Controllers/
│   ├── Providers/        # Service providers
│   └── Models/           # Data models
├── resources/            # Application resources
│   └── views/           # Twig templates
├── routes/              # Route definitions
├── storage/             # Application storage
│   ├── logs/           # Log files
│   └── framework/      # Framework cache
├── docs/               # Documentation
└── maintenance/tests/  # Test files
```

## Configuration

Configuration is handled through environment variables and service providers:

```php
// Environment variables
$_ENV['APP_ENV'] = 'development';
$_ENV['DB_CONNECTION'] = 'mysql';

// Service provider configuration
$viewServiceProvider = new ViewServiceProvider();
$viewServiceProvider->register($container);
```

## Security Considerations

- **Input Validation**: All user input validated
- **Output Escaping**: Twig auto-escaping enabled
- **Error Handling**: No sensitive information in error messages
- **File Permissions**: Proper file permissions set
- **HTTPS Ready**: Prepared for HTTPS deployment

## Performance

- **Routing**: FastRoute for high-performance routing
- **Caching**: Twig template caching in production
- **Minimal Dependencies**: Lightweight framework
- **Optimized Assets**: CSS and JS optimization ready

## Extensibility

The architecture is designed for easy extension:

- **Service Providers**: Add new services easily
- **Controllers**: Simple controller pattern
- **Templates**: Twig extension system
- **Frontend**: ZamZam.js component system
- **Routing**: Easy route registration

## Testing Strategy

- **Unit Tests**: Individual component testing
- **Integration Tests**: Service interaction testing
- **Feature Tests**: End-to-end functionality testing
- **Frontend Tests**: ZamZam.js component testing

This architecture provides a solid foundation for building a modern, scalable wiki system while maintaining simplicity and developer productivity.
