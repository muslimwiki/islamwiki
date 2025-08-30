# Contribution Guidelines

Thank you for considering contributing to IslamWiki! We appreciate your time and effort in helping us build a better platform for Islamic knowledge.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Commit Message Guidelines](#commit-message-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)
- [Code Review Process](#code-review-process)
- [Community](#community)

## Code of Conduct

This project and everyone participating in it is governed by our [Code of Conduct](code-of-conduct.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

- Ensure the bug was not already reported by searching in [GitHub Issues](https://github.com/muslimwiki/islamwiki/issues).
- If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/muslimwiki/islamwiki/issues/new).
- Use a clear and descriptive title for the issue.
- Include as many details as possible in the issue template.

### Suggesting Enhancements

- Use the feature request template when creating a new issue.
- Clearly describe the use case and benefits of the enhancement.
- Include any relevant screenshots or mockups.

### Your First Code Contribution

Looking for your first contribution? Look for issues labeled `good first issue` or `help wanted` in the issue tracker.

## Development Workflow

1. **Fork** the repository on GitHub
2. **Clone** your fork locally:
   ```bash
   git clone git@github.com:your-username/islamwiki.git
   cd islamwiki
   ```
3. **Add the upstream remote**:
   ```bash
   git remote add upstream git@github.com:muslimwiki/islamwiki.git
   ```
4. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```
5. **Make your changes**
6. **Run tests** to ensure nothing is broken
7. **Commit your changes** following the commit message guidelines
8. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
9. **Open a Pull Request** from your fork to the main repository

## Coding Standards

### PHP

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Use type hints and return type declarations where possible
- Add PHPDoc blocks for all classes, methods, and functions
- Keep methods small and focused on a single responsibility
- Use dependency injection instead of facades when possible

### JavaScript

- Follow [Airbnb JavaScript Style Guide](https://github.com/airbnb/javascript)
- Use ES6+ features
- Prefer `const` and `let` over `var`
- Use template literals instead of string concatenation

### CSS/SCSS

- Follow [BEM](http://getbem.com/) methodology
- Use meaningful class names
- Keep selectors short and specific
- Use variables for colors, spacing, and other common values

### Database

- Use migrations for all database schema changes
- Add indexes for frequently queried columns
- Use foreign keys for relationships
- Add comments for complex queries

## Commit Message Guidelines

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, etc.)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries

### Examples

```
feat(auth): add two-factor authentication

Add support for TOTP-based two-factor authentication for enhanced security.

Closes #123
```

```
fix(api): prevent race condition in user registration

Resolve an issue where concurrent registration requests could create duplicate users.

Fixes #456
```

## Pull Request Process

1. Ensure any install or build dependencies are removed before the end of the layer when doing a build.
2. Update the README.md with details of changes to the interface, including new environment variables, exposed ports, useful file locations, and container parameters.
3. Increase the version numbers in any example files and the README.md to the new version that this Pull Request would represent.
4. The PR must pass all CI checks before it can be merged.
5. You may merge the Pull Request once you have the sign-off of two other developers, or if you do not have permission to do that, you may request the reviewer to merge it for you.

## Reporting Bugs

When reporting bugs, please include:

1. A clear, descriptive title
2. Steps to reproduce the issue
3. Expected vs. actual behavior
4. Screenshots if applicable
5. Your environment (PHP version, database, OS, etc.)

## Suggesting Enhancements

When suggesting enhancements, please include:

1. A clear, descriptive title
2. A description of the problem you're trying to solve
3. A detailed explanation of the proposed solution
4. Any alternative solutions you've considered
5. Any additional context or screenshots

## Code Review Process

1. All code submissions go through a peer review process
2. At least one approval is required before merging
3. CI must pass all tests
4. Code should be well-documented and follow our coding standards
5. New features should include tests

## Community

- Join our [community forum](https://community.islamwiki.org)
- Follow us on [Twitter](https://twitter.com/islamwiki)
- Subscribe to our [newsletter](https://islamwiki.org/newsletter)

## Credits

Thank you to all the people who have already contributed to IslamWiki!
