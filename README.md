# IslamWiki

A lightweight, self-contained Islamic knowledge platform built with PHP 8.0+ and SQLite.

## Features

- 🚀 Simple, clean interface
- 💾 SQLite database (no separate database server required)
- 🔄 PSR-7/PSR-15 compliant request handling
- 🛠 Built-in development server
- 🔒 Secure admin dashboard with authentication
- 📱 Responsive design for all devices
- ⚡ Fast and lightweight
- 🔄 Easy to deploy and maintain

## Requirements

- PHP 8.0 or higher
- SQLite 3.0 or higher
- Composer (for dependency management)

## Quick Start

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/islamwiki.git
   cd islamwiki
   ```
2. Install dependencies
   ```bash
   composer install
   ```
3. Start the development server
   ```bash
   php -S 0.0.0.0:80 -t public
   ```
4. Access the application
   - Main site: http://localhost
   - Admin panel: http://localhost/admin
     - Default credentials: admin / admin123 (change these in production!)

## Development

### Project Structure

```
.
├── admin/              # Admin panel files
│   ├── assets/         # CSS, JS, and images
│   └── includes/       # Shared PHP includes
├── config/            # Configuration files
├── public/            # Web server document root
│   └── admin/         # Admin panel entry point
├── src/               # Source code
│   └── Http/          # HTTP handlers and routing
├── tests/             # Test files
├── vendor/            # Composer dependencies
├── .env.example       # Example environment variables
├── .htaccess          # Apache configuration
├── CHANGELOG.md       # Version history
├── composer.json      # PHP dependencies
└── README.md          # This file
```

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
