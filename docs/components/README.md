# IslamWiki Core Components

## 🎯 **Overview**

This directory contains documentation for the core application components that form the foundation of IslamWiki. These components provide essential functionality for the platform's operation and user experience.

---

## 🏗️ **Component Architecture**

### **Core Component System**
```
Component Architecture:
├── 📁 Asas (Foundation) - Dependency injection and service container
├── 📁 Aman (Security) - Authentication, authorization, and security
├── 📁 Sabil (Path) - Routing and request handling
├── 📁 Nizam (Order) - Application coordination and management
├── 📁 Mizan (Balance) - Database and data management
├── 📁 Tadbir (Management) - Configuration and administration
├── 📁 Rihlah (Journey) - Caching and performance
├── 📁 Sabr (Patience) - Job queues and background processing
├── 📁 Usul (Principles) - Knowledge and rule management
├── 📁 Iqra (Read) - Search and discovery
├── 📁 Bayan (Explanation) - Content formatting and presentation
├── 📁 Siraj (Light) - API management and external services
├── 📁 Shahid (Witness) - Logging and monitoring
├── 📁 Wisal (Connection) - Session and connection management
├── 📁 Safa (Purity) - CSS framework and styling
└── 📁 Marwa (Excellence) - JavaScript framework and interactivity
```

---

## 🔧 **Component Categories**

### **1. Foundation Components (Asas)**
- **Container System**: Dependency injection and service resolution
- **Service Registry**: Service registration and management
- **Configuration Loader**: Configuration file loading and parsing
- **Environment Manager**: Environment variable management

### **2. Security Components (Aman)**
- **Authentication System**: User login and session management
- **Authorization Engine**: Role-based access control
- **Input Validation**: Request data validation and sanitization
- **Security Middleware**: Security-focused request processing

### **3. Routing Components (Sabil)**
- **Route Manager**: URL routing and handler mapping
- **Middleware Pipeline**: Request/response processing chain
- **Controller Factory**: Controller instantiation and management
- **Route Caching**: Compiled route caching for performance

### **4. Application Components (Nizam)**
- **Application Bootstrap**: Application initialization and startup
- **Service Provider**: Service registration and bootstrapping
- **Event Dispatcher**: Event handling and dispatching
- **Application Lifecycle**: Application state management

### **5. Database Components (Mizan)**
- **Connection Manager**: Database connection pooling
- **Query Builder**: SQL query construction and execution
- **Migration System**: Database schema versioning
- **Data Models**: Entity and relationship management

---

## 📚 **Component Documentation**

### **Available Documentation**
- **[Foundation Components](asas/README.md)** - Core foundation and dependency injection
- **[Security Components](aman/README.md)** - Authentication and authorization
- **[Routing Components](sabil/README.md)** - URL routing and middleware
- **[Application Components](nizam/README.md)** - Application management
- **[Database Components](mizan/README.md)** - Data management and persistence

### **Component Development**
- **[Component Standards](../standards.md)** - Development standards and guidelines
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming conventions
- **[Style Guide](../guides/style-guide.md)** - Coding standards and best practices

---

## 🔍 **Component Discovery**

### **Automatic Discovery**
The component system automatically discovers and registers components based on:
- **Directory Structure**: Components are organized by Islamic naming
- **Service Providers**: Components register themselves via service providers
- **Configuration Files**: Component configuration and dependencies
- **Auto-loading**: PSR-4 compliant autoloading system

### **Component Registration**
```php
// Component registration example
class ComponentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('component.name', function ($app) {
            return new ComponentClass($app);
        });
    }
}
```

---

## 🚀 **Performance & Optimization**

### **Component Caching**
- **Route Caching**: Compiled routes cached for performance
- **Service Caching**: Service instances cached when appropriate
- **Configuration Caching**: Configuration data cached for speed
- **Template Caching**: Compiled templates cached for rendering

### **Lazy Loading**
- **On-Demand Loading**: Components loaded only when needed
- **Dependency Resolution**: Dependencies resolved at runtime
- **Memory Optimization**: Efficient memory usage patterns
- **Startup Performance**: Fast application startup times

---

## 🔒 **Security & Reliability**

### **Component Security**
- **Input Validation**: All input validated and sanitized
- **Access Control**: Role-based component access
- **Error Handling**: Secure error reporting and logging
- **Audit Trail**: Component usage tracking and logging

### **Component Reliability**
- **Error Recovery**: Graceful error handling and recovery
- **Fallback Systems**: Alternative component implementations
- **Health Monitoring**: Component health and status monitoring
- **Performance Metrics**: Component performance tracking

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - Detailed system documentation
- **[Development Standards](../standards.md)** - Development guidelines
- **[API Documentation](../api/overview.md)** - API reference

### **Development Resources**
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide
- **[Testing Guidelines](../testing/README.md)** - Testing strategies

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** Components Documentation Complete ✅ 