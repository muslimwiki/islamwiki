# Contributing to IslamWiki

Thank you for your interest in contributing to IslamWiki! We welcome contributions from everyone who shares our goal of creating a comprehensive, accurate, and accessible Islamic knowledge platform.

## Getting Started

1. **Fork** the repository on GitHub
2. **Clone** your fork locally
3. Set up your development environment (see [Development Setup](#development-setup))
4. Create a new branch for your changes
5. Make your changes and commit them
6. Push your changes to your fork
7. Open a **Pull Request** to the `main` branch

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- MariaDB 10.3+ or MySQL 8.0+
- Git

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/muslimwiki/islamwiki.git
   cd islamwiki
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file and configure it:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and other settings
   ```

4. Set the application key:
   ```bash
   php artisan key:generate
   ```

5. Run database migrations:
   ```bash
   php artisan migrate
   ```

6. Start the development server:
   ```bash
   php artisan serve
   ```

## Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Keep methods small and focused on a single responsibility
- Write tests for new features and bug fixes

## Pull Request Guidelines

- Keep PRs focused on a single feature or bug fix
- Write clear, concise commit messages
- Reference any related issues in your PR
- Ensure all tests pass before submitting
- Update documentation as needed

## Reporting Issues

When reporting issues, please include:
- A clear description of the issue
- Steps to reproduce
- Expected vs. actual behavior
- Any relevant error messages
- Your environment details (PHP version, database, etc.)

## License

By contributing to IslamWiki, you agree that your contributions will be licensed under the [AGPL-3.0-or-later license](LICENSE).

## Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project, you agree to abide by its terms.

## Getting Help

If you have any questions or need help, please open an issue or join our community chat.
