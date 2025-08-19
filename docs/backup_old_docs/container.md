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
# Container Component

## Overview

The Container component provides a powerful dependency injection container for managing class dependencies and performing dependency injection.

## Version
0.0.1

## Features

- Automatic dependency resolution
- Interface binding
- Singleton and transient bindings
- Service providers
- Contextual binding
- Tagging
- Extensibility

## Basic Usage

### Binding

```php
use IslamWiki\Core\Container;

$container = new Container();

// Bind an interface to an implementation
$container->bind(
    'App\Contracts\LoggerInterface',
    'App\Services\FileLogger'
);

// Bind a singleton
$container->singleton(
    'App\Database\Connection',
    function ($container) {
        return new Connection($container->make('config')['database']);
    }
);
```

### Resolving Instances

```php
// Resolve a class
$logger = $container->make('App\Services\Logger');

// Resolve with parameters
$user = $container->make('App\Models\User', ['id' => 1]);

// Call a method with dependency injection
$result = $container->call([$user, 'update'], ['data' => $data]);
```

## Service Providers

Service providers are the central place to configure your application. They register bindings and boot services.

### Creating a Service Provider

```php
namespace App\Providers;

use IslamWiki\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('logger', function ($app) {
            return new FileLogger($app['config']['logging']);
        });
    }
    
    public function boot()
    {
        // Boot services here
    }
}
```

## Advanced Features

### Contextual Binding

```php
$container->when('App\Http\Controllers\UserController')
          ->needs('App\Contracts\LoggerInterface')
          ->give('App\Services\DatabaseLogger');
```

### Tagging

```php
$container->tag(['App\Services\EmailNotifier', 'App\Services\SMSNotifier'], 'notifiers');

$notifiers = $container->tagged('notifiers');
```

## Best Practices

1. **Type Hint Dependencies** - Use constructor injection for required dependencies
2. **Use Service Providers** - Keep your container configuration organized
3. **Prefer Interface Binding** - Makes your code more testable and flexible
4. **Avoid Service Location** - Use dependency injection instead of resolving from the container directly
5. **Keep It Simple** - Only use the container when you need its features

## Version History

### 0.0.1 (2025-07-26)
- Initial implementation
- Basic container functionality
- Service provider support
- Binding and resolving instances
