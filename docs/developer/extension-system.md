# IslamWiki Extension System

**Version**: 0.0.19  
**Status**: Production Ready  
**Last Updated**: 2025-07-31

## Overview

The IslamWiki Extension System provides a modular architecture for adding functionality to IslamWiki without modifying the core codebase. The system is inspired by MediaWiki's extension system but built with modern PHP practices and Islamic content focus.

## Architecture

### Core Components

#### Extension Base Class
```php
namespace IslamWiki\Core\Extensions;

abstract class Extension
{
    protected Container $container;
    protected HookManager $hookManager;
    protected bool $enabled = false;
    protected array $config = [];
    
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->hookManager = $container->get(HookManager::class);
        $this->loadExtensionInfo();
    }
    
    abstract protected function onInitialize(): void;
    abstract public function getConfig(): array;
    abstract public function isEnabled(): bool;
}
```

#### Hook Manager
```php
namespace IslamWiki\Core\Extensions\Hooks;

class HookManager
{
    private array $hooks = [];
    private array $priorities = [];
    
    public function register(string $hookName, callable $callback, int $priority = 10): void
    public function run(string $hookName, array $args = []): array
    public function getHooks(): array
}
```

#### Extension Manager
```php
namespace IslamWiki\Core\Extensions;

class ExtensionManager
{
    private Container $container;
    private HookManager $hookManager;
    private array $extensions = [];
    private array $extensionMetadata = [];
    
    public function loadExtensions(): void
    public function loadExtension(string $extensionName): bool
    public function getAvailableExtensions(): array
    public function getEnabledExtensions(): array
    public function enableExtension(string $extensionName): bool
    public function disableExtension(string $extensionName): bool
}
```

## Creating Extensions

### Extension Structure

```
extensions/YourExtension/
├── extension.json          # Extension metadata
├── YourExtension.php      # Main extension file
├── includes/              # PHP classes
├── modules/               # Frontend resources (CSS/JS)
├── templates/             # Template files
├── i18n/                  # Language files
└── sql/                   # Database patches
```

### Extension Configuration (extension.json)

```json
{
    "name": "YourExtension",
    "version": "0.0.1",
    "description": "Description of your extension",
    "author": "Your Name",
    "url": "https://islamwiki.org/extensions/YourExtension",
    "main": "YourExtension.php",
    "class": "IslamWiki\\Extensions\\YourExtension\\YourExtension",
    "type": "content",
    "requires": {
        "IslamWiki": ">= 0.0.19"
    },
    "config": {
        "enabled": false,
        "setting1": "value1",
        "setting2": "value2"
    },
    "hooks": {
        "ArticleSave": "onArticleSave",
        "ContentParse": "onContentParse"
    }
}
```

### Main Extension File

```php
<?php
declare(strict_types=1);

namespace IslamWiki\Extensions\YourExtension;

use IslamWiki\Core\Extensions\Extension;

class YourExtension extends Extension
{
    protected function onInitialize(): void
    {
        // Load configuration
        $this->loadConfiguration();
        
        // Register hooks
        $this->registerHooks();
        
        // Initialize extension-specific functionality
        $this->initializeExtension();
    }
    
    public function onArticleSave(array $articleData, array $userData): array
    {
        // Handle article save event
        return $articleData;
    }
    
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        // Parse and transform content
        return $content;
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
```

## Available Extensions

### Enhanced Markdown Extension

**Purpose**: Provides enhanced Markdown support with Islamic content syntax.

**Features**:
- Islamic syntax: `{{quran:2:255}}`, `{{hadith:bukhari:1:1}}`
- Arabic text support with RTL handling
- Smart templates for Islamic content
- Content validation for Islamic syntax

**Configuration**:
```json
{
    "enableIslamicSyntax": true,
    "enableArabicSupport": true,
    "enableTemplates": true,
    "defaultEditor": "markdown",
    "syntaxHighlighting": true
}
```

### Git Integration Extension

**Purpose**: Provides automatic version control and backup system.

**Features**:
- Automatic Git commits on content changes
- Branch-based scholarly review workflow
- Conflict resolution tools
- Remote repository synchronization

**Configuration**:
```json
{
    "enabled": false,
    "repositoryPath": "storage/git/content",
    "remoteUrl": "",
    "branch": "main",
    "autoCommit": true,
    "autoPush": true,
    "reviewWorkflow": true
}
```

## Hook System

### Available Hooks

#### Content Hooks
- `ArticleSave`: Triggered when an article is saved
- `ArticleDelete`: Triggered when an article is deleted
- `ContentParse`: Triggered when content is parsed
- `EditorInit`: Triggered when the editor is initialized

#### User Hooks
- `UserLogin`: Triggered when a user logs in
- `UserLogout`: Triggered when a user logs out
- `UserRegister`: Triggered when a user registers

#### System Hooks
- `ContentBackup`: Triggered for content backup operations
- `ReviewRequest`: Triggered for review workflow requests

### Registering Hooks

```php
protected function registerHooks(): void
{
    $hookManager = $this->getHookManager();
    
    // Register with default priority (10)
    $hookManager->register('ArticleSave', [$this, 'onArticleSave']);
    
    // Register with custom priority (higher = earlier execution)
    $hookManager->register('ContentParse', [$this, 'onContentParse'], 5);
}
```

### Hook Callback Methods

```php
public function onArticleSave(array $articleData, array $userData): array
{
    // Modify article data if needed
    $articleData['modified_by'] = $userData['id'];
    
    return $articleData;
}

public function onContentParse(string $content, string $format = 'markdown'): string
{
    // Transform content
    $content = $this->parseCustomSyntax($content);
    
    return $content;
}
```

## Extension Management

### Loading Extensions

Extensions are automatically discovered and loaded from the `extensions/` directory. Each extension must have:

1. A valid `extension.json` file
2. A main PHP file specified in the configuration
3. A class that extends `IslamWiki\Core\Extensions\Extension`

### Enabling/Disabling Extensions

```php
// Get the extension manager
$extensionManager = $container->get(ExtensionManager::class);

// Enable an extension
$extensionManager->enableExtension('GitIntegration');

// Disable an extension
$extensionManager->disableExtension('GitIntegration');

// Check if extension is enabled
$enabled = $extensionManager->isExtensionLoaded('GitIntegration');
```

### Extension Statistics

```php
$stats = $extensionManager->getStatistics();

// Returns:
[
    'total_extensions' => 2,
    'available_extensions' => ['EnhancedMarkdown', 'GitIntegration'],
    'enabled_extensions' => ['EnhancedMarkdown'],
    'extensions' => [
        'GitIntegration' => [
            'name' => 'GitIntegration',
            'version' => '0.0.1',
            'enabled' => false,
            'config' => [...]
        ]
    ]
]
```

## Security Considerations

### Extension Security

1. **Input Validation**: Always validate extension configuration
2. **Access Control**: Check user permissions before performing operations
3. **Error Handling**: Implement proper error handling and logging
4. **Resource Limits**: Be mindful of memory and processing limits

### Safe Defaults

- Extensions are disabled by default
- Configuration validation is enforced
- Error logging is comprehensive
- Access controls are implemented

## Performance Optimization

### Extension Loading

- Extensions are loaded lazily (only when needed)
- Configuration is cached for performance
- Hook registration is optimized
- Memory usage is monitored

### Best Practices

1. **Minimal Dependencies**: Keep extension dependencies minimal
2. **Efficient Hooks**: Use hooks efficiently and avoid unnecessary processing
3. **Caching**: Implement appropriate caching for extension data
4. **Error Recovery**: Implement graceful error recovery

## Testing Extensions

### Unit Testing

```php
// Test extension loading
$extensionManager = new ExtensionManager($container);
$loaded = $extensionManager->loadExtension('YourExtension');
$this->assertTrue($loaded);

// Test extension functionality
$extension = $extensionManager->getExtension('YourExtension');
$this->assertInstanceOf(YourExtension::class, $extension);
```

### Integration Testing

```php
// Test hook execution
$hookManager = $container->get(HookManager::class);
$result = $hookManager->run('ArticleSave', [$articleData, $userData]);
$this->assertIsArray($result);
```

## Troubleshooting

### Common Issues

1. **Extension Not Loading**
   - Check `extension.json` syntax
   - Verify class namespace and name
   - Check file permissions

2. **Hooks Not Executing**
   - Verify hook registration
   - Check hook callback method signature
   - Review error logs

3. **Configuration Issues**
   - Validate configuration format
   - Check configuration access methods
   - Review configuration validation

### Debugging

```php
// Enable debug logging
error_log('Extension debug: ' . json_encode($extensionData));

// Check extension status
$status = $extensionManager->getStatistics();
error_log('Extension status: ' . json_encode($status));
```

## Future Enhancements

### Planned Features

1. **Extension Marketplace**: Centralized extension distribution
2. **Extension Dependencies**: Automatic dependency resolution
3. **Extension Updates**: Automatic extension updates
4. **Extension Analytics**: Usage analytics and metrics

### Extension API

1. **REST API**: Extension management via REST API
2. **CLI Commands**: Command-line extension management
3. **Web Interface**: Web-based extension management
4. **Extension Builder**: Visual extension builder tool

---

**For more information, see the [Extension Development Guide](extension-development.md)** 