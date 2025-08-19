# IslamWiki API Documentation

## 🌐 **Overview**

This directory contains comprehensive documentation for the IslamWiki API system. The platform provides both RESTful and GraphQL APIs for content access, user management, and system administration, all while maintaining Islamic content authenticity and security standards.

## 📚 **API Documentation**

### **Core API Documents**
- **[API Overview](overview.md)** - Complete API system overview and architecture

## 🏗️ **API Architecture**

### **API Layers**
- **Siraj (Light)** - API management and routing system
- **Aman (Security)** - API authentication and authorization
- **Shahid (Witness)** - API monitoring and logging
- **Rihlah (Journey)** - API caching and performance

### **API Types**

#### **1. RESTful API**
- Standard HTTP methods (GET, POST, PUT, DELETE)
- JSON response format for consistency
- Authentication via API keys and tokens
- Rate limiting to prevent abuse
- Versioning for backward compatibility

#### **2. GraphQL API**
- Flexible queries for complex data needs
- Real-time subscriptions for live updates
- Schema introspection for documentation
- Performance optimization with query batching

## 🔐 **API Security**

### **Authentication Methods**
- **API Keys**: Simple authentication for public endpoints
- **Bearer Tokens**: JWT-based authentication for user sessions
- **OAuth 2.0**: Third-party application integration
- **Multi-factor Authentication**: Enhanced security for admin APIs

### **Authorization Levels**
- **Public**: Read-only access to public content
- **Authenticated**: User-specific content and actions
- **Contributor**: Content creation and editing permissions
- **Moderator**: Content moderation and user management
- **Administrator**: Full system access and configuration

### **Security Features**
- **Rate Limiting**: Prevent API abuse and DDoS attacks
- **Input Validation**: Comprehensive input sanitization
- **Output Escaping**: Prevent XSS and injection attacks
- **CORS Configuration**: Controlled cross-origin access
- **Request Logging**: Complete audit trail of all API calls

## 📡 **API Endpoints**

### **Content API**
```
GET    /api/v1/content/{id}           # Get content by ID
GET    /api/v1/content/search         # Search content
POST   /api/v1/content                # Create new content
PUT    /api/v1/content/{id}           # Update content
DELETE /api/v1/content/{id}           # Delete content
```

### **User API**
```
GET    /api/v1/users/{id}             # Get user profile
GET    /api/v1/users/me               # Get current user
PUT    /api/v1/users/{id}             # Update user profile
POST   /api/v1/users                  # Create new user
DELETE /api/v1/users/{id}             # Delete user
```

### **Search API**
```
GET    /api/v1/search                  # General search
GET    /api/v1/search/quran           # Quran-specific search
GET    /api/v1/search/hadith          # Hadith-specific search
GET    /api/v1/search/suggestions     # Search suggestions
```

### **Authentication API**
```
POST   /api/v1/auth/login             # User login
POST   /api/v1/auth/logout            # User logout
POST   /api/v1/auth/refresh           # Refresh token
POST   /api/v1/auth/register          # User registration
POST   /api/v1/auth/forgot-password   # Password reset
```

## 🔧 **API Usage**

### **Authentication Example**
```bash
# Get API token
curl -X POST https://api.islam.wiki/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Use token for authenticated requests
curl -X GET https://api.islam.wiki/api/v1/users/me \
  -H "Authorization: Bearer {token}"
```

### **Content Search Example**
```bash
# Search for Islamic content
curl -X GET "https://api.islam.wiki/api/v1/search?q=salah&type=article&page=1&limit=10"
```

### **GraphQL Example**
```graphql
query SearchContent($query: String!, $type: ContentType) {
  search(query: $query, type: $type) {
    id
    title
    excerpt
    author {
      name
      username
    }
    createdAt
    updatedAt
  }
}
```

## 📊 **API Performance**

### **Caching Strategy**
- **Response Caching**: Cache API responses for improved performance
- **Query Caching**: Cache database queries and search results
- **CDN Integration**: Global content delivery for static resources
- **Rate Limiting**: Prevent abuse while maintaining performance

### **Optimization Features**
- **Pagination**: Efficient handling of large result sets
- **Field Selection**: Return only requested data fields
- **Compression**: Gzip compression for all API responses
- **Connection Pooling**: Efficient database connection management

## 🧪 **API Testing**

### **Testing Tools**
```bash
# Test API endpoints
curl -X GET https://api.islam.wiki/api/v1/health

# Test authentication
curl -X POST https://api.islam.wiki/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "test123"}'
```

### **API Testing Suite**
- **Postman Collections**: Complete API testing collections
- **Automated Tests**: PHPUnit tests for all API endpoints
- **Performance Tests**: Load testing and benchmarking
- **Security Tests**: Vulnerability scanning and penetration testing

## 📖 **Related Documentation**

- **[Architecture Overview](architecture/README.md)** - System architecture
- **[Security Documentation](security/README.md)** - Security practices
- **[Development Guide](guides/development.md)** - Development practices
- **[Testing Documentation](testing/README.md)** - Testing strategies

## 📄 **License Information**

This API documentation is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

---

**Last Updated:** 2025-08-19  
**Version:** 0.0.1.0  
**Author:** IslamWiki Development Team  
**License:** AGPL-3.0  
**Status:** API Documentation Complete ✅ 