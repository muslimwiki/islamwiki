# IslamWiki Controllers

## 🎯 **Overview**

This directory contains documentation for the controller layer of IslamWiki, which handles HTTP requests, processes business logic, and returns appropriate responses. Controllers follow the MVC (Model-View-Controller) pattern and implement Islamic naming conventions.

---

## 🏗️ **Controller Architecture**

### **Controller Hierarchy**
```
Controller Architecture:
├── 📁 Base Controllers - Abstract base classes and interfaces
├── 📁 Web Controllers - HTTP request handling controllers
├── 📁 API Controllers - REST API endpoint controllers
├── 📁 Admin Controllers - Administrative functionality controllers
├── 📁 Extension Controllers - Extension-specific controllers
└── 📁 Custom Controllers - Application-specific controllers
```

### **Controller Responsibilities**
- **Request Handling**: Process incoming HTTP requests
- **Input Validation**: Validate and sanitize request data
- **Business Logic**: Coordinate between models and services
- **Response Generation**: Generate appropriate HTTP responses
- **Error Handling**: Handle errors and exceptions gracefully

---

## 🔧 **Controller Types**

### **1. Base Controllers**
- **AbstractController**: Base controller with common functionality
- **ApiController**: Base controller for API endpoints
- **AdminController**: Base controller for administrative functions
- **ExtensionController**: Base controller for extensions

### **2. Web Controllers**
- **HomeController**: Homepage and main site functionality
- **PageController**: Wiki page management and display
- **UserController**: User account management
- **SearchController**: Search functionality and results

### **3. API Controllers**
- **QuranApiController**: Quran-related API endpoints
- **HadithApiController**: Hadith-related API endpoints
- **SalahApiController**: Salah time API endpoints
- **UserApiController**: User management API endpoints

### **4. Admin Controllers**
- **AdminDashboardController**: Administrative dashboard
- **UserManagementController**: User administration
- **ContentModerationController**: Content moderation tools
- **SystemSettingsController**: System configuration

---

## 📝 **Controller Implementation**

### **Basic Controller Structure**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;

/**
 * Example Controller - Demonstrates controller implementation
 * 
 * @package IslamWiki\Http\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ExampleController extends AbstractController
{
    private TwigRenderer $renderer;
    
    public function __construct(TwigRenderer $renderer)
    {
        $this->renderer = $renderer;
    }
    
    /**
     * Display the example page
     */
    public function index(Request $request): Response
    {
        $data = [
            'title' => 'Example Page',
            'content' => 'This is an example controller method'
        ];
        
        $html = $this->renderer->render('example/index.twig', $data);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    /**
     * Process form submission
     */
    public function store(Request $request): Response
    {
        // Validate input
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email'
        ]);
        
        // Process data
        $data = $request->getParsedBody();
        
        // Return response
        return new Response(json_encode(['success' => true]), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
```

### **Controller Method Patterns**
```php
// Index method - Display list or main page
public function index(Request $request): Response

// Show method - Display single item
public function show(Request $request, int $id): Response

// Create method - Show creation form
public function create(Request $request): Response

// Store method - Save new item
public function store(Request $request): Response

// Edit method - Show edit form
public function edit(Request $request, int $id): Response

// Update method - Update existing item
public function update(Request $request, int $id): Response

// Destroy method - Delete item
public function destroy(Request $request, int $id): Response
```

---

## 🚀 **Controller Features**

### **Request Processing**
- **Input Validation**: Automatic request validation
- **Data Sanitization**: XSS and injection prevention
- **File Uploads**: Secure file handling
- **CSRF Protection**: Cross-site request forgery prevention

### **Response Generation**
- **HTML Responses**: Twig template rendering
- **JSON Responses**: API endpoint responses
- **File Downloads**: Secure file serving
- **Redirects**: HTTP redirect responses

### **Error Handling**
- **Exception Handling**: Graceful error processing
- **Validation Errors**: User-friendly error messages
- **Logging**: Comprehensive error logging
- **Fallback Responses**: Graceful degradation

---

## 🔒 **Security Considerations**

### **Input Security**
- **Validation**: All input validated and sanitized
- **Authentication**: User authentication required where appropriate
- **Authorization**: Role-based access control
- **Rate Limiting**: Request rate limiting and throttling

### **Output Security**
- **XSS Prevention**: Output escaping and sanitization
- **CSRF Protection**: Token-based request validation
- **Content Security**: Content Security Policy headers
- **Secure Headers**: Security-focused HTTP headers

---

## 📚 **Controller Documentation**

### **Available Controllers**
- **[Home Controller](home/README.md)** - Homepage and main site
- **[Page Controller](page/README.md)** - Wiki page management
- **[User Controller](user/README.md)** - User account management
- **[Search Controller](search/README.md)** - Search functionality
- **[Admin Controller](admin/README.md)** - Administrative functions

### **Controller Development**
- **[Controller Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🧪 **Testing Controllers**

### **Unit Testing**
```php
class ExampleControllerTest extends TestCase
{
    public function testIndexMethodReturnsValidResponse(): void
    {
        $controller = new ExampleController($this->mockRenderer());
        $request = $this->createMockRequest();
        
        $response = $controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContains('Example Page', $response->getBody());
    }
}
```

### **Integration Testing**
- **Request Processing**: Test complete request flow
- **Response Validation**: Verify response format and content
- **Error Handling**: Test error scenarios and responses
- **Security Testing**: Validate security measures

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Models Documentation](../models/README.md)** - Data models
- **[Views Documentation](../views/README.md)** - Template system

### **Development Resources**
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Controllers Documentation Complete ✅ 