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
1. Asas (Foundation) - Core foundation and dependency injection container
2. Aman (Security) - Comprehensive security framework
3. Siraj (Light/Illumination) - API management and routing system
4. Shahid (Witness/Evidence) - Comprehensive logging and error handling system
5. Wisal (Connection) - Session management system
6. Rihlah (Journey) - Caching system
7. Sabr (Patience/Persistence) - Job queue system
8. Usul (Principles/Roots) - Knowledge management system
9. Iqra (Read) - Islamic search engine
10. Bayan (Explanation/Clarification) - Content formatting system
11. Simplified Routing (Path/Way) - Advanced routing system
12. Nizam (System/Order) - Main application system
13. Mizan (Balance/Scale) - Database system
14. Tadbir (Management/Planning) - Configuration management system
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
Extension                      → RihlahExtension       → Journey (Extension)
Controller                     → SabilController       → Path (Controller)
Service                       → UsulService            → Principles (Service)
Provider                      → AsasProvider           → Foundation (Provider)
Model                         → MizanModel             → Balance (Data Model)
Widget                        → BayanWidget            → Explanation (Widget)
Migration                     → SabrMigration          → Patience (Database Migration)
Config                        → TadbirConfig           → Management (Configuration)
```

### **2. Database & Data Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Database                      → MizanDatabase          → Balance (Database)
Query                         → IqraQuery              → Read (Query)
Connection                    → WisalConnection         → Connection (Database)
Migration                     → SabrMigration          → Patience (Migration)
Seed                          → UsulSeed               → Principles (Seed Data)
Model                         → MizanModel             → Balance (Data Model)
Collection                    → ShahidCollection        → Witness (Data Collection)
Repository                    → BayanRepository         → Explanation (Data Access)
```

### **3. HTTP & Web Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Request                       → SabilRequest           → Path (HTTP Request)
Response                      → BayanResponse          → Explanation (HTTP Response)
Middleware                    → AmanMiddleware         → Security (Middleware)
Route                         → SabilRoute             → Path (Route)
API                           → SirajAPI               → Light (API)
Endpoint                      → SirajEndpoint          → Light (API Endpoint)
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
Layout                        → NizamLayout            → Order (Layout)
Component                     → BayanComponent         → Explanation (Component)
```

### **5. Testing & Development Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Test                          → ShahidTest             → Witness (Test)
Unit                          → UsulUnit               → Principles (Unit Test)
Integration                   → WisalIntegration       → Connection (Integration)
Fixture                       → UsulFixture            → Principles (Test Data)
Mock                          → ShahidMock             → Witness (Mock)
Assertion                     → ShahidAssertion        → Witness (Assertion)
```

### **6. Utility & Helper Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Utility                       → UsulUtility            → Principles (Utility)
Helper                        → BayanHelper            → Explanation (Helper)
Factory                       → AsasFactory            → Foundation (Factory)
Builder                       → NizamBuilder           → Order (Builder)
Validator                     → AmanValidator          → Security (Validator)
Formatter                     → BayanFormatter         → Explanation (Formatter)
```

### **7. Event & Notification Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Event                         → SirajEvent             → Light (Event)
Listener                      → IqraListener           → Read (Listener)
Dispatcher                    → SirajDispatcher        → Light (Event Dispatcher)
Notification                  → BayanNotification      → Explanation (Notification)
Observer                      → ShahidObserver         → Witness (Observer)
```

### **8. Cache & Performance Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Cache                         → RihlahCache            → Journey (Cache)
Store                         → MizanStore             → Balance (Data Store)
Pool                          → WisalPool              → Connection (Connection Pool)
Queue                         → SabrQueue              → Patience (Job Queue)
Job                           → SabrJob                → Patience (Background Job)
Worker                        → SabrWorker             → Patience (Background Worker)
```

### **9. Security & Authentication Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
Auth                          → AmanAuth               → Security (Authentication)
Guard                         → AmanGuard              → Security (Guard)
Policy                        → UsulPolicy             → Principles (Policy)
Permission                    → UsulPermission         → Principles (Permission)
Role                          → UsulRole               → Principles (Role)
Token                         → AmanToken              → Security (Token)
Hash                          → AmanHash               → Security (Hash)
```

### **10. File & Asset Components**
```
Current Name                    → Islamic Name          → Meaning & Purpose
─────────────────────────────────────────────────────────────────────────────
File                          → IqraFile               → Read (File)
Asset                         → BayanAsset             → Explanation (Asset)
Upload                        → IqraUpload             → Read (File Upload)
Download                      → IqraDownload           → Read (File Download)
Storage                       → SabrStorage            → Patience (File Storage)
Manager                       → TadbirManager          → Management (Manager)
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
- asas-container.php (Foundation container)
- aman-authentication.php (Security authentication)
- sabil-router.php (Path router)
- tadbir-config.php (Management configuration)
- rihlah-cache.php (Journey caching)
- mizan-database.php (Balance database)
- usul-knowledge.php (Principles knowledge)
- iqra-search.php (Read search)
- bayan-formatter.php (Explanation formatter)
- siraj-api.php (Light API)
- shahid-logger.php (Witness logging)
- wisal-session.php (Connection session)
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
- AsasContainer (Foundation container)
- AmanAuthentication (Security authentication)
- SabilRouter (Path router)
- TadbirConfiguration (Management configuration)
- RihlahCache (Journey cache)
- MizanDatabase (Balance database)
- UsulKnowledge (Principles knowledge)
- IqraSearch (Read search)
- BayanFormatter (Explanation formatter)
- SirajAPI (Light API)
- ShahidLogger (Witness logger)
- WisalSession (Connection session)
- SabrQueue (Patience queue)
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
├── Asas (Foundation) - Base system, containers, services
├── Aman (Security) - Authentication, authorization, validation
├── Siraj (Light) - API management, endpoints, routing
├── Shahid (Witness) - Logging, monitoring, error handling
├── Wisal (Connection) - Sessions, connections, state
├── Rihlah (Journey) - Caching, performance, optimization
├── Sabr (Patience) - Queues, background jobs, persistence
├── Usul (Principles) - Knowledge, rules, policies
├── Iqra (Read) - Search, discovery, reading
├── Bayan (Explanation) - Formatting, presentation, clarification
├── Simplified Routing (Path) - Routing, requests, middleware
├── Nizam (Order) - Application, coordination, management
├── Mizan (Balance) - Database, data, storage
├── Tadbir (Management) - Configuration, planning, administration
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
- **Asas**: Foundation, basis, fundamental
- **Aman**: Security, safety, trust
- **Siraj**: Lamp, light, illumination
- **Shahid**: Witness, evidence, martyr
- **Wisal**: Connection, joining, union
- **Rihlah**: Journey, travel, expedition
- **Sabr**: Patience, perseverance, endurance
- **Usul**: Principles, fundamentals, methodology
- **Iqra**: Read, recite, proclaim
- **Bayan**: Explanation, clarification, statement
- **Simplified Routing**: Path, way, method
- **Nizam**: System, order, organization
- **Mizan**: Balance, scale, measure
- **Tadbir**: Management, administration, planning
- **Safa**: Purity, cleanliness, clarity
- **Marwa**: Excellence, elevation, distinction

### **Additional Islamic Terms for Future Use**
- **Hikmah** (Wisdom) - AI/ML systems
- **Fadhilah** (Virtue) - Quality assurance
- **Tawheed** (Unity) - System integration
- **Ihsan** (Excellence) - Performance optimization
- **Adab** (Etiquette) - User interface guidelines
- **Taqwa** (Consciousness) - Monitoring systems

---

**Last Updated:** 2025-08-19  
**Version:** 1.0  
**Author:** IslamWiki Development Team  
**Status:** Islamic Naming Conventions Guide Complete ✅ 