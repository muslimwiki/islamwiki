# IslamWiki Data Models

## 🎯 **Overview**

This directory contains documentation for the data models that represent the core entities and relationships in IslamWiki. Models follow the Active Record pattern and implement Islamic naming conventions for database tables and relationships.

---

## 🏗️ **Model Architecture**

### **Model Hierarchy**
```
Model Architecture:
├── 📁 Base Models - Abstract base classes and interfaces
├── 📁 User Models - User account and authentication models
├── 📁 Content Models - Wiki pages and content models
├── 📁 Islamic Models - Quran, Hadith, and Islamic content
├── 📁 System Models - System configuration and settings
└── 📁 Extension Models - Extension-specific data models
```

### **Model Responsibilities**
- **Data Representation**: Represent database entities and relationships
- **Data Validation**: Validate data before database operations
- **Business Logic**: Implement domain-specific business rules
- **Database Operations**: Handle CRUD operations and queries
- **Relationship Management**: Manage model associations and joins

---

## 🔧 **Model Categories**

### **1. Base Models**
- **AbstractModel**: Base model with common functionality
- **TimestampsModel**: Model with created/updated timestamps
- **SoftDeleteModel**: Model with soft delete functionality
- **AuditModel**: Model with audit trail capabilities

### **2. User Models**
- **User**: User account information and authentication
- **UserProfile**: Extended user profile information
- **UserRole**: User roles and permissions
- **UserSession**: User session management

### **3. Content Models**
- **Page**: Wiki page content and metadata
- **PageRevision**: Page version history and changes
- **PageCategory**: Page categorization and organization
- **PageTag**: Page tagging and labeling

### **4. Islamic Models**
- **QuranVerse**: Quran verses and translations
- **QuranSurah**: Quran chapters and metadata
- **Hadith**: Hadith collections and authenticity
- **HadithNarrator**: Hadith narrator information
- **SalahTime**: Salah time calculations and data
- **HijriDate**: Islamic calendar dates and events

### **5. System Models**
- **Configuration**: System configuration settings
- **Extension**: Extension information and status
- **Skin**: Skin configuration and settings
- **Log**: System logs and audit trails

---

## 📝 **Model Implementation**

### **Basic Model Structure**
```php
<?php

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\AbstractModel;
use IslamWiki\Core\Database\TimestampsModel;

/**
 * User Model - User account management
 * 
 * @package IslamWiki\Models
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class User extends AbstractModel implements TimestampsModel
{
    protected string $table = 'mizan_users';
    
    protected array $fillable = [
        'username',
        'email',
        'password_hash',
        'first_name',
        'last_name',
        'is_active',
        'email_verified_at'
    ];
    
    protected array $hidden = [
        'password_hash',
        'remember_token'
    ];
    
    protected array $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
    
    /**
     * Get user's pages
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'user_id');
    }
    
    /**
     * Get user's roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(UserRole::class, 'mizan_user_roles');
    }
}
```

### **Model Relationships**
```php
// One-to-One Relationship
public function profile(): HasOne
{
    return $this->hasOne(UserProfile::class, 'user_id');
}

// One-to-Many Relationship
public function posts(): HasMany
{
    return $this->hasMany(Post::class, 'author_id');
}

// Many-to-Many Relationship
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class, 'mizan_post_tags');
}

// Polymorphic Relationship
public function comments(): MorphMany
{
    return $this->morphMany(Comment::class, 'commentable');
}
```

---

## 🗄️ **Database Structure**

### **Table Naming Convention**
```sql
-- ✅ Correct - Using Islamic naming
CREATE TABLE mizan_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE mizan_pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES mizan_users(id)
);
```

### **Indexing Strategy**
```sql
-- Primary indexes
PRIMARY KEY (id)

-- Unique indexes
UNIQUE KEY uk_users_username (username)
UNIQUE KEY uk_users_email (email)
UNIQUE KEY uk_pages_slug (slug)

-- Performance indexes
KEY idx_pages_user_id (user_id)
KEY idx_pages_status (status)
KEY idx_pages_created_at (created_at)
KEY idx_pages_title_content (title, content(100))
```

---

## 🚀 **Model Features**

### **Data Validation**
- **Input Validation**: Validate data before database operations
- **Type Casting**: Automatic data type conversion
- **Mass Assignment Protection**: Protect sensitive fields
- **Validation Rules**: Comprehensive validation rules

### **Query Building**
- **Eloquent-like Syntax**: Familiar query building interface
- **Relationship Loading**: Eager and lazy relationship loading
- **Query Scopes**: Reusable query constraints
- **Raw Queries**: Direct SQL when needed

### **Performance Optimization**
- **Query Caching**: Cache frequently used queries
- **Lazy Loading**: Load relationships on demand
- **Eager Loading**: Load relationships efficiently
- **Database Indexing**: Optimized database performance

---

## 🔒 **Security Considerations**

### **Data Protection**
- **Mass Assignment**: Protect sensitive fields from mass assignment
- **Input Sanitization**: Sanitize all input data
- **SQL Injection Prevention**: Use prepared statements
- **XSS Prevention**: Escape output data

### **Access Control**
- **Field Visibility**: Hide sensitive fields from output
- **Role-based Access**: Implement role-based data access
- **Audit Logging**: Log all data modifications
- **Data Encryption**: Encrypt sensitive data

---

## 📚 **Model Documentation**

### **Available Models**
- **[User Models](user/README.md)** - User account management
- **[Content Models](content/README.md)** - Wiki content management
- **[Islamic Models](islamic/README.md)** - Islamic content models
- **[System Models](system/README.md)** - System configuration
- **[Extension Models](extension/README.md)** - Extension data

### **Model Development**
- **[Model Standards](../standards.md)** - Development standards
- **[Style Guide](../guides/style-guide.md)** - Coding standards
- **[Islamic Naming Conventions](../guides/islamic-naming-conventions.md)** - Naming guide

---

## 🧪 **Testing Models**

### **Unit Testing**
```php
class UserTest extends TestCase
{
    public function testUserCanBeCreated(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hashed_password'
        ]);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testuser', $user->username);
    }
    
    public function testUserFullNameIsCorrect(): void
    {
        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        
        $this->assertEquals('John Doe', $user->full_name);
    }
}
```

### **Database Testing**
- **Model Creation**: Test model instantiation and creation
- **Relationship Loading**: Test relationship loading and queries
- **Data Validation**: Test validation rules and constraints
- **Performance Testing**: Test query performance and optimization

---

## 📖 **Additional Resources**

### **Related Documentation**
- **[Architecture Overview](../architecture/overview.md)** - System architecture
- **[Core Systems](../architecture/core-systems.md)** - System components
- **[Controllers Documentation](../controllers/README.md)** - Request handling
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
**Status:** Models Documentation Complete ✅ 