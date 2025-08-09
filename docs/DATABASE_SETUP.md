# IslamWiki Database Setup Guide

## Overview

IslamWiki now has full database integration with the following features:

- ✅ User authentication and management
- ✅ Wiki page creation and editing
- ✅ Page revision tracking
- ✅ User profiles and contributions
- ✅ Database migrations system
- ✅ Comprehensive test suite

## Quick Start

### 1. Database Configuration

Create a `.env` file in the root directory with your database settings:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=islamwiki
DB_USERNAME=root
DB_PASSWORD=your_password_here
DB_CHARSET=utf8mb4

# Application Settings
APP_NAME=IslamWiki
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### 2. Run Database Setup

Use the automated setup script to create the database and run migrations:

```bash
php scripts/setup_database.php
```

This script will:
- Test database connectivity
- Create the database if it doesn't exist
- Run all migrations
- Create sample data (admin user and sample pages)

### 3. Run Integration Tests

Verify that everything is working correctly:

```bash
php tests/Unit/Database/IntegrationTest.php
```

## Database Schema

### Users Table
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email address
- `password_hash` - Hashed password
- `display_name` - User's display name
- `bio` - User biography
- `avatar_url` - Profile picture URL
- `is_admin` - Admin privileges
- `is_active` - Account status
- `email_verified_at` - Email verification timestamp
- `last_login_at` - Last login timestamp
- `remember_token` - Remember me token
- `created_at`, `updated_at` - Timestamps

### Pages Table
- `id` - Primary key
- `title` - Page title
- `slug` - URL slug
- `content` - Page content
- `content_format` - Content format (markdown, html, etc.)
- `namespace` - Page namespace
- `parent_id` - Parent page ID (for hierarchical pages)
- `is_locked` - Page lock status
- `view_count` - Page view counter
- `created_at`, `updated_at` - Timestamps

### Page Revisions Table
- `id` - Primary key
- `page_id` - Foreign key to pages
- `user_id` - Foreign key to users (who made the edit)
- `title` - Page title at time of revision
- `content` - Page content at time of revision
- `content_format` - Content format
- `comment` - Edit comment
- `is_minor_edit` - Minor edit flag
- `ip_address` - IP address of editor
- `user_agent` - User agent of editor
- `created_at` - Revision timestamp

### Additional Tables
- `user_watchlist` - User page watchlists
- `categories` - Page categories
- `page_categories` - Page-category relationships
- `media_files` - Uploaded media files

## Sample Data

The setup script creates the following sample data:

### Admin User
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@islamwiki.local`
- **Role**: Administrator

### Sample Pages
1. **Welcome to IslamWiki** (`/welcome`)
   - Introduction to the wiki
   - Feature overview
   - Getting started guide

2. **About Islam** (`/about-islam`)
   - Core beliefs
   - Five pillars
   - Basic Islamic concepts

## API Endpoints

### Authentication
- `GET /login` - Show login form
- `POST /login` - Process login
- `GET /register` - Show registration form
- `POST /register` - Process registration
- `POST /logout` - Logout user

### Pages
- `GET /pages` - List all pages
- `GET /pages/create` - Show create page form
- `POST /pages` - Create new page
- `GET /pages/{id}` - View page
- `GET /pages/{id}/edit` - Edit page form
- `PUT /pages/{id}` - Update page
- `DELETE /pages/{id}` - Delete page
- `GET /pages/{id}/history` - Page revision history

### User Profile
- `GET /profile` - User profile
- `PUT /profile` - Update profile
- `PUT /profile/password` - Change password

## Development

### Running Tests

```bash
# Run database integration tests
php tests/Unit/Database/IntegrationTest.php

# Run database connection test
php tests/Unit/Database/DatabaseConnectionTest.php
```

### Creating Migrations

Migrations are stored in `database/migrations/`. Each migration should:

1. Extend the `Migration` class
2. Implement `up()` and `down()` methods
3. Use the schema builder for table operations

Example:
```php
<?php
declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema()->create('example_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists('example_table');
    }
};
```

### Model Usage

```php
// Create a user
$user = new User($connection, [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secure_password',
    'display_name' => 'John Doe'
]);
$user->save();

// Find a user
$user = User::findByUsername('john_doe', $connection);

// Create a page
$page = new Page($connection, [
    'title' => 'My Page',
    'slug' => 'my-page',
    'content' => '# My Page\n\nContent here...',
    'content_format' => 'markdown'
]);
$page->save();
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check your database credentials in `.env`
   - Ensure MySQL/MariaDB is running
   - Verify the database exists

2. **Migration Errors**
   - Check that all migration files have correct syntax
   - Ensure database user has CREATE/DROP privileges
   - Run `php scripts/setup_database.php` to reset

3. **Model Errors**
   - Verify table structure matches model expectations
   - Check that required fields are provided
   - Ensure database connection is working

### Debug Mode

Enable debug mode in your `.env` file:
```ini
APP_DEBUG=true
```

This will show detailed error messages and stack traces.

## Next Steps

With the database integration complete, the next priorities are:

1. **Enhanced Authentication**
   - Email verification
   - Password reset functionality
   - Social login integration

2. **Advanced Page Features**
   - Wiki text parsing
   - Media uploads
   - Page templates

3. **Search Functionality**
   - Full-text search
   - Search suggestions
   - Advanced filters

4. **User Interface**
   - Rich text editor
   - Drag-and-drop uploads
   - Real-time collaboration

5. **API Development**
   - REST API endpoints
   - API authentication
   - Rate limiting

## Support

If you encounter issues:

1. Check the error logs in `logs/`
2. Run the integration tests
3. Verify your database configuration
4. Check the troubleshooting section above

For development questions, refer to the main README.md file. 