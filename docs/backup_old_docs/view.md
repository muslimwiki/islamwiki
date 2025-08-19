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
# View Component

## Overview

The View component provides a simple and powerful templating system for rendering PHP templates with data.

## Version
0.0.1

## Features

- Template inheritance
- Sections and layouts
- Template variables
- Template includes
- Template caching
- Extensible with custom directives

## Basic Usage

### Rendering a View

```php
use IslamWiki\Core\View\View;

// Create a new view instance
$view = new View('welcome', ['name' => 'John']);

// Render the view
$html = $view->render();

// Or use the global helper
$html = view('welcome', ['name' => 'John']);
```

### Template Inheritance

**Layout (layouts/app.twig):**
```twig
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}Default Title{% endblock %}</title>
</head>
<body>
    <div class="container">
        {% block content %}{% endblock %}
    </div>
</body>
</html>
```

**View (welcome.twig):**
```twig
{% extends 'layouts/app' %}

{% block title %}Welcome{% endblock %}

{% block content %}
    <h1>Hello, {{ name }}!</h1>
    <p>Welcome to our application.</p>
{% endblock %}
```

## View Composers

View composers are callbacks or class methods that are called when a view is rendered.

```php
// In a service provider
View::composer('profile', function ($view) {
    $view->with('user', Auth::user());
});

// Or using a class
View::composer('dashboard', 'App\Http\View\Composers\DashboardComposer');
```

## View Creators

View creators are similar to composers but are called immediately when the view is instantiated.

```php
View::creator('profile', function ($view) {
    $view->with('createdAt', now());
});
```

## Sharing Data with All Views

```php
// In a service provider
View::share('appName', 'IslamWiki');
```

## View Namespaces

You can organize your views into namespaces:

```php
// Register a view namespace
View::addNamespace('admin', 'resources/views/admin');

// Use the namespaced view
return view('admin::dashboard');
```

## Best Practices

1. **Keep Logic Out of Views** - Views should only contain presentation logic
2. **Use Template Inheritance** - Reduce code duplication with layouts
3. **Use View Composers** - Keep your controllers lean
4. **Cache Expensive Operations** - Cache views that require heavy processing
5. **Use Subdirectories** - Organize your views into logical directories

## Version History

### 0.0.1 (2025-07-26)
- Initial implementation
- Basic template rendering
- Template inheritance
- View composers and creators
