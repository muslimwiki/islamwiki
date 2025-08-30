# Development Environment Setup

This guide will help you set up a local development environment for IslamWiki.

## Prerequisites

- PHP 8.0 or higher
- Composer (PHP package manager)
- SQLite 3.0 or higher (included with PHP)
- Git

## 1. Clone the Repository

```bash
git clone https://github.com/yourusername/islamwiki.git
cd islamwiki
```

## 2. Install Dependencies

```bash
composer install
```

## 3. Configure Environment

1. The application uses SQLite by default, so no additional database setup is required.
2. The database file will be automatically created at `database/database.sqlite` on first run.
3. Ensure the `database` directory is writable by the web server:
   ```bash
   chmod -R 775 database/
   ```

## 4. Start the Development Server

```bash
php -S 0.0.0.0:8000 run-app.php
```

Then open `http://localhost:8000` in your browser.

## 5. Development Workflow

- The main application entry point is `run-app.php`
- Routes are defined in `config/routes.php`
- Controllers are in `src/Http/Controllers`
- Models are in `app/Models`
- Database migrations are in `database/migrations`

## 6. Testing

To run the test suite:

```bash
composer test
```

## 7. Debugging

- Check the error log at `/tmp/php_errors.log`
- The application uses PSR-3 compatible logging
- Enable debug mode by setting `display_errors = On` in your `php.ini`

## 8. Contributing

Please read [CONTRIBUTING.md](../CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

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
