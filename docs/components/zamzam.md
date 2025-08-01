# ZamZam.js Framework

ZamZam.js is a custom lightweight JavaScript framework designed specifically for IslamWiki. It provides Alpine.js-like functionality with Islamic-themed naming conventions and features.

## Overview

ZamZam.js is a reactive JavaScript framework that allows you to add interactivity to your HTML without the need for a heavy framework. It's designed to be lightweight, fast, and easy to use.

## Features

- **Reactive Data Binding**: Automatically updates the DOM when data changes
- **Event Handling**: Simple event binding with `z-click`, `z-click-away`, etc.
- **DOM Manipulation**: Show/hide elements, update text, manage classes
- **Form Handling**: Two-way data binding with `z-model`
- **Transitions**: Built-in CSS transition support
- **Islamic-themed**: Naming conventions and features inspired by Islamic culture

## Installation

ZamZam.js is included in the IslamWiki project and doesn't require external dependencies.

```html
<!-- Include ZamZam.js CSS -->
<link rel="stylesheet" href="/css/zamzam.css">

<!-- Include ZamZam.js JavaScript -->
<script defer src="/js/zamzam.js"></script>
```

## Basic Usage

### Data Binding

```html
<div z-data='{"message": "Bismillah!", "count": 0}'>
    <p z-text="message"></p>
    <p>Count: <span z-text="count"></span></p>
</div>
```

### Event Handling

```html
<div z-data='{"open": false}'>
    <button z-click="open = !open">Toggle</button>
    <div z-show="open">Content is visible!</div>
</div>
```

### Form Input

```html
<div z-data='{"name": "", "email": ""}'>
    <input type="text" z-model="name" placeholder="Your name">
    <input type="email" z-model="email" placeholder="Your email">
    <p>Hello, <span z-text="name || 'Guest'"></span>!</p>
</div>
```

## Directives

### Data and State

- `z-data`: Define component data
- `z-methods`: Define component methods

### Event Handling

- `z-click`: Handle click events
- `z-click-away`: Handle clicks outside element
- `z-submit`: Handle form submission

### DOM Manipulation

- `z-show`: Show/hide elements
- `z-if`: Conditional rendering
- `z-text`: Update text content
- `z-html`: Update HTML content
- `z-class`: Manage CSS classes
- `z-attr`: Set HTML attributes
- `z-model`: Two-way data binding for forms

## CSS Classes

ZamZam.js includes utility CSS classes for transitions and animations:

### Transitions

```css
.z-transition          /* Base transition */
.z-transition-fast     /* 75ms transition */
.z-transition-slow     /* 300ms transition */
.z-duration-200        /* 200ms duration */
.z-ease-out           /* Easing function */
```

### Animations

```css
.z-fade-in            /* Fade in animation */
.z-fade-out           /* Fade out animation */
.z-slide-up           /* Slide up animation */
.z-slide-down         /* Slide down animation */
.z-scale-in           /* Scale in animation */
.z-scale-out          /* Scale out animation */
```

### Islamic-themed Animations

```css
.z-prayer-fade        /* Prayer-themed fade animation */
.z-qibla-pulse        /* Qibla direction pulse */
```

## Examples

### Dropdown Menu

```html
<div class="dropdown" z-data='{"open": false}'>
    <button z-click="open = !open">Menu</button>
    <div z-show="open" z-click-away="open = false" class="dropdown-menu">
        <a href="/dashboard">Dashboard</a>
        <a href="/profile">Profile</a>
        <a href="/settings">Settings</a>
    </div>
</div>
```

### Form with Validation

```html
<form z-data='{"name": "", "email": "", "errors": {}}' z-submit="validateForm()">
    <input type="text" z-model="name" placeholder="Name">
    <input type="email" z-model="email" placeholder="Email">
    <div z-show="errors.name" class="error" z-text="errors.name"></div>
    <button type="submit">Submit</button>
</form>
```

### Counter with Methods

```html
<div z-data='{"count": 0}' 
     z-methods='{"increment": "function() { this.count++; }", "reset": "function() { this.count = 0; }"}'>
    <p>Count: <span z-text="count"></span></p>
    <button z-click="increment()">+</button>
    <button z-click="reset()">Reset</button>
</div>
```

### Conditional Styling

```html
<div z-data='{"isActive": false, "isError": false}'>
    <button z-click="isActive = !isActive">Toggle Active</button>
    <div z-class='{"active": isActive, "error": isError}'>
        Dynamic styling
    </div>
</div>
```

## API Reference

### ZamZam Class

```javascript
// Start ZamZam
ZamZam.start()

// Set global data
ZamZam.data('componentName', data)

// Register component
ZamZam.component('name', definition)
```

### ZamZamTransitions Class

```javascript
// Apply enter transition
ZamZamTransitions.enter(element, {
    enter: 'transition',
    enterStart: 'opacity-0 scale-95',
    enterEnd: 'opacity-100 scale-100'
})

// Apply leave transition
ZamZamTransitions.leave(element, {
    leave: 'transition',
    leaveStart: 'opacity-100 scale-100',
    leaveEnd: 'opacity-0 scale-95'
})
```

## Migration from Alpine.js

If you're migrating from Alpine.js, here are the main differences:

| Alpine.js | ZamZam.js |
|-----------|-----------|
| `x-data` | `z-data` |
| `@click` | `z-click` |
| `x-show` | `z-show` |
| `x-transition` | CSS classes + `z-transition` |
| `x-model` | `z-model` |
| `x-text` | `z-text` |
| `x-html` | `z-html` |
| `x-class` | `z-class` |

## Browser Support

ZamZam.js supports all modern browsers that support:
- ES6 Classes
- Proxy objects
- MutationObserver
- CSS transitions

## Performance

ZamZam.js is designed to be lightweight and performant:
- No external dependencies
- Minimal DOM manipulation
- Efficient reactivity system
- Small file size (~15KB minified)

## Contributing

ZamZam.js is part of the IslamWiki project. To contribute:
1. Follow the project's coding standards
2. Add tests for new features
3. Update documentation
4. Ensure Islamic-themed naming conventions

## License

ZamZam.js is part of IslamWiki and follows the same license terms. 