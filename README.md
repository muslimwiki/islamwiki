# IslamWiki

A lightweight, self-contained Islamic knowledge platform built with PHP 8.0+ and SQLite.

## Features

- Simple, clean interface
- SQLite database (no separate database server required)
- PSR-7/PSR-15 compliant request handling
- Built-in development server
- Easy to deploy and maintain

## Requirements

- PHP 8.0 or higher
- SQLite 3.0 or higher
- Composer (for dependency management)

## Quick Start

1. Clone the repository
2. Run `composer install`
3. Start the development server: `php -S 0.0.0.0:80 run-app.php`
4. Open `http://localhost` in your browser

## Development

### Project Structure

- `/app` - Application models and core classes
- `/config` - Configuration files
- `/database` - Database migrations and SQLite database
- `/public` - Web server document root
- `/releases` - Release notes for each version
- `/src` - Source code
- `/tests` - Test files

### Running Tests

```bash
composer test
```

## Documentation

For detailed documentation, please see the [/docs](/docs) directory.

## License

MIT License - See [LICENSE](LICENSE) for details.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.
