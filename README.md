# IslamWiki

A comprehensive Islamic knowledge management platform built with modern web technologies.

**Current Version:** 0.0.39  
**Release Date:** August 2, 2025

## 🌟 Features

### 🏗️ Core Platform
- **Modern PHP Framework** - Built with custom MVC architecture
- **Responsive Design** - Mobile-friendly interface with multiple skins
- **User Management** - Complete authentication system with session management
- **User Profiles** - Private and public profile viewing with user statistics
- **Content Management** - Wiki-style page creation and editing
- **Search System** - Advanced search with filters and suggestions
- **Navigation System** - User dropdown menu with ZamZam.js integration

### 📚 Islamic Features
- **Quran Integration** - Verse search, tafsir, and recitation
- **Hadith Collections** - Comprehensive hadith database with authenticity ratings
- **Islamic Calendar** - Hijri calendar with important dates and events
- **Prayer Times** - Accurate prayer time calculations with location support
- **Scholar Profiles** - Database of Islamic scholars and their works

### 🧠 Knowledge Graph System
- **Bayan Knowledge Graph** - Advanced graph-based knowledge management
- **Node Management** - Create and manage Islamic concepts, verses, hadith, scholars
- **Relationship Mapping** - Connect concepts through various relationship types
- **Graph Traversal** - Find paths and connections between Islamic knowledge
- **Advanced Search** - Full-text search with relationship-aware filtering
- **Statistics Dashboard** - Real-time metrics and analytics

### 🎨 User Interface
- **Multi-Skin Support** - Multiple themes including Bismillah, BlueSkin, GreenSkin
- **Responsive Design** - Works on desktop, tablet, and mobile devices
- **Modern UI** - Clean, intuitive interface with enhanced navigation and layout
- **Three-Column Layout System** - Comprehensive full-width responsive layout
  - **Pages Page:** Left (create content & stats), Middle (hero & pages list), Right (search & filter)
  - **About Page:** Left (get involved), Middle (main content), Right (contact us)
  - **Dashboard:** Left (quick actions & user stats), Middle (main content), Right (learning resources & site stats)
- **Enhanced Navigation** - Separated top and primary navigation with improved styling
- **Dynamic Site Statistics** - Real-time dashboard statistics with accurate data
- **Accessibility** - WCAG compliant design with better contrast and readability

### 🔧 Technical Features
- **Database Migration System** - Automated schema management
- **Service Provider Architecture** - Modular, extensible design
- **RESTful API** - Programmatic access to all features
- **Security Features** - CSRF protection, input validation, XSS prevention
- **Error Handling** - Comprehensive logging and debugging

## 📁 Project Structure

### Organization
- **`public/`** - Web-accessible files only (clean and secure)
- **`tests/`** - All test files organized by type
  - `tests/web/` - Web/integration tests (73 files)
  - `tests/Unit/` - Unit tests
- **`debug/`** - Debug scripts and development tools (20 files)
- **`src/`** - Application source code
- **`resources/`** - Views and assets
- **`docs/`** - Documentation

### Security
- Development files moved out of web-accessible directory
- Test files properly organized and secured
- Debug tools in dedicated directory
- Clean public directory structure

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Composer
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/islamwiki.git
   cd islamwiki
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Run database migrations**
   ```bash
   php scripts/migrate.php
   ```

5. **Set up web server**
   - Point document root to `/public`
   - Ensure mod_rewrite is enabled (Apache)
   - Configure SSL certificate

6. **Access the application**
   - Main site: `https://your-domain.com`
   - Knowledge Graph: `https://your-domain.com/bayan`

## 📊 Knowledge Graph System

### Overview
The Bayan Knowledge Graph System provides a powerful platform for connecting Islamic knowledge through a graph-based approach. Users can create nodes representing concepts, verses, hadith, scholars, and other entities, then establish relationships between them.

### Key Features

#### Node Types
- **Concept** - Islamic concepts and terms
- **Verse** - Quran verses with tafsir
- **Hadith** - Prophet's sayings and actions
- **Scholar** - Islamic scholars and authorities
- **School** - Schools of thought and madhabs
- **Event** - Historical events
- **Place** - Important places in Islamic history
- **Person** - Important figures
- **Book** - Islamic books and texts
- **Topic** - General topics and subjects

#### Relationship Types
- **References** - One concept references another
- **Explains** - One concept explains another
- **Authored By** - Content authored by a scholar
- **Belongs To** - Concept belongs to a category
- **Related To** - General relationship
- **Mentions** - One concept mentions another
- **Derived From** - Concept derived from another
- **Similar To** - Similar concepts
- **Opposes** - Opposing concepts
- **Supports** - Supporting evidence

### Usage Examples

#### Creating a Node
```php
$nodeData = [
    'type' => 'concept',
    'title' => 'Tawhid (Monotheism)',
    'content' => 'Tawhid is the Islamic concept of monotheism...',
    'metadata' => ['category' => 'Aqeedah', 'importance' => 'fundamental']
];
$nodeId = $bayanManager->createNode($nodeData);
```

#### Creating a Relationship
```php
$relationshipId = $bayanManager->createRelationship(
    $sourceNodeId, 
    $targetNodeId, 
    'opposes', 
    ['strength' => 'strong']
);
```

#### Searching the Graph
```php
$results = $bayanManager->search('Tawhid', ['type' => 'concept']);
```

#### Finding Related Nodes
```php
$relatedNodes = $bayanManager->getRelatedNodes($nodeId, 'opposes');
```

### Web Interface

#### Routes
- `/bayan` - Main dashboard with statistics
- `/bayan/search` - Search interface with filters
- `/bayan/create` - Node creation form
- `/bayan/node/{id}` - Node details and relationships
- `/bayan/statistics` - Graph statistics and metrics
- `/bayan/paths` - Path finding between nodes

#### API Endpoints
- `POST /bayan/create` - Create new node
- `POST /bayan/relationship` - Create relationship
- `GET /bayan/statistics` - Get graph statistics
- `GET /bayan/paths` - Find paths between nodes

## 🏗️ Architecture

### Core Components
- **Application** - Main application bootstrap and configuration
- **Container** - Dependency injection container
- **Router** - HTTP routing and request handling
- **Database** - Database connection and query builder
- **View** - Template rendering with Twig
- **Session** - Session management and authentication

### Service Providers
- **DatabaseServiceProvider** - Database connection and migrations
- **ViewServiceProvider** - Template engine and view rendering
- **SessionServiceProvider** - Session management
- **ExtensionServiceProvider** - Plugin and extension system
- **ConfigurationServiceProvider** - Application configuration
- **LoggingServiceProvider** - Logging and error handling
- **SkinServiceProvider** - Theme and skin management
- **BayanServiceProvider** - Knowledge graph system

### Controllers
- **HomeController** - Main page and navigation
- **AuthController** - User authentication
- **ProfileController** - User profiles and settings
- **PageController** - Wiki page management
- **QuranController** - Quran verse management
- **HadithController** - Hadith collection management
- **BayanController** - Knowledge graph management

## 📁 Project Structure

```
islamwiki/
├── src/                    # Source code
│   ├── Core/              # Core framework
│   │   ├── Bayan/         # Knowledge graph system
│   │   ├── Database/      # Database layer
│   │   ├── Http/          # HTTP handling
│   │   ├── Routing/       # Routing system
│   │   └── View/          # Template engine
│   ├── Http/              # HTTP layer
│   │   ├── Controllers/   # Application controllers
│   │   └── Middleware/    # Request middleware
│   ├── Models/            # Data models
│   ├── Providers/         # Service providers
│   └── Skins/             # Theme system
├── resources/             # Application resources
│   └── views/            # Template files
├── public/               # Web root
├── database/             # Database migrations
├── scripts/              # Utility scripts
├── docs/                 # Documentation
└── tests/                # Test files
```

## 🔧 Configuration

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Database Schema
The application uses MySQL with the following key tables:
- `users` - User accounts and profiles
- `pages` - Wiki pages and content
- `bayan_nodes` - Knowledge graph nodes
- `bayan_edges` - Knowledge graph relationships
- `quran_verses` - Quran verses and translations
- `hadiths` - Hadith collections and chains
- `scholars` - Islamic scholars and authorities

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php scripts/test.php

# Run specific test
php public/tests/test-bayan.php
```

### Test Scripts
- `test-bayan.php` - Knowledge graph system tests
- `debug-bayan.php` - Knowledge graph debugging
- `bayan-test.php` - Web interface tests

## 🔒 Security

### Features
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive input sanitization
- **SQL Injection Prevention** - Prepared statements and parameter binding
- **XSS Protection** - Output encoding and sanitization
- **Session Security** - Secure session management
- **Error Handling** - Secure error reporting without information disclosure

### Best Practices
- Always validate and sanitize user input
- Use prepared statements for database queries
- Implement proper authentication and authorization
- Keep dependencies updated
- Use HTTPS in production
- Regular security audits

## 📈 Performance

### Optimization Features
- **Database Indexing** - Optimized queries with proper indexes
- **Caching** - Redis-based caching for frequently accessed data
- **CDN Integration** - Content delivery network for static assets
- **Pagination** - Efficient pagination for large datasets
- **Lazy Loading** - On-demand resource loading

### Monitoring
- **Error Logging** - Comprehensive error tracking
- **Performance Metrics** - Response time and throughput monitoring
- **Database Analytics** - Query performance and optimization
- **User Analytics** - Usage patterns and behavior tracking

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

### Coding Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comprehensive documentation
- Write unit tests for new features
- Follow the existing code style

### Testing Guidelines
- Write tests for all new features
- Ensure existing tests pass
- Test edge cases and error conditions
- Use descriptive test names

## 📚 Documentation

### Additional Resources
- [API Documentation](docs/api.md)
- [Database Schema](docs/database.md)
- [Deployment Guide](docs/deployment.md)
- [Security Guide](docs/security.md)
- [Performance Guide](docs/performance.md)

### Knowledge Graph Documentation
- [Bayan System Guide](docs/bayan-system.md)
- [Graph Query Examples](docs/graph-queries.md)
- [API Reference](docs/bayan-api.md)
- [User Guide](docs/bayan-user-guide.md)

## 📄 License

This project is licensed under the GNU Affero General Public License v3.0 - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- **Islamic Scholars** - For their contributions to Islamic knowledge
- **Open Source Community** - For the tools and libraries used
- **Contributors** - For their time and expertise
- **Users** - For feedback and suggestions

## 📞 Support

### Getting Help
- **Documentation** - Check the docs folder for detailed guides
- **Issues** - Report bugs and feature requests on GitHub
- **Discussions** - Join community discussions
- **Email** - Contact the development team

### Reporting Issues
When reporting issues, please include:
- **Version** - IslamWiki version you're using
- **Environment** - PHP version, database, web server
- **Steps** - Detailed steps to reproduce the issue
- **Expected vs Actual** - What you expected vs what happened
- **Logs** - Relevant error logs and stack traces

---

**Version:** 0.0.34  
**Last Updated:** August 1, 2025  
**Status:** Production Ready


