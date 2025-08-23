# Islamic Extension Template

## Overview

The Islamic Extension Template demonstrates how to properly integrate extensions with the new IslamWiki Islamic architecture. This template provides a foundation for creating modern, Islamic-named extensions that integrate seamlessly with all 16 core Islamic systems.

## Features

- **Islamic Architecture Integration**: Full integration with Foundation, Infrastructure, Application, and User Interface layers
- **Modern Extension System**: Uses the new `IslamicExtension` base class with comprehensive lifecycle management
- **Hook System**: Enhanced hook system with priority management and context passing
- **Service Integration**: Direct access to all Islamic systems (Iqra, Bayan, Siraj, Rihlah, etc.)
- **Statistics & Monitoring**: Built-in performance tracking and integration monitoring
- **Dependency Management**: Automatic dependency resolution and validation

## Architecture Integration

### Foundation Layer (أساس)
- **AsasContainer**: Dependency injection container access
- **AsasFoundation**: Foundation services integration
- **AsasBootstrap**: Application bootstrap integration

### Infrastructure Layer (سبيل, نظام, ميزان, تدبير)
- **Simplified Routing**: Route management and navigation
- **NizamApplication**: System orchestration
- **MizanDatabase**: Database management
- **TadbirConfiguration**: Configuration management

### Application Layer (أمان, وصل, صبر, أصول)
- **AmanSecurity**: Security and authentication
- **WisalSession**: Session management
- **SabrQueue**: Background processing
- **UsulKnowledge**: Business rules and validation

### User Interface Layer (إقرأ, بيان, سراج, رحلة)
- **IqraSearch**: Search and content discovery
- **BayanFormatter**: Content formatting
- **SirajAPI**: API management
- **RihlahCaching**: Caching and optimization

## Usage

### 1. Extend IslamicExtension

```php
use IslamWiki\Core\Extensions\IslamicExtension;

class YourExtension extends IslamicExtension
{
    // Implement required abstract methods
    protected function onInitializeServices(): void
    {
        // Initialize your extension services
    }

    protected function onRegisterHooks(): void
    {
        // Register your extension hooks
    }

    protected function onBoot(): void
    {
        // Boot your extension
    }

    protected function onShutdown(): void
    {
        // Shutdown your extension
    }
}
```

### 2. Configure extension.json

```json
{
    "name": "YourExtension",
    "version": "0.0.1.1",
    "layer": "user_interface",
    "dependencies": [
        "iqra.search",
        "bayan.formatter"
    ],
    "hooks": {
        "YourHook": {
            "callback": "onYourHook",
            "priority": 10
        }
    }
}
```

### 3. Access Islamic Systems

```php
// Access search system
if ($this->hasService('iqra.search')) {
    $searchService = $this->getService('iqra.search');
    $results = $searchService->search('query');
}

// Access formatter system
if ($this->hasService('bayan.formatter')) {
    $formatterService = $this->getService('bayan.formatter');
    $formatted = $formatterService->formatContent('template', $data, 'html');
}
```

## Hook System

### Registering Hooks

```php
protected function onRegisterHooks(): void
{
    $this->hooks = [
        'ContentRender' => [
            'callback' => 'onContentRender',
            'priority' => 10,
            'description' => 'Content rendering hook'
        ]
    ];
}
```

### Hook Callbacks

```php
public function onContentRender(array $context): array
{
    // Process the context
    $context['processed'] = true;
    
    // Return modified context
    return $context;
}
```

## Service Management

### Creating Services

```php
protected function onInitializeServices(): void
{
    $this->services = [
        'content_service' => $this->createContentService(),
        'integration_service' => $this->createIntegrationService()
    ];
}
```

### Service Access

```php
public function getContentService(): object
{
    return $this->services['content_service'];
}
```

## Configuration Management

### Accessing Configuration

```php
// Get extension config
$config = $this->getConfig('feature_enabled', false);

// Set extension config
$this->setConfig('feature_enabled', true);
```

### Configuration Validation

```php
protected function validateConfiguration(): void
{
    $required = ['api_key', 'endpoint'];
    
    foreach ($required as $key) {
        if (!$this->getConfig($key)) {
            throw new Exception("Missing required configuration: {$key}");
        }
    }
}
```

## Statistics & Monitoring

### Built-in Statistics

```php
// Get extension statistics
$stats = $this->getStatistics();

// Get extension-specific stats
$extensionStats = $this->getExtensionStats();
```

### Custom Statistics

```php
protected function trackPerformance(string $operation, float $time): void
{
    $this->extensionStats['performance'][$operation] = $time;
}
```

## Best Practices

### 1. Islamic Naming Conventions
- Use Arabic names for major components when appropriate
- Follow the established naming patterns in the core system
- Maintain consistency with the 16 core Islamic systems

### 2. Error Handling
- Always use try-catch blocks for external service calls
- Log errors with appropriate context
- Provide meaningful error messages

### 3. Performance
- Use caching when appropriate (RihlahCaching)
- Implement lazy loading for heavy resources
- Monitor and log performance metrics

### 4. Security
- Validate all input data
- Use the AmanSecurity system for authentication
- Follow security best practices

### 5. Integration
- Register with appropriate Islamic systems
- Use the hook system for extensibility
- Maintain backward compatibility when possible

## Migration Guide

### From Legacy Extension System

1. **Update Base Class**: Change from `Extension` to `IslamicExtension`
2. **Update Constructor**: Remove manual service initialization
3. **Implement Abstract Methods**: Add required lifecycle methods
4. **Update Configuration**: Use new extension.json format
5. **Register Services**: Use the new service registration system
6. **Update Hooks**: Use the new hook system

### Example Migration

**Before (Legacy)**:
```php
class LegacyExtension extends Extension
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->initializeServices();
    }
}
```

**After (Islamic)**:
```php
class ModernExtension extends IslamicExtension
{
    protected function onInitializeServices(): void
    {
        // Services automatically initialized
    }
}
```

## Troubleshooting

### Common Issues

1. **Service Not Found**: Ensure the service is properly registered in the container
2. **Hook Not Triggered**: Check hook registration and callback method names
3. **Configuration Missing**: Verify extension.json format and required fields
4. **Dependency Issues**: Check dependency requirements and availability

### Debug Mode

Enable debug mode in extension.json:
```json
{
    "config": {
        "debug_mode": true
    }
}
```

## Support

For questions and support:
- Check the IslamWiki documentation
- Review the core Islamic system implementations
- Consult the extension development guide
- Contact the development team

## License

This template is licensed under AGPL-3.0-only, the same license as IslamWiki. 