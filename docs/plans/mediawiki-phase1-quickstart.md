# 🚀 **MediaWiki Integration - Phase 1 Quick Start Guide**

## 🎯 **Overview**

This guide provides immediate steps to begin implementing Phase 1 of the MediaWiki integration plan. Phase 1 focuses on creating the core wiki markup system that will enable MediaWiki-style syntax in IslamWiki.

---

## ⚡ **Immediate Actions (Today)**

### **1. Set Up Development Environment**
```bash
# Navigate to your IslamWiki project
cd /var/www/html/local.islam.wiki

# Create the new extension directory
mkdir -p extensions/WikiMarkupExtension
cd extensions/WikiMarkupExtension

# Create extension structure
mkdir -p src config docs templates tests
```

### **2. Create Extension Files**
```bash
# Create the main extension file
touch WikiMarkupExtension.php
touch extension.json
touch composer.json
touch README.md
```

---

## 🔧 **Phase 1 Implementation Steps**

### **Step 1: Create Extension Configuration**

**File**: `extensions/WikiMarkupExtension/extension.json`
```json
{
    "name": "WikiMarkupExtension",
    "version": "0.0.1.0",
    "description": "MediaWiki-style markup support for IslamWiki",
    "author": "IslamWiki Development Team",
    "license": "AGPL-3.0",
    "requires": {
        "php": ">=8.1",
        "islamwiki/core": ">=0.0.1.0"
    },
    "autoload": {
        "psr-4": {
            "IslamWiki\\Extensions\\WikiMarkupExtension\\": "src/"
        }
    },
    "extensions": {
        "WikiMarkup": {
            "class": "WikiMarkupExtension",
            "priority": 10
        }
    }
}
```

### **Step 2: Create Main Extension Class**

**File**: `extensions/WikiMarkupExtension/src/WikiMarkupExtension.php`
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Wiki Markup Extension
 *
 * Provides MediaWiki-style syntax support including:
 * - Internal links: [[Page]] and [[Page|Display Text]]
 * - Templates: {{Template}} and {{Template|param1|param2}}
 * - Headers: === Header ===
 * - Lists: *, #, ;, :
 */
class WikiMarkupExtension extends Extension
{
    /**
     * @var WikiMarkupParser
     */
    private WikiMarkupParser $parser;

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->parser = new WikiMarkupParser();
        $this->registerHooks();
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hook - process wiki markup
        $hookManager->register('ContentParse', [$this, 'onContentParse'], 10);

        // Post-render hook - finalize HTML output
        $hookManager->register('ContentPostRender', [$this, 'onContentPostRender'], 10);
    }

    /**
     * Content parsing hook
     */
    public function onContentParse(string &$content): void
    {
        $content = $this->parser->parse($content);
    }

    /**
     * Post-render hook
     */
    public function onContentPostRender(string &$html): void
    {
        $html = $this->parser->postProcess($html);
    }
}
```

### **Step 3: Create Wiki Markup Parser**

**File**: `extensions/WikiMarkupExtension/src/WikiMarkupParser.php`
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

/**
 * Wiki Markup Parser
 *
 * Parses MediaWiki-style syntax and converts it to HTML
 */
class WikiMarkupParser
{
    /**
     * Parse wiki markup content
     */
    public function parse(string $content): string
    {
        $content = $this->parseInternalLinks($content);
        $content = $this->parseTemplates($content);
        $content = $this->parseHeaders($content);
        $content = $this->parseLists($content);
        
        return $content;
    }

    /**
     * Parse internal links [[Page]] and [[Page|Display Text]]
     */
    private function parseInternalLinks(string $content): string
    {
        // Pattern: [[Page]] or [[Page|Display Text]]
        $pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $pageName = trim($matches[1]);
            $displayText = isset($matches[2]) ? trim($matches[2]) : $pageName;
            
            $url = $this->generatePageUrl($pageName);
            return "<a href=\"{$url}\" class=\"wiki-link\">{$displayText}</a>";
        }, $content);
    }

    /**
     * Parse templates {{Template}} and {{Template|param1|param2}}
     */
    private function parseTemplates(string $content): string
    {
        // Pattern: {{Template}} or {{Template|param1|param2}}
        $pattern = '/\{\{([^|}]+)(?:\|([^}]+))?\}\}/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $templateName = trim($matches[1]);
            $parameters = isset($matches[2]) ? $this->parseTemplateParameters($matches[2]) : [];
            
            return $this->renderTemplate($templateName, $parameters);
        }, $content);
    }

    /**
     * Parse MediaWiki-style headers === Header ===
     */
    private function parseHeaders(string $content): string
    {
        // Pattern: === Header ===
        $content = preg_replace('/^=== (.+) ===$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^== (.+) ==$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^= (.+) =$/m', '<h1>$1</h1>', $content);
        
        return $content;
    }

    /**
     * Parse list formatting
     */
    private function parseLists(string $content): string
    {
        // Convert * to unordered lists
        $content = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ul>$0</ul>', $content);
        
        // Convert # to ordered lists
        $content = preg_replace('/^# (.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.+<\/li>\n)+/s', '<ol>$0</ol>', $content);
        
        return $content;
    }

    /**
     * Generate page URL for internal links
     */
    private function generatePageUrl(string $pageName): string
    {
        // Convert page name to URL-friendly format
        $slug = strtolower(str_replace(' ', '-', $pageName));
        return "/wiki/{$slug}";
    }

    /**
     * Parse template parameters
     */
    private function parseTemplateParameters(string $paramString): array
    {
        $parameters = [];
        $parts = explode('|', $paramString);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '=') !== false) {
                list($key, $value) = explode('=', $part, 2);
                $parameters[trim($key)] = trim($value);
            } else {
                $parameters[] = $part;
            }
        }
        
        return $parameters;
    }

    /**
     * Render template with parameters
     */
    private function renderTemplate(string $templateName, array $parameters): string
    {
        // For now, return a placeholder
        // This will be enhanced in Phase 2
        $paramString = !empty($parameters) ? '|' . implode('|', $parameters) : '';
        return "<div class=\"template-placeholder\" data-template=\"{$templateName}\">Template: {$templateName}{$paramString}</div>";
    }

    /**
     * Post-process HTML output
     */
    public function postProcess(string $html): string
    {
        // Clean up any remaining wiki markup
        // Add CSS classes for styling
        $html = str_replace('class="wiki-link"', 'class="wiki-link internal-link"', $html);
        
        return $html;
    }
}
```

### **Step 4: Create Extension Service Provider**

**File**: `extensions/WikiMarkupExtension/src/WikiMarkupServiceProvider.php`
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiMarkupExtension;

use Container;\Container

/**
 * Wiki Markup Service Provider
 */
class WikiMarkupServiceProvider
{
    /**
     * Register services with the container
     */
    public function register(Container $container): void
    {
        // Register the parser as a singleton
        $container->singleton(WikiMarkupParser::class, function () {
            return new WikiMarkupParser();
        });
    }
}
```

---

## 🧪 **Testing the Extension**

### **1. Test Basic Functionality**
```bash
# Navigate to your project
cd /var/www/html/local.islam.wiki

# Test that the extension loads
php -r "
require_once 'vendor/autoload.php';
echo 'Extension structure created successfully!';
"
```

### **2. Test Wiki Markup Parsing**
Create a test file to verify the parser works:

**File**: `extensions/WikiMarkupExtension/test_parser.php`
```php
<?php

require_once __DIR__ . '/src/WikiMarkupParser.php';

use IslamWiki\Extensions\WikiMarkupExtension\WikiMarkupParser;

$parser = new WikiMarkupParser();

$testContent = "
# Test Page

This is a test page with [[Internal Link]] and [[Another Page|Display Text]].

## Features

* List item 1
* List item 2
* List item 3

=== Subsection ===

{{Template|param1|param2}}

# Ordered list
# Second item
# Third item
";

$parsed = $parser->parse($testContent);
echo "Original:\n{$testContent}\n\n";
echo "Parsed:\n{$parsed}\n";
```

### **3. Run the Test**
```bash
cd extensions/WikiMarkupExtension
php test_parser.php
```

---

## 🔗 **Integration with Existing System**

### **1. Update EnhancedMarkdown Extension**
The WikiMarkupExtension should work alongside the existing EnhancedMarkdown extension. The processing order should be:

1. **Wiki Markup Parsing** (this extension)
2. **Markdown Processing** (EnhancedMarkdown)
3. **HTML Generation**

### **2. Hook into Content Processing**
The extension hooks into the content processing pipeline at the right points to ensure wiki markup is processed before markdown.

---

## 📋 **Phase 1 Checklist**

### **Core Parser (Week 1)**
- [ ] Create extension structure
- [ ] Implement basic parser class
- [ ] Add internal link support
- [ ] Test with simple content

### **Enhanced Features (Week 2)**
- [ ] Add template placeholder support
- [ ] Implement header parsing
- [ ] Add list formatting
- [ ] Test with complex content

### **Integration & Testing (Week 3)**
- [ ] Integrate with existing system
- [ ] Test with real wiki pages
- [ ] Performance testing
- [ ] User feedback collection

---

## 🚨 **Common Issues & Solutions**

### **Issue: Extension Not Loading**
**Solution**: Check that the extension.json file is valid and the autoload paths are correct.

### **Issue: Parser Not Working**
**Solution**: Verify that the hooks are registered correctly and the parser is being called.

### **Issue: Performance Problems**
**Solution**: The parser uses regex which can be slow for large content. Consider caching parsed results.

---

## 📚 **Next Steps**

After completing Phase 1:

1. **Update Progress Tracker**: Mark Phase 1 tasks as complete
2. **Begin Phase 2**: Start working on the template system
3. **User Testing**: Get feedback on the new wiki markup features
4. **Documentation**: Update user documentation with new syntax

---

## 🎯 **Success Criteria for Phase 1**

- [ ] Extension loads without errors
- [ ] Internal links parse correctly
- [ ] Template placeholders are recognized
- [ ] Headers render properly
- [ ] Lists format correctly
- [ ] Performance impact is minimal (<100ms per page)
- [ ] Integration with existing system works

---

**Ready to Start**: ✅  
**Estimated Time**: 3 weeks  
**Dependencies**: None  
**Next Phase**: Advanced Template System

---

**Last Updated:** 2025-01-27  
**Version:** 0.0.1.0  
**Status:** Ready for Implementation 🚀 