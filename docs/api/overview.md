# API Documentation

## Base URL

All API endpoints are relative to the base URL:

```
https://api.islamwiki.org/v1
```

## Authentication

Most API endpoints require authentication. The API uses Bearer Token authentication.

### Obtaining an Access Token

1. **Login** to get an access token:
   ```http
   POST /auth/login
   ```
   
   **Request Body:**
   ```json
   {
     "email": "user@example.com",
     "password": "yourpassword"
   }
   ```

   **Response:**
   ```json
   {
     "access_token": "your_access_token_here",
     "token_type": "Bearer",
     "expires_in": 3600,
     "user": {
       "id": 1,
       "name": "John Doe",
       "email": "user@example.com",
       "avatar": "https://..."
     }
   }
   ```

2. **Include the token** in subsequent requests:
   ```
   Authorization: Bearer your_access_token_here
   ```

### Refreshing Tokens

Access tokens expire after 1 hour. Use the refresh token to get a new access token:

```http
POST /auth/refresh
```

**Headers:**
```
Authorization: Bearer your_refresh_token_here
```

## Rate Limiting

- **Rate Limit:** 60 requests per minute per IP address
- **Response Headers:**
  - `X-RateLimit-Limit`: Request limit per time window
  - `X-RateLimit-Remaining`: Remaining requests in current window
  - `X-RateLimit-Reset`: When the rate limit resets (UTC epoch seconds)

## Pagination

Endpoints that return lists of items are paginated. The response includes pagination metadata:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  },
  "links": {
    "first": "https://api.islamwiki.org/v1/endpoint?page=1",
    "last": "https://api.islamwiki.org/v1/endpoint?page=5",
    "prev": null,
    "next": "https://api.islamwiki.org/v1/endpoint?page=2"
  }
}
```

### Pagination Parameters

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

## Filtering

Many list endpoints support filtering using query parameters:

```
GET /api/pages?status=published&category=islamic-law&sort=-created_at
```

### Common Filter Operators

- `field=value`: Equals
- `field[gt]=value`: Greater than
- `field[gte]=value`: Greater than or equal
- `field[lt]=value`: Less than
- `field[lte]=value`: Less than or equal
- `field[like]=value`: Case-insensitive search
- `field[in]=value1,value2`: Value in list
- `-field`: Sort descending (e.g., `-created_at`)

## Error Handling

### Error Response Format

```json
{
  "error": {
    "code": "validation_error",
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."],
      "password": ["The password must be at least 8 characters."]
    },
    "status_code": 422
  }
}
```

### Common HTTP Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `204 No Content`: Resource deleted successfully
- `400 Bad Request`: Invalid request (e.g., malformed JSON)
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation error
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## Response Format

### Success Response

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Islam",
    "content": "...",
    "created_at": "2023-01-01T00:00:00Z",
    "updated_at": "2023-01-01T00:00:00Z"
  }
}
```

### Collection Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Page 1",
      "excerpt": "..."
    },
    {
      "id": 2,
      "title": "Page 2",
      "excerpt": "..."
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 2,
    "total": 75
  },
  "links": {
    "first": "https://api.islamwiki.org/v1/pages?page=1",
    "last": "https://api.islamwiki.org/v1/pages?page=5",
    "prev": null,
    "next": "https://api.islamwiki.org/v1/pages?page=2"
  }
}
```

## Versioning

The API is versioned using the URL path. The current version is `v1`.

Example: `https://api.islamwiki.org/v1/pages`

## Data Types

- **Dates**: ISO 8601 format (e.g., `2023-01-01T00:00:00Z`)
- **Booleans**: `true` or `false` (not `"true"` or `"false"`)
- **Numbers**: JSON numbers (not strings)
- **Files**: Multipart form data for file uploads

## Webhooks

Webhooks allow you to receive real-time updates when certain events occur in the system.

### Available Events

- `page.created`: A new page is created
- `page.updated`: An existing page is updated
- `page.deleted`: A page is deleted
- `user.registered`: A new user registers
- `user.updated`: A user's details are updated

### Webhook Payload

```json
{
  "event": "page.updated",
  "data": {
    "id": 123,
    "title": "Updated Page",
    "url": "https://islamwiki.org/pages/updated-page"
  },
  "occurred_at": "2023-01-01T12:00:00Z"
}
```

### Setting Up Webhooks

1. Go to your account settings
2. Navigate to "Webhooks"
3. Click "Add Webhook"
4. Enter the target URL and select events to subscribe to
5. Save the webhook

## SDKs and Libraries

Official SDKs are available for:

- JavaScript/Node.js
- Python
- PHP
- Java
- Ruby

See the [SDK documentation](sdk.md) for more details.

## Support

For API support, please contact:

- **Email**: support@islamwiki.org
- **Documentation**: https://docs.islamwiki.org/api
