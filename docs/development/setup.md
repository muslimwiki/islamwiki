# Development Environment Setup

This guide will help you set up a local development environment for IslamWiki.

## Prerequisites

- PHP 8.1 or higher
- Composer (PHP package manager)
- MariaDB 10.3+ or MySQL 8.0+
- Node.js 16+ and npm (for frontend assets)
- Git

## 1. Clone the Repository

```bash
git clone https://github.com/muslimwiki/islamwiki.git
cd islamwiki
```

## 2. Install PHP Dependencies

```bash
composer install
```

## 3. Configure Environment

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

2. Generate an application key:
   ```bash
   php artisan key:generate
   ```

3. Update the `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=islamwiki
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

## 4. Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE islamwiki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```
   This will create all necessary tables and add default data.

## 5. Storage Permissions

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## 6. Install Frontend Dependencies

```bash
npm install
npm run dev
```

## 7. Start the Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 8. Default Admin Account

A default admin account is created during database seeding:

- **Email**: admin@islamwiki.org
- **Password**: admin123

**Important**: Change this password immediately after first login.

## Development Tools

### Code Style

We use PHP CS Fixer to maintain code style:

```bash
composer cs-fix
```

### Testing

Run the test suite:

```bash
composer test
```

### Debugging

1. Xdebug is recommended for PHP debugging
2. Laravel Telescope is included for local development
3. Debug bar is available for request inspection

## IDE Configuration

### PHPStorm/IntelliJ

1. Set PHP language level to 8.1+
2. Enable PHP CS Fixer:
   - Go to Settings > PHP > Quality Tools > PHP CS Fixer
   - Set the path to `vendor/bin/php-cs-fixer`
   - Enable "On Save" actions

### VS Code

Recommended extensions:
- PHP Intelephense
- PHP Debug
- Laravel Extension Pack
- PHP CS Fixer

## Common Issues

### Database Connection Issues
- Verify your `.env` credentials
- Ensure MySQL/MariaDB is running
- Check user permissions

### Permission Issues
- Run `composer dump-autoload`
- Clear cache: `php artisan cache:clear`
- Check storage permissions

### Frontend Assets Not Updating
- Run `npm run dev` or `npm run watch`
- Clear browser cache

## Next Steps

1. Read the [contribution guidelines](../contributing/guidelines.md)
2. Check out the [development workflow](./workflow.md)
3. Explore the [API documentation](../api/overview.md)
4. Join our [community chat](https://chat.islamwiki.org)
