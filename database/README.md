# Database Migrations

This directory contains database migration files and scripts for the IslamWiki system.

## Wiki System Migration

### Files
- `migrations/2025_01_20_000001_create_wiki_tables.sql` - SQL migration for wiki system tables
- `migrate_wiki_tables.php` - PHP script to run the migration
- `test_connection.php` - Test database connectivity before migration
- `rollback_wiki_tables.php` - Remove all wiki tables (use with caution)

### Running the Migration

1. **Ensure database connection is configured** in your environment variables:
   ```bash
   DB_HOST=localhost
   DB_DATABASE=islamwiki
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. **Test the database connection**:
   ```bash
   php database/test_connection.php
   ```

3. **Run the migration script**:
   ```bash
   php database/migrate_wiki_tables.php
   ```

3. **Verify the tables were created**:
   ```sql
   SHOW TABLES LIKE 'wiki_%';
   ```

### Tables Created

The migration creates the following tables:

- `wiki_pages` - Main wiki page content
- `wiki_categories` - Content categorization
- `wiki_revisions` - Page version history
- `wiki_page_categories` - Many-to-many relationship
- `wiki_tags` - Content tagging system
- `wiki_page_tags` - Many-to-many relationship
- `wiki_page_views` - Page view tracking
- `wiki_search_logs` - Search analytics
- `wiki_page_locks` - Page protection
- `wiki_page_watches` - User notifications

### Rollback

To rollback the migration, you can use the rollback script:

```bash
php database/rollback_wiki_tables.php
```

**⚠️  Warning**: This will delete ALL wiki data permanently!

Alternatively, you can manually drop all wiki tables:

```sql
DROP TABLE IF EXISTS 
    wiki_page_watches,
    wiki_page_locks,
    wiki_search_logs,
    wiki_page_views,
    wiki_page_tags,
    wiki_tags,
    wiki_page_categories,
    wiki_revisions,
    wiki_categories,
    wiki_pages;
```

## Future Migrations

For future migrations, we'll implement a proper migration system using:
- `IslamWiki\Core\Database\Database base class
- `IslamWiki\Core\Database\Database for table operations
- Migration versioning and rollback support 