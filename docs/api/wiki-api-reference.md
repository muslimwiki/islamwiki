# Wiki API Reference - IslamWiki

**Version:** 0.0.2.1  
**Last Updated:** 2025-01-20  
**Status:** Complete API Reference ✅  

## 🎯 **Overview**

This API reference provides comprehensive documentation for all WikiExtension endpoints, including request/response formats, authentication, error handling, and examples.

## 🔐 **Authentication**

### **Authentication Methods**
- **Session-based**: For web interface users
- **API Key**: For external applications
- **Bearer Token**: For mobile applications

### **Headers**
```http
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
X-API-Key: {your-api-key}
```

## 📚 **API Endpoints**

### **Base URL**
```
https://your-domain.com/api/v1/wiki
```

### **Response Format**
All API responses follow this structure:
```json
{
  "success": true,
  "data": {},
  "message": "Operation completed successfully",
  "meta": {
    "timestamp": "2025-01-20T10:00:00Z",
    "version": "0.0.2.1"
  }
}
```

### **Error Response Format**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "field": "error description"
    }
  },
  "meta": {
    "timestamp": "2025-01-20T10:00:00Z",
    "version": "0.0.2.1"
  }
}
```

## 🏠 **Wiki Homepage API**

### **GET /api/v1/wiki**

Get wiki homepage data including featured content and statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "featured_pages": [
      {
        "id": 1,
        "title": "The Golden Age of Islam",
        "slug": "golden-age-of-islam",
        "excerpt": "The Golden Age of Islam, also known as the Islamic Golden Age...",
        "category": {
          "id": 1,
          "name": "Islamic History",
          "slug": "islamic-history",
          "color": "#007cba"
        },
        "tags": [
          {
            "id": 1,
            "name": "History",
            "slug": "history",
            "color": "#007cba"
          }
        ],
        "view_count": 150,
        "revision_count": 3,
        "created_at": "2025-01-20T10:00:00Z",
        "updated_at": "2025-01-20T15:30:00Z"
      }
    ],
    "recent_pages": [
      {
        "id": 2,
        "title": "Islamic Contributions to Mathematics",
        "slug": "islamic-contributions-mathematics",
        "excerpt": "Islamic scholars made significant contributions...",
        "category": {
          "id": 2,
          "name": "Islamic Sciences",
          "slug": "islamic-sciences",
          "color": "#28a745"
        },
        "view_count": 89,
        "created_at": "2025-01-20T12:00:00Z"
      }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Islamic History",
        "slug": "islamic-history",
        "description": "Articles about Islamic history and civilization",
        "icon": "fas fa-landmark",
        "color": "#007cba",
        "page_count": 25,
        "is_featured": true
      }
    ],
    "statistics": {
      "total_pages": 100,
      "total_categories": 5,
      "total_views": 5000,
      "total_revisions": 250,
      "active_users": 45
    }
  },
  "message": "Wiki homepage data retrieved successfully"
}
```

## 📄 **Page Management API**

### **GET /api/v1/wiki/pages/{slug}**

Get individual wiki page data.

**Parameters:**
- `slug` (string, required): Page URL slug

**Response:**
```json
{
  "success": true,
  "data": {
    "page": {
      "id": 1,
      "title": "The Golden Age of Islam",
      "slug": "golden-age-of-islam",
      "content": "# The Golden Age of Islam\n\n## Overview\n\nThe Golden Age of Islam...",
      "meta_description": "Explore the remarkable achievements of the Islamic Golden Age...",
      "content_type": "article",
      "status": "published",
      "is_featured": true,
      "is_locked": false,
      "view_count": 150,
      "revision_count": 3,
      "category": {
        "id": 1,
        "name": "Islamic History",
        "slug": "islamic-history",
        "description": "Articles about Islamic history and civilization",
        "icon": "fas fa-landmark",
        "color": "#007cba"
      },
      "tags": [
        {
          "id": 1,
          "name": "History",
          "slug": "history",
          "color": "#007cba"
        },
        {
          "id": 2,
          "name": "Culture",
          "slug": "culture",
          "color": "#6f42c1"
        }
      ],
      "creator": {
        "id": 1,
        "username": "admin",
        "display_name": "Administrator",
        "avatar": "/avatars/admin.jpg"
      },
      "last_editor": {
        "id": 2,
        "username": "editor",
        "display_name": "Content Editor",
        "avatar": "/avatars/editor.jpg"
      },
      "published_at": "2025-01-20T10:00:00Z",
      "created_at": "2025-01-20T10:00:00Z",
      "updated_at": "2025-01-20T15:30:00Z"
    },
    "related_pages": [
      {
        "id": 2,
        "title": "Islamic Contributions to Mathematics",
        "slug": "islamic-contributions-mathematics",
        "excerpt": "Islamic scholars made significant contributions...",
        "similarity_score": 0.85
      }
    ],
    "revision_summary": {
      "total_revisions": 3,
      "last_revision": {
        "id": 3,
        "revision_number": 3,
        "edit_comment": "Added new section about art and architecture",
        "created_at": "2025-01-20T15:30:00Z"
      }
    }
  },
  "message": "Page data retrieved successfully"
}
```

### **POST /api/v1/wiki/pages**

Create a new wiki page.

**Request Body:**
```json
{
  "title": "New Page Title",
  "content": "# New Page\n\nThis is the content of the new page...",
  "meta_description": "Brief description of the new page",
  "category_id": 1,
  "content_type": "article",
  "tags": "tag1, tag2, tag3",
  "status": "published",
  "is_featured": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "page_id": 123,
    "slug": "new-page-title",
    "url": "/wiki/new-page-title"
  },
  "message": "Page created successfully"
}
```

### **PUT /api/v1/wiki/pages/{slug}**

Update existing wiki page.

**Request Body:**
```json
{
  "title": "Updated Page Title",
  "content": "# Updated Page\n\nUpdated content here...",
  "meta_description": "Updated description",
  "category_id": 2,
  "tags": "updated, tags, here",
  "edit_comment": "Updated content and improved formatting"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "revision_id": 456,
    "revision_number": 4,
    "changes": {
      "title_changed": true,
      "content_changed": true,
      "category_changed": true,
      "tags_changed": true
    }
  },
  "message": "Page updated successfully"
}
```

### **DELETE /api/v1/wiki/pages/{slug}**

Delete wiki page.

**Response:**
```json
{
  "success": true,
  "data": {
    "deleted_at": "2025-01-20T16:00:00Z"
  },
  "message": "Page deleted successfully"
}
```

### **GET /api/v1/wiki/pages**

List wiki pages with filtering and pagination.

**Query Parameters:**
- `page` (int, optional): Page number (default: 1)
- `per_page` (int, optional): Items per page (default: 20, max: 100)
- `category_id` (int, optional): Filter by category
- `content_type` (string, optional): Filter by content type
- `status` (string, optional): Filter by status
- `is_featured` (boolean, optional): Filter featured pages
- `search` (string, optional): Search in title and content
- `sort_by` (string, optional): Sort field (title, created_at, updated_at, view_count)
- `sort_order` (string, optional): Sort order (asc, desc)

**Response:**
```json
{
  "success": true,
  "data": {
    "pages": [
      {
        "id": 1,
        "title": "The Golden Age of Islam",
        "slug": "golden-age-of-islam",
        "excerpt": "The Golden Age of Islam...",
        "category": {
          "id": 1,
          "name": "Islamic History",
          "slug": "islamic-history"
        },
        "tags": ["History", "Culture"],
        "view_count": 150,
        "revision_count": 3,
        "status": "published",
        "is_featured": true,
        "created_at": "2025-01-20T10:00:00Z",
        "updated_at": "2025-01-20T15:30:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_items": 100,
      "total_pages": 5,
      "has_next_page": true,
      "has_previous_page": false
    },
    "filters": {
      "applied": {
        "category_id": 1,
        "content_type": "article"
      },
      "available": {
        "categories": [...],
        "content_types": [...],
        "statuses": [...]
      }
    }
  },
  "message": "Pages retrieved successfully"
}
```

## 🏷️ **Category Management API**

### **GET /api/v1/wiki/categories**

List all wiki categories.

**Query Parameters:**
- `featured` (boolean, optional): Filter featured categories
- `parent_id` (int, optional): Filter by parent category
- `include_stats` (boolean, optional): Include page counts (default: true)

**Response:**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Islamic History",
        "slug": "islamic-history",
        "description": "Articles about Islamic history and civilization",
        "icon": "fas fa-landmark",
        "color": "#007cba",
        "parent_id": null,
        "sort_order": 0,
        "is_featured": true,
        "is_public": true,
        "page_count": 25,
        "subcategory_count": 3,
        "creator": {
          "id": 1,
          "username": "admin",
          "display_name": "Administrator"
        },
        "created_at": "2025-01-20T10:00:00Z",
        "updated_at": "2025-01-20T10:00:00Z"
      }
    ],
    "total_categories": 5,
    "featured_categories": [...],
    "category_tree": [...]
  },
  "message": "Categories retrieved successfully"
}
```

### **GET /api/v1/wiki/categories/{slug}**

Get individual category with pages.

**Parameters:**
- `slug` (string, required): Category URL slug

**Query Parameters:**
- `page` (int, optional): Page number for pagination
- `per_page` (int, optional): Items per page
- `sort_by` (string, optional): Sort field
- `sort_order` (string, optional): Sort order

**Response:**
```json
{
  "success": true,
  "data": {
    "category": {
      "id": 1,
      "name": "Islamic History",
      "slug": "islamic-history",
      "description": "Articles about Islamic history and civilization",
      "icon": "fas fa-landmark",
      "color": "#007cba",
      "parent_id": null,
      "sort_order": 0,
      "is_featured": true,
      "is_public": true,
      "page_count": 25,
      "subcategory_count": 3,
      "creator": {...},
      "created_at": "2025-01-20T10:00:00Z",
      "updated_at": "2025-01-20T10:00:00Z"
    },
    "pages": [...],
    "subcategories": [...],
    "pagination": {...}
  },
  "message": "Category data retrieved successfully"
}
```

### **POST /api/v1/wiki/categories**

Create new category.

**Request Body:**
```json
{
  "name": "New Category",
  "description": "Description of the new category",
  "icon": "fas fa-folder",
  "color": "#28a745",
  "parent_id": null,
  "sort_order": 0,
  "is_featured": false,
  "is_public": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "category_id": 6,
    "slug": "new-category"
  },
  "message": "Category created successfully"
}
```

### **PUT /api/v1/wiki/categories/{slug}**

Update existing category.

**Request Body:**
```json
{
  "name": "Updated Category Name",
  "description": "Updated description",
  "icon": "fas fa-folder-open",
  "color": "#dc3545"
}
```

### **DELETE /api/v1/wiki/categories/{slug}**

Delete category.

**Response:**
```json
{
  "success": true,
  "data": {
    "deleted_pages": 25,
    "deleted_at": "2025-01-20T16:00:00Z"
  },
  "message": "Category and all associated pages deleted successfully"
}
```

## 🔍 **Search API**

### **GET /api/v1/wiki/search**

Search wiki content.

**Query Parameters:**
- `q` (string, required): Search query
- `type` (string, optional): Search type (general, title, content, category)
- `category_id` (int, optional): Filter by category
- `content_type` (string, optional): Filter by content type
- `status` (string, optional): Filter by status
- `date_from` (string, optional): Filter by start date (ISO 8601)
- `date_to` (string, optional): Filter by end date (ISO 8601)
- `sort_by` (string, optional): Sort field (relevance, date, title, view_count)
- `sort_order` (string, optional): Sort order (asc, desc)
- `page` (int, optional): Page number for pagination
- `per_page` (int, optional): Items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "query": "islamic mathematics",
    "results": [
      {
        "id": 2,
        "title": "Islamic Contributions to Mathematics",
        "slug": "islamic-contributions-mathematics",
        "excerpt": "Islamic scholars made significant contributions to mathematics...",
        "relevance_score": 0.95,
        "category": {
          "id": 2,
          "name": "Islamic Sciences",
          "slug": "islamic-sciences"
        },
        "tags": ["Mathematics", "Science", "History"],
        "content_type": "article",
        "status": "published",
        "view_count": 89,
        "revision_count": 2,
        "created_at": "2025-01-20T12:00:00Z",
        "updated_at": "2025-01-20T14:00:00Z",
        "highlights": {
          "title": ["<mark>Islamic</mark> Contributions to <mark>Mathematics</mark>"],
          "content": ["<mark>Islamic</mark> scholars made significant contributions to <mark>mathematics</mark>..."]
        }
      }
    ],
    "total_results": 15,
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_items": 15,
      "total_pages": 1
    },
    "filters": {
      "applied": {
        "type": "general",
        "category_id": 2
      },
      "available": {
        "categories": [...],
        "content_types": [...],
        "statuses": [...],
        "date_ranges": [...]
      }
    },
    "suggestions": [
      "islamic science",
      "islamic algebra",
      "islamic geometry"
    ],
    "search_metadata": {
      "search_time_ms": 45,
      "index_size": 1000,
      "last_indexed": "2025-01-20T09:00:00Z"
    }
  },
  "message": "Search completed successfully"
}
```

### **GET /api/v1/wiki/search/suggestions**

Get search suggestions and autocomplete.

**Query Parameters:**
- `q` (string, required): Partial search query
- `limit` (int, optional): Maximum suggestions (default: 10)

**Response:**
```json
{
  "success": true,
  "data": {
    "query": "islam",
    "suggestions": [
      {
        "text": "Islamic History",
        "type": "category",
        "url": "/wiki/categories/islamic-history"
      },
      {
        "text": "Islamic Contributions to Mathematics",
        "type": "page",
        "url": "/wiki/islamic-contributions-mathematics"
      },
      {
        "text": "Islamic Art and Architecture",
        "type": "category",
        "url": "/wiki/categories/islamic-art-architecture"
      }
    ],
    "popular_searches": [
      "islamic golden age",
      "islamic science",
      "islamic art"
    ]
  },
  "message": "Search suggestions retrieved successfully"
}
```

## 📚 **Revision History API**

### **GET /api/v1/wiki/pages/{slug}/history**

Get page revision history.

**Parameters:**
- `slug` (string, required): Page URL slug

**Query Parameters:**
- `page` (int, optional): Page number for pagination
- `per_page` (int, optional): Items per page
- `include_content` (boolean, optional): Include full content (default: false)

**Response:**
```json
{
  "success": true,
  "data": {
    "page": {
      "id": 1,
      "title": "The Golden Age of Islam",
      "slug": "golden-age-of-islam"
    },
    "revisions": [
      {
        "id": 3,
        "revision_number": 3,
        "content": "# The Golden Age of Islam...",
        "edit_comment": "Added new section about art and architecture",
        "is_minor": false,
        "is_current": true,
        "changes": {
          "added_lines": 15,
          "removed_lines": 3,
          "modified_sections": ["Art and Architecture"],
          "diff_summary": "Added 15 lines, removed 3 lines"
        },
        "creator": {
          "id": 2,
          "username": "editor",
          "display_name": "Content Editor"
        },
        "created_at": "2025-01-20T15:30:00Z"
      },
      {
        "id": 2,
        "revision_number": 2,
        "content": "# The Golden Age of Islam...",
        "edit_comment": "Fixed typos and improved formatting",
        "is_minor": true,
        "is_current": false,
        "changes": {
          "added_lines": 2,
          "removed_lines": 2,
          "modified_sections": ["Introduction"]
        },
        "creator": {
          "id": 1,
          "username": "admin",
          "display_name": "Administrator"
        },
        "created_at": "2025-01-20T12:00:00Z"
      }
    ],
    "total_revisions": 3,
    "pagination": {...}
  },
  "message": "Revision history retrieved successfully"
}
```

### **GET /api/v1/wiki/pages/{slug}/history/{revision_id}**

Get specific revision.

**Parameters:**
- `slug` (string, required): Page URL slug
- `revision_id` (int, required): Revision ID

**Response:**
```json
{
  "success": true,
  "data": {
    "revision": {
      "id": 2,
      "revision_number": 2,
      "content": "# The Golden Age of Islam...",
      "edit_comment": "Fixed typos and improved formatting",
      "is_minor": true,
      "is_current": false,
      "changes": {...},
      "creator": {...},
      "created_at": "2025-01-20T12:00:00Z"
    },
    "page": {...}
  },
  "message": "Revision retrieved successfully"
}
```

### **GET /api/v1/wiki/pages/{slug}/history/compare/{revision1}/{revision2}**

Compare two revisions.

**Parameters:**
- `slug` (string, required): Page URL slug
- `revision1` (int, required): First revision ID
- `revision2` (int, required): Second revision ID

**Response:**
```json
{
  "success": true,
  "data": {
    "comparison": {
      "revision1": {...},
      "revision2": {...},
      "diff": {
        "added_lines": 15,
        "removed_lines": 3,
        "changed_lines": 8,
        "unchanged_lines": 45,
        "html_diff": "<div class='diff'>...</div>",
        "text_diff": "@@ -1,5 +1,5 @@\n- Old line\n+ New line"
      }
    }
  },
  "message": "Revision comparison completed successfully"
}
```

### **POST /api/v1/wiki/pages/{slug}/history/revert/{revision_id}**

Revert page to specific revision.

**Parameters:**
- `slug` (string, required): Page URL slug
- `revision_id` (int, required): Revision ID to revert to

**Request Body:**
```json
{
  "edit_comment": "Reverted to previous version due to incorrect information"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "new_revision_id": 4,
    "revision_number": 4,
    "reverted_from": 2
  },
  "message": "Page reverted successfully"
}
```

## 🏷️ **Tag Management API**

### **GET /api/v1/wiki/tags**

List all tags.

**Query Parameters:**
- `sort_by` (string, optional): Sort field (name, usage_count, created_at)
- `sort_order` (string, optional): Sort order (asc, desc)
- `limit` (int, optional): Maximum tags to return

**Response:**
```json
{
  "success": true,
  "data": {
    "tags": [
      {
        "id": 1,
        "name": "History",
        "slug": "history",
        "description": "Historical content and articles",
        "color": "#007cba",
        "usage_count": 45,
        "creator": {...},
        "created_at": "2025-01-20T10:00:00Z"
      }
    ],
    "total_tags": 8,
    "popular_tags": [...]
  },
  "message": "Tags retrieved successfully"
}
```

### **GET /api/v1/wiki/tags/{slug}**

Get tag details and associated pages.

**Parameters:**
- `slug` (string, required): Tag URL slug

**Response:**
```json
{
  "success": true,
  "data": {
    "tag": {
      "id": 1,
      "name": "History",
      "slug": "history",
      "description": "Historical content and articles",
      "color": "#007cba",
      "usage_count": 45,
      "creator": {...},
      "created_at": "2025-01-20T10:00:00Z"
    },
    "pages": [...],
    "related_tags": [...]
  },
  "message": "Tag data retrieved successfully"
}
```

## 📊 **Analytics API**

### **GET /api/v1/wiki/analytics/overview**

Get wiki overview statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "statistics": {
      "total_pages": 100,
      "published_pages": 85,
      "draft_pages": 10,
      "archived_pages": 5,
      "total_categories": 5,
      "total_tags": 8,
      "total_revisions": 250,
      "total_views": 5000,
      "total_users": 45
    },
    "trends": {
      "pages_created_this_month": 15,
      "pages_edited_this_month": 45,
      "views_this_month": 1200,
      "new_users_this_month": 8
    },
    "top_content": {
      "most_viewed_pages": [...],
      "most_edited_pages": [...],
      "most_popular_categories": [...],
      "most_used_tags": [...]
    }
  },
  "message": "Analytics data retrieved successfully"
}
```

### **GET /api/v1/wiki/analytics/pages/{slug}**

Get page-specific analytics.

**Parameters:**
- `slug` (string, required): Page URL slug

**Query Parameters:**
- `period` (string, optional): Time period (day, week, month, year)

**Response:**
```json
{
  "success": true,
  "data": {
    "page": {
      "id": 1,
      "title": "The Golden Age of Islam",
      "slug": "golden-age-of-islam"
    },
    "views": {
      "total": 150,
      "this_period": 25,
      "trend": "increasing",
      "daily_breakdown": [...]
    },
    "engagement": {
      "average_time_on_page": 180,
      "bounce_rate": 0.15,
      "social_shares": 12
    },
    "referrers": [...],
    "user_agents": [...]
  },
  "message": "Page analytics retrieved successfully"
}
```

## 🔒 **Page Protection API**

### **POST /api/v1/wiki/pages/{slug}/lock**

Lock page for editing.

**Parameters:**
- `slug` (string, required): Page URL slug

**Request Body:**
```json
{
  "lock_type": "edit",
  "lock_reason": "Page under review",
  "expires_at": "2025-02-20T10:00:00Z"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "lock_id": 1,
    "lock_type": "edit",
    "lock_reason": "Page under review",
    "locked_by": {...},
    "locked_at": "2025-01-20T16:00:00Z",
    "expires_at": "2025-02-20T10:00:00Z"
  },
  "message": "Page locked successfully"
}
```

### **POST /api/v1/wiki/pages/{slug}/unlock**

Unlock page for editing.

**Parameters:**
- `slug` (string, required): Page URL slug

**Response:**
```json
{
  "success": true,
  "data": {
    "unlocked_at": "2025-01-20T17:00:00Z"
  },
  "message": "Page unlocked successfully"
}
```

## 👥 **User Management API**

### **GET /api/v1/wiki/users/{user_id}/contributions**

Get user contributions.

**Parameters:**
- `user_id` (int, required): User ID

**Query Parameters:**
- `type` (string, optional): Contribution type (pages, edits, categories)
- `page` (int, optional): Page number for pagination
- `per_page` (int, optional): Items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "display_name": "Administrator",
      "avatar": "/avatars/admin.jpg"
    },
    "contributions": {
      "pages_created": 25,
      "pages_edited": 150,
      "categories_created": 3,
      "total_edits": 175
    },
    "recent_activity": [...],
    "statistics": {...}
  },
  "message": "User contributions retrieved successfully"
}
```

## 🚨 **Error Codes and Messages**

### **Common Error Codes**

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 400 | Request validation failed |
| `UNAUTHORIZED` | 401 | Authentication required |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `CONFLICT` | 409 | Resource conflict |
| `RATE_LIMITED` | 429 | Too many requests |
| `INTERNAL_ERROR` | 500 | Internal server error |

### **Validation Error Example**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": {
      "title": "Title is required and must be between 3 and 255 characters",
      "content": "Content is required and must be at least 10 characters",
      "category_id": "Category ID must be a valid integer"
    }
  }
}
```

### **Permission Error Example**
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You do not have permission to edit this page",
    "details": {
      "required_permission": "wiki.edit",
      "current_user_permissions": ["wiki.read"]
    }
  }
}
```

## 📱 **Rate Limiting**

### **Rate Limits**
- **Anonymous Users**: 100 requests per hour
- **Authenticated Users**: 1000 requests per hour
- **API Keys**: 5000 requests per hour

### **Rate Limit Headers**
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 950
X-RateLimit-Reset: 1642680000
```

### **Rate Limit Exceeded Response**
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMITED",
    "message": "Rate limit exceeded. Please try again later.",
    "details": {
      "limit": 1000,
      "reset_time": "2025-01-20T18:00:00Z"
    }
  }
}
```

## 🔄 **Webhooks and Notifications**

### **Webhook Events**
- `page.created` - New page created
- `page.updated` - Page updated
- `page.deleted` - Page deleted
- `category.created` - New category created
- `user.registered` - New user registered

### **Webhook Payload Example**
```json
{
  "event": "page.created",
  "timestamp": "2025-01-20T16:00:00Z",
  "data": {
    "page": {
      "id": 123,
      "title": "New Page",
      "slug": "new-page",
      "creator": {...}
    }
  }
}
```

## 📚 **SDK and Libraries**

### **Official SDKs**
- **PHP SDK**: `composer require islamwiki/wiki-sdk`
- **JavaScript SDK**: `npm install @islamwiki/wiki-sdk`
- **Python SDK**: `pip install islamwiki-wiki-sdk`

### **PHP SDK Example**
```php
use IslamWiki\WikiSdk\WikiClient;

$client = new WikiClient([
    'api_key' => 'your-api-key',
    'base_url' => 'https://your-domain.com/api/v1'
]);

// Get page
$page = $client->pages()->get('golden-age-of-islam');

// Create page
$newPage = $client->pages()->create([
    'title' => 'New Page',
    'content' => '# Content here...',
    'category_id' => 1
]);
```

### **JavaScript SDK Example**
```javascript
import { WikiClient } from '@islamwiki/wiki-sdk';

const client = new WikiClient({
    apiKey: 'your-api-key',
    baseUrl: 'https://your-domain.com/api/v1'
});

// Get page
const page = await client.pages.get('golden-age-of-islam');

// Create page
const newPage = await client.pages.create({
    title: 'New Page',
    content: '# Content here...',
    categoryId: 1
});
```

## 🧪 **Testing and Development**

### **Test Environment**
- **Base URL**: `https://staging.your-domain.com/api/v1/wiki`
- **Test Data**: Separate test database with sample content
- **Rate Limits**: Higher limits for testing

### **Testing Tools**
- **Postman Collection**: Import our Postman collection
- **cURL Examples**: Command-line examples for all endpoints
- **Test Credentials**: Dedicated test user accounts

### **cURL Examples**

#### **Get Page**
```bash
curl -X GET "https://your-domain.com/api/v1/wiki/pages/golden-age-of-islam" \
  -H "Accept: application/json"
```

#### **Create Page**
```bash
curl -X POST "https://your-domain.com/api/v1/wiki/pages" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "title": "New Page",
    "content": "# Content here...",
    "category_id": 1
  }'
```

#### **Search Content**
```bash
curl -X GET "https://your-domain.com/api/v1/wiki/search?q=islamic+mathematics" \
  -H "Accept: application/json"
```

## 📞 **Support and Resources**

### **API Documentation**
- **Interactive Docs**: Swagger/OpenAPI documentation
- **Code Examples**: GitHub repository with examples
- **SDK Documentation**: Comprehensive SDK guides

### **Support Channels**
- **Developer Forum**: Community support and discussions
- **Email Support**: Technical support for API issues
- **GitHub Issues**: Bug reports and feature requests

### **Additional Resources**
- **Changelog**: API version history and updates
- **Migration Guide**: Guide for upgrading between versions
- **Best Practices**: Recommended implementation patterns

---

**You're now ready to integrate with the IslamWiki Wiki API!** 🚀

This reference covers all available endpoints, authentication methods, and response formats. Remember to:
- Use proper authentication headers
- Handle rate limiting gracefully
- Implement proper error handling
- Follow API best practices

**Happy coding!** 💻✨

---

**Last Updated:** 2025-01-20  
**Version:** 0.0.2.1  
**Status:** Complete API Reference ✅  
**Next:** Style Guide and Administration Guide 📋 