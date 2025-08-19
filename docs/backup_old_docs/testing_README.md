# Testing Guidelines

This guide explains how testing is organized and how to run the different kinds of tests under `maintenance/tests/`.

## Test Directory Layout

```
maintenance/tests/
├── cli/                 # CLI test runners (direct PHP scripts)
├── Unit/                # Unit-level tests (small, fast)
│   └── Database/
├── Integration/         # Integration and end-to-end scenarios
└── web/                 # Browser-style test scripts (served locally for manual testing)
```

Supporting test artifacts (created at runtime) are ignored by Git:
- `maintenance/tests/data/`
- `maintenance/tests/tmp/`
- `maintenance/tests/output/`
- `maintenance/tests/.cache/`
- `maintenance/tests/coverage/`
- `maintenance/tests/logs/`

See: `.gitignore` rules for these paths.

## Conventions

- Tests are plain PHP scripts designed to be runnable without a heavy framework.
- Dev autoload: `composer.json` maps `Tests\` to `maintenance/tests/` via `autoload-dev` for any helper classes.
- Prefer small, deterministic tests. Log output to `maintenance/tests/logs/` when needed.
- Name web scripts with a clear prefix like `test-*.php` to indicate purpose.

## Running Tests

### 1) CLI Test Runners (`maintenance/tests/cli/`)
Run the desired script directly with PHP. Examples:

```bash
# Salah time calculations
php maintenance/tests/cli/TestSalahTimes.php

# Minimal salah time smoke check
php maintenance/tests/cli/TestMinimalSalahTime.php

# Logger behavior
php maintenance/tests/cli/TestLogger.php
```

### 2) Unit Tests (`maintenance/tests/Unit/`)
These are small PHP scripts that exercise individual components. Examples:

```bash
# Database connectivity checks
php maintenance/tests/Unit/Database/DatabaseConnectionTest.php

# Simple DB integration scenario
php maintenance/tests/Unit/Database/IntegrationTest.php

# Repository layer behavior
php maintenance/tests/Unit/QuranAyahRepositoryTest.php
```

If any script requires environment variables (e.g., database credentials), set them before running:

```bash
APP_ENV=development DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=islamwiki DB_USERNAME=... DB_PASSWORD=... \
php maintenance/tests/Unit/Database/IntegrationTest.php
```

### 3) Integration Tests (`maintenance/tests/Integration/`)
Reserved for cross-component scenarios. Run them similarly as plain PHP scripts:

```bash
# example placeholder (add your integration scripts here)
php maintenance/tests/Integration/SomeIntegrationTest.php
```

### 4) Web Tests (`maintenance/tests/web/`)
These scripts were moved out of `public/` for security and are not web-accessible by default. To run them locally in a safe way, use PHP's built-in server and browse to the pages:

```bash
# Serve ONLY the tests directory on a local port
php -S 127.0.0.1:8081 -t maintenance/tests/web
```

Then open your browser to a specific test, for example:
- http://127.0.0.1:8081/test-islam-router-comprehensive.php
- http://127.0.0.1:8081/test-homepage.php
- http://127.0.0.1:8081/test-router.php

This keeps tests local and out of the public web root while enabling manual, browser-driven verification.

## Frequently Used Test Scripts

- Router: `maintenance/tests/web/test-islam-router-comprehensive.php`
- Home controller: `maintenance/tests/web/test-home-controller.php`
- DB connectivity: `maintenance/tests/web/test-db-connection.php`
- Session flows: `maintenance/tests/web/test-session.php`, `maintenance/tests/web/test-session-manager.php`

## Troubleshooting

- Ensure dependencies are installed: `composer install`
- Clear caches if behavior seems stale: remove `maintenance/tests/.cache/` and `maintenance/tests/output/`
- Check logs under `maintenance/tests/logs/` for details
- Verify environment variables for DB-related tests

## Future Improvements

- Add lightweight assertions/util helpers under `maintenance/tests/_support/`
- Optional: re-introduce PHPUnit config for automated suites when desired
- Document each web test's purpose in a brief header block at the top of the file
