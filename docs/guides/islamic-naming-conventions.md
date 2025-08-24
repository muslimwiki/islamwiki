# Islamic Naming Conventions Guide

## 🕌 **Why Islamic Naming Conventions?**

### **Philosophy & Purpose**
IslamWiki uses Islamic naming conventions throughout its architecture to:

1. **Honor Islamic Heritage**: Connect the platform to Islamic values and traditions
2. **Cultural Relevance**: Make the platform more meaningful to Muslim developers and users
3. **Memorable Names**: Islamic terms are often more memorable than generic technical terms
4. **Unified Identity**: Create a cohesive, Islamic-focused development experience
5. **Educational Value**: Help developers learn Islamic terms while building the platform

### **Naming Principles**
- **Meaningful**: Each name should reflect the system's purpose
- **Authentic**: Use genuine Islamic terms, not forced translations
- **Consistent**: Follow established patterns across the system
- **Accessible**: Choose terms that are well-known in Islamic tradition
- **Professional**: Maintain professional standards while being culturally appropriate

### **Islamic Terminology Standards**
- **Salah**: Always use "salah" instead of "prayer" (the English translation)
- **Quran**: Use "Quran" (not "Koran" or other variations)
- **Hadith**: Use "Hadith" (not "Ahadith" in English contexts)
- **Hijri**: Use "Hijri" for Islamic calendar (not "Islamic calendar" when referring to the system)
- **Adhan**: Use "Adhan" (not "call to prayer")
- **Qibla**: Use "Qibla" (not "direction of prayer")

---

## 🏗️ **Complete Islamic Naming Convention List**

### **Core Systems (Already Documented)**
```
1. Container (Container) - Core foundation and dependency injection container
2. Security (Security) - Comprehensive security framework
3. API (Light/Illumination) - API management and routing system
4. Logging (Witness/Evidence) - Comprehensive logging and error handling system
5. Session (Connection) - Session management system
6. Routing (Journey) - Caching system
7. Queue (Patience/Persistence) - Job queue system
8. Knowledge (Principles/Roots) - Knowledge management system
9. Iqra (Read) - Islamic search engine
10. Bayan (Explanation/Clarification) - Content formatting system
11. Simplified Routing (Path/Way) - Advanced routing system
12. Application (System/Order) - Main application system
13. Database (Balance/Scale) - Database system
14. Configuration (Management/Planning) - Configuration management system
15. Safa (Purity/Cleanliness) - CSS framework and styling system
16. Marwa (Elevation/Excellence) - JavaScript framework and interactivity
```

### **Frontend Framework Names (CORRECTED)**
```
Safa (Purity/Cleanliness) - CSS Framework
├── Purpose: Clean, pure styling system
├── Features: Responsive design, Islamic aesthetic themes
├── Components: Layout, typography, color schemes
└── Files: safa.css, safa-components.css, safa-themes.css

Marwa (Excellence) - JavaScript Framework
├── Purpose: Enhanced user interactions and functionality
├── Features: Progressive enhancement, accessibility
├── Components: UI interactions, form handling, animations
└── Files: marwa.js, marwa-components.js, marwa-utils.js
```

---

## 📁 **File & Folder Naming Conventions**

### **Root Directory Structure**
```
local.islam.wiki/
├── 📁 asas/                    # Core foundation (was: src/)
├── 📁 aman/                    # Security system (was: security/)
├── 📁 simplified-routing/      # Routing system (was: routes/)
├── 📁 nizam/                   # Main application (was: public/)
├── 📁 tadbir/                  # Configuration (was: config/)
├── 📁 rihlah/                  # Caching system (was: cache/)
├── 📁 mizan/                   # Database system (was: database/)
├── 📁 usul/                    # Knowledge management (was: resources/)
├── 📁 sabr/                    # Job queues (was: storage/)
├── 📁 shahid/                  # Logging system (was: logs/)
├── 📁 wisal/                   # Session management (was: sessions/)
├── 📁 iqra/                    # Search engine (was: search/)
├── 📁 bayan/                   # Content formatting (was: formatting/)
├── 📁 siraj/                   # API system (was: api/)
├── 📁 safa/                    # CSS framework (was: css/)
├── 📁 marwa/                   # JavaScript framework (was: js/)
├── 📁 extensions/              # Extension system (keep as is)
├── 📁 skins/                   # Skin system (keep as is)
├── 📁 languages/               # Language files (keep as is)
├── 📁 vendor/                  # Dependencies (keep as is)
└── 📁 docs/                    # Documentation (keep as is)
```

---

## 🔧 **Additional Components Needing Islamic Naming**

### **1. Extension System Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Extension                      → RoutingExtension       → Journey (Extension)
Controller                     → SabilController       → Path (Controller)
Service                       → KnowledgeService            → Principles (Service)
Provider                      → ContainerProvider           → Container (Provider)
Model                         → DatabaseModel             → Balance (Data Model)
Widget                        → BayanWidget            → Explanation (Widget)
Migration                     → QueueMigration          → Patience (Database Migration)
Config                        → ConfigurationConfig           → Management (Configuration)
```

### **2. Database & Data Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Database                      → Database          → Balance (Database)
Query                         → IqraQuery              → Read (Query)
Connection                    → SessionConnection         → Connection (Database)
Migration                     → QueueMigration          → Patience (Migration)
Seed                          → KnowledgeSeed               → Principles (Seed Data)
Model                         → DatabaseModel             → Balance (Data Model)
Collection                    → LoggingCollection        → Witness (Data Collection)
Repository                    → BayanRepository         → Explanation (Data Access)
```

### **3. HTTP & Web Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Request                       → SabilRequest           → Path (HTTP Request)
Response                      → BayanResponse          → Explanation (HTTP Response)
Middleware                    → SecurityMiddleware         → Security (Middleware)
Route                         → SabilRoute             → Path (Route)
API                           → API               → Light (API)
Endpoint                      → APIEndpoint          → Light (API Endpoint)
```

### **4. View & Template Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
View                          → IqraView               → Read (View)
Template                      → BayanTemplate          → Explanation (Template)
Renderer                      → BayanRenderer          → Explanation (Renderer)
Theme                         → SafaTheme              → Purity (Theme)
Skin                          → SafaSkin               → Purity (Skin)
Layout                        → ApplicationLayout            → Order (Layout)
Component                     → BayanComponent         → Explanation (Component)
```

### **5. Testing & Development Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Test                          → LoggingTest             → Witness (Test)
Unit                          → KnowledgeUnit               → Principles (Unit Test)
Integration                   → SessionIntegration       → Connection (Integration)
Fixture                       → KnowledgeFixture            → Principles (Test Data)
Mock                          → LoggingMock             → Witness (Mock)
Assertion                     → LoggingAssertion        → Witness (Assertion)
```

### **6. Utility & Helper Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Utility                       → KnowledgeUtility            → Principles (Utility)
Helper                        → BayanHelper            → Explanation (Helper)
Factory                       → ContainerFactory            → Container (Factory)
Builder                       → ApplicationBuilder           → Order (Builder)
Validator                     → SecurityValidator          → Security (Validator)
Formatter                     → BayanFormatter         → Explanation (Formatter)
```

### **7. Event & Notification Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Event                         → APIEvent             → Light (Event)
Listener                      → IqraListener           → Read (Listener)
Dispatcher                    → APIDispatcher        → Light (Event Dispatcher)
Notification                  → BayanNotification      → Explanation (Notification)
Observer                      → LoggingObserver         → Witness (Observer)
```

### **8. Cache & Performance Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Cache                         → RoutingCache            → Journey (Cache)
Store                         → DatabaseStore             → Balance (Data Store)
Pool                          → SessionPool              → Connection (Connection Pool)
Queue                         → Queue              → Patience (Job Queue)
Job                           → QueueJob                → Patience (Background Job)
Worker                        → QueueWorker             → Patience (Background Worker)
```

### **9. Security & Authentication Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Auth                          → SecurityAuth               → Security (Authentication)
Guard                         → SecurityGuard              → Security (Guard)
Policy                        → KnowledgePolicy             → Principles (Policy)
Permission                    → KnowledgePermission         → Principles (Permission)
Role                          → KnowledgeRole               → Principles (Role)
Token                         → SecurityToken              → Security (Token)
Hash                          → SecurityHash               → Security (Hash)
```

### **10. File & Asset Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
File                          → IqraFile               → Read (File)
Asset                         → BayanAsset             → Explanation (Asset)
Upload                        → IqraUpload             → Read (File Upload)
Download                      → IqraDownload           → Read (File Download)
Storage                       → QueueStorage            → Patience (File Storage)
Manager                       → ConfigurationManager          → Management (Manager)
```

---

## 🔧 **Implementation Guidelines**

### **Naming Rules**
1. **Use Arabic Root Words**: Choose meaningful Arabic terms
2. **Avoid Generic Terms**: Don't use generic words like "system" or "manager"
3. **Maintain Consistency**: Use the same naming pattern across similar components
4. **Consider Pronunciation**: Choose names that are easy to pronounce
5. **Document Meanings**: Always document what each name represents

### **File Naming Examples**
```
✅ Correct:
- container.php (Container container)
- aman-authentication.php (Security authentication)
- sabil-router.php (Path router)
- tadbir-config.php (Management configuration)
- rihlah-cache.php (Journey caching)
- mizan-database.php (Balance database)
- usul-knowledge.php (Principles knowledge)
- iqra-search.php (Read search)
- bayan-formatter.php (Explanation formatter)
- api.php (Light API)
- logger.php (Witness logging)
- session.php (Connection session)
- sabr-queue.php (Patience queue)
- safa-theme.php (Purity theme)
- marwa-component.php (Excellence component)

❌ Incorrect:
- system-container.php (Generic)
- security-auth.php (Mixed languages)
- router.php (No prefix)
- config.php (No prefix)
```

### **Class Naming Examples**
```
✅ Correct:
- Container (Container container)
- SecurityAuthentication (Security authentication)
- SabilRouter (Path router)
- Configuration (Management configuration)
- RoutingCache (Journey cache)
- Database (Balance database)
- Knowledge (Principles knowledge)
- IqraSearch (Read search)
- BayanFormatter (Explanation formatter)
- API (Light API)
- Logger (Witness logger)
- Session (Connection session)
- Queue (Patience queue)
- SafaTheme (Purity theme)
- MarwaComponent (Excellence component)

❌ Incorrect:
- SystemContainer (Generic)
- SecurityAuth (Mixed)
- Router (No prefix)
- Configuration (No prefix)
```

---

## 📚 **Complete Naming Reference**

### **System Components**
```
Core Systems:
├── Container (Container) - Base system, containers, services
├── Security (Security) - Authentication, authorization, validation
├── API (Light) - API management, endpoints, routing
├── Logging (Witness) - Logging, monitoring, error handling
├── Session (Connection) - Sessions, connections, state
├── Routing (Journey) - Caching, performance, optimization
├── Queue (Patience) - Queues, background jobs, persistence
├── Knowledge (Principles) - Knowledge, rules, policies
├── Iqra (Read) - Search, discovery, reading
├── Bayan (Explanation) - Formatting, presentation, clarification
├── Simplified Routing (Path) - Routing, requests, middleware
├── Application (Order) - Application, coordination, management
├── Database (Balance) - Database, data, storage
├── Configuration (Management) - Configuration, planning, administration
├── Safa (Purity) - CSS, styling, aesthetics
└── Marwa (Excellence) - JavaScript, interactivity, enhancement
```

### **File Extensions & Patterns**
```
PHP Files:
├── asas-{component}.php        # Core foundation files
├── aman-{component}.php        # Security files
├── simplified-routing-{component}.php  # Routing files
├── tadbir-{component}.php      # Configuration files
├── rihlah-{component}.php      # Caching files
├── mizan-{component}.php       # Database files
├── usul-{component}.php        # Knowledge files
├── iqra-{component}.php        # Search files
├── bayan-{component}.php       # Formatting files
├── siraj-{component}.php       # API files
├── shahid-{component}.php      # Logging files
├── wisal-{component}.php       # Session files
├── sabr-{component}.php        # Queue files
├── nizam-{component}.php       # Application files
├── safa-{component}.php        # CSS framework files
└── marwa-{component}.php       # JavaScript framework files

CSS Files:
├── safa-{component}.css        # CSS framework files
├── safa-base.css               # Base styles
├── safa-components.css         # Component styles
├── safa-themes.css             # Theme styles
└── safa-utilities.css          # Utility classes

JavaScript Files:
├── marwa-{component}.js        # JavaScript framework files
├── marwa-core.js               # Core functionality
├── marwa-components.js         # UI components
├── marwa-themes.js             # Theme functionality
└── marwa-utilities.js          # Utility functions
```

---

## 🚀 **Migration Strategy**

### **Phase 1: Core Systems (Immediate)**
- ✅ **Already Complete**: Core system documentation
- 🔄 **Next**: Update file names and folder structure

### **Phase 2: File Structure (Short-term)**
- Rename `src/` to `asas/`
- Rename `config/` to `tadbir/`
- Rename `cache/` to `rihlah/`
- Create `safa/` and `marwa/` directories

### **Phase 3: System Components (Medium-term)**
- Rename system directories with Islamic names
- Rename content directories with Islamic names
- Update all PHP class names with Islamic prefixes

### **Phase 4: Extension & Component Names (Long-term)**
- Update all extension components with Islamic names
- Update all utility and helper classes
- Update all testing and development components

### **Phase 5: Testing & Validation (Final)**
- Test all renamed components
- Validate naming consistency
- Update developer documentation
- Create migration guides

---

## 📖 **References & Resources**

### **Islamic Terms Dictionary**
- **Container**: Container, basis, fundamental
- **Security**: Security, safety, trust
- **API**: Lamp, light, illumination
- **Logging**: Witness, evidence, martyr
- **Session**: Connection, joining, union
- **Routing**: Journey, travel, expedition
- **Queue**: Patience, perseverance, endurance
- **Knowledge**: Principles, fundamentals, methodology
- **Iqra**: Read, recite, proclaim
- **Bayan**: Explanation, clarification, statement
- **Simplified Routing**: Path, way, method
- **Application**: System, order, organization
- **Database**: Balance, scale, measure
- **Configuration**: Management, administration, planning
- **Safa**: Purity, cleanliness, clarity
- **Marwa**: Excellence, elevation, distinction

### **Additional Islamic Terms for Future Use**
- **Wisdom** (Wisdom) - AI/ML systems
- **Fadhilah** (Virtue) - Quality assurance
- **Tawheed** (Unity) - System integration
- **Ihsan** (Excellence) - Performance optimization
- **Adab** (Etiquette) - User interface guidelines
- **Piety** (Consciousness) - Monitoring systems

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Status:** Islamic Naming Conventions Guide Complete ✅ 