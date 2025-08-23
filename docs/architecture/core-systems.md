# IslamWiki Core Systems Architecture

## 🏛️ **Overview**

IslamWiki is built around a comprehensive set of core systems, each named after significant Islamic concepts and designed to handle specific aspects of the platform. These systems work together to create a robust, scalable, and Islamic-focused knowledge management platform.

---

## 🌟 **Core System Overview**

### **Foundation Systems**

#### **1. Asas (Foundation)**
- **Purpose**: Core foundation and dependency injection container
- **Responsibility**: System initialization, core services, dependency injection
- **Components**: 
  - Container management
  - Service registration
  - Core configuration
  - System bootstrapping

#### **2. Aman (Security)**
- **Purpose**: Comprehensive security framework
- **Responsibility**: Authentication, authorization, content validation
- **Components**:
  - User authentication
  - Role-based access control
  - Content security validation
  - Rate limiting
  - Security monitoring

#### **3. Siraj (Light/Illumination)**
- **Purpose**: API management and routing system
- **Responsibility**: API endpoints, API routing, API management
- **Components**:
  - API endpoint management
  - API routing
  - API versioning
  - API documentation
  - API security

### **Content Management Systems**

#### **4. Shahid (Witness/Evidence)**
- **Purpose**: Comprehensive logging and error handling system
- **Responsibility**: System logging, error handling, monitoring
- **Components**:
  - System logging
  - Error handling
  - Performance monitoring
  - Audit trails
  - Debug information

#### **5. Wisal (Connection)**
- **Purpose**: Session management system
- **Responsibility**: User sessions, connections, state management
- **Components**:
  - Session handling
  - User connections
  - State persistence
  - Connection pooling
  - Session security

#### **6. Rihlah (Journey)**
- **Purpose**: Caching system
- **Responsibility**: Multi-level caching, performance optimization
- **Components**:
  - Page caching
  - Object caching
  - Route caching
  - Template caching
  - Cache invalidation

### **Knowledge Systems**

#### **7. Sabr (Patience/Persistence)**
- **Purpose**: Job queue system
- **Responsibility**: Background processing, task management, job queues
- **Components**:
  - Job queues
  - Background processing
  - Task scheduling
  - Progress monitoring
  - Error handling

#### **8. Usul (Principles/Roots)**
- **Purpose**: Knowledge management system
- **Responsibility**: Knowledge organization, content principles, knowledge structure
- **Components**:
  - Knowledge organization
  - Content principles
  - Knowledge structure
  - Content management
  - Knowledge discovery

#### **9. Iqra (Read)**
- **Purpose**: Islamic search engine
- **Responsibility**: Content search, Islamic content discovery, search optimization
- **Components**:
  - Full-text search
  - Islamic content search
  - Search indexing
  - Knowledge discovery
  - Search optimization

#### **10. Bayan (Explanation/Clarification)**
- **Purpose**: Content formatting system
- **Responsibility**: Content formatting, text processing, content presentation
- **Components**:
  - Text formatting
  - HTML formatting
  - Markdown processing
  - Content presentation
  - Format validation

### **Infrastructure Systems**

#### **11. Sabil (Path/Way)**
- **Purpose**: Advanced routing system
- **Responsibility**: URL routing, request processing, middleware management
- **Components**:
  - URL routing
  - Request handling
  - Middleware stack
  - Route optimization
  - Request validation

#### **12. Nizam (System/Order)**
- **Purpose**: Main application system
- **Responsibility**: Application coordination, system integration, main entry point
- **Components**:
  - Application coordination
  - System integration
  - Main entry point
  - Application lifecycle
  - System management

#### **13. Mizan (Balance/Scale)**
- **Purpose**: Database system
- **Responsibility**: Database management, data storage, data optimization
- **Components**:
  - Database management
  - Data storage
  - Data optimization
  - Database scaling
  - Data integrity

#### **14. Tadbir (Management/Planning)**
- **Purpose**: Configuration management system
- **Responsibility**: System configuration, settings management, configuration optimization
- **Components**:
  - System configuration
  - Settings management
  - Configuration optimization
  - Environment management
  - Configuration validation

### **Frontend Framework Systems**

#### **15. Safa (Purity/Cleanliness)**
- **Purpose**: CSS framework and styling system
- **Responsibility**: Clean, pure styling, responsive design, Islamic aesthetic themes
- **Components**:
  - Base styles and reset
  - Component styling
  - Theme variations
  - Responsive design
  - Islamic aesthetic themes
  - Layout utilities
  - Typography system
  - Color schemes

#### **16. Marwa (Elevation/Excellence)**
- **Purpose**: JavaScript framework and interactivity system
- **Responsibility**: Enhanced user interactions, progressive enhancement, accessibility
- **Components**:
  - Core functionality
  - UI components
  - Event handling
  - Form management
  - Theme switching
  - Progressive enhancement
  - Accessibility features
  - Animation system

---

## 🔄 **System Interactions**

### **Data Flow**
```
User Request → Simplified Routing → Aman (Security) → 
Content Request → Iqra (Search) → Usul (Knowledge) → 
Response → Rihlah (Caching) → User
```

### **Authentication Flow**
```
Login Request → Aman (Security) → Wisal (Session) → 
User Validation → Asas (Foundation) → Response
```

### **Content Processing Flow**
```
Content Input → Shahid (Logging) → Usul (Knowledge) → 
Storage → Mizan (Database) → Iqra (Search) → Bayan (Formatting)
```

### **Frontend Rendering Flow**
```
Content Data → Usul (Knowledge) → Safa (Styling) → 
Marwa (Interactivity) → User Interface
```

---

## 🏗️ **System Architecture**

### **Layer Structure**
```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐         │
│  │  Bayan  │ │  Siraj  │ │  Rihlah │ │  Safa   │         │
│  │(Explain)│ │ (Light) │ │(Journey)│ │(Purity) │         │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘         │
├─────────────────────────────────────────────────────────────┤
│                   Application Layer                         │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐         │
│  │   Aman  │ │  Wisal  │ │  Sabr   │ │  Usul   │         │
│  │(Security)│ │(Session)│ │(Patience)│ │(Principles)│     │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘         │
├─────────────────────────────────────────────────────────────┤
│                  Domain Layer                              │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐         │
│  │  Sabil  │ │  Nizam  │ │  Mizan  │ │ Tadbir  │         │
│  │ (Path)  │ │ (Order) │ │(Balance)│ │(Manage) │         │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘         │
├─────────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                       │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐         │
│  │  Asas   │ │  Iqra   │ │ Marwa   │ │ Shahid  │         │
│  │(Foundation)│(Search)│(Excellence)│(Witness)│         │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### **System Dependencies**
```
Asas (Foundation)
├── Aman (Security)
├── Simplified Routing
├── Nizam (Application)
└── Mizan (Database)

Aman (Security)
├── Wisal (Sessions)
├── Shahid (Logging)
└── Usul (Knowledge)

Simplified Routing
├── Bayan (Explanation)
├── Siraj (API)
└── Rihlah (Caching)

Nizam (Application)
├── Tadbir (Configuration)
├── Safa (Styling)
└── Marwa (Interactivity)

Frontend Systems
├── Safa (CSS) - Independent styling system
├── Marwa (JS) - Depends on Safa for styling
└── Both integrate with Usul for knowledge
```

---

## 🚀 **System Features**

### **Performance Features**
- **Rihlah**: Multi-level caching system
- **Simplified Routing**: Optimized routing and request handling
- **Sabr**: Asynchronous task processing
- **Mizan**: Database optimization and scaling

### **Security Features**
- **Aman**: Multi-layer security framework
- **Shahid**: Comprehensive logging and monitoring
- **Usul**: Knowledge-based security policies
- **Wisal**: Secure session management

### **Content Features**
- **Iqra**: Advanced Islamic search engine
- **Bayan**: Comprehensive content formatting
- **Siraj**: API-first content delivery
- **Usul**: Knowledge organization and management

### **Frontend Features**
- **Safa**: Clean, responsive CSS framework with Islamic themes
- **Marwa**: Progressive JavaScript enhancement with accessibility
- **Nizam**: Optimized application coordination
- **Tadbir**: Flexible configuration management

---

## 🔧 **Configuration**

### **System Configuration**
Each system can be configured independently through the main configuration files:

```php
// LocalSettings.php
$wgAsasConfig = [
    'debug' => true,
    'environment' => 'development'
];

$wgAmanConfig = [
    'authentication' => 'database',
    'session_timeout' => 3600
];

$wgSabilConfig = [
    'cache_routes' => true,
    'optimize_requests' => true
];
```

### **Frontend Configuration**
```php
$wgSafaConfig = [
    'theme' => 'islamic',
    'responsive' => true,
    'rtl_support' => true
];

$wgMarwaConfig = [
    'progressive_enhancement' => true,
    'accessibility' => true,
    'animations' => true
];
```

### **Performance Configuration**
```php
$wgRihlahConfig = [
    'caching' => true,
    'cache_driver' => 'redis',
    'cache_ttl' => 3600
];

$wgSabrConfig = [
    'queue_driver' => 'redis',
    'max_workers' => 10
];
```

---

## 📊 **Monitoring and Metrics**

### **System Health Monitoring**
- **Asas**: System stability and core services
- **Aman**: Security events and authentication
- **Simplified Routing**: Routing performance and request handling
- **Rihlah**: Caching performance and optimization

### **Frontend Performance Monitoring**
- **Safa**: CSS loading and rendering performance
- **Marwa**: JavaScript execution and interaction performance
- **Nizam**: Application coordination and performance
- **Iqra**: Search performance and optimization

### **Performance Metrics**
- Response times
- Throughput
- Resource utilization
- Error rates
- User satisfaction
- Frontend performance scores

### **Quality Metrics**
- Content accuracy
- User engagement
- Content completeness
- System reliability
- Frontend accessibility scores

---

## 🔮 **Future Enhancements**

### **AI Integration**
- **Iqra**: AI-powered search and recommendations
- **Bayan**: AI-generated content formatting
- **Marwa**: AI-driven user interaction optimization

### **Blockchain Integration**
- **Shahid**: Blockchain-based logging and verification
- **Aman**: Decentralized authentication
- **Asas**: Immutable system foundation

### **Microservices Architecture**
- **Nizam**: Service orchestration
- **Rihlah**: Service performance monitoring
- **Tadbir**: Service management and deployment

### **Advanced Frontend Features**
- **Safa**: Advanced CSS-in-JS, CSS custom properties
- **Marwa**: Web Components, Progressive Web App features
- **Nizam**: Advanced application analytics

---

## 📚 **Related Documentation**

- [Architecture Overview](overview.md) - High-level architecture overview
- [Hybrid Architecture](hybrid-architecture.md) - Architecture philosophy
- [Islamic Naming Conventions](../guides/islamic-naming-conventions.md) - Naming conventions guide
- [Islamic Naming Implementation](../guides/islamic-naming-implementation.md) - Implementation guide
- [System Structure](structure.md) - File and directory structure
- [Security Architecture](security.md) - Security implementation
- [Performance Architecture](performance.md) - Performance optimization
- [Database Architecture](database.md) - Database design and optimization

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Status:** Core Systems Architecture Complete ✅ 