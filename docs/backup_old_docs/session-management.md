# Session Management

This document describes the session management system in IslamWiki, which provides secure user authentication and session handling.

## Overview

The session management system is built around the `SessionManager` class and provides:

- Secure session configuration with HTTP-only cookies
- Session regeneration for security against session fixation
- User authentication state management
- CSRF token generation and verification
- Remember me functionality

## Components

### SessionManager

The core session management class located at `src/Core/Session/SessionManager.php`.

#### Key Features

- **Secure Configuration**: HTTP-only, SameSite cookies
- **Session Regeneration**: Automatic regeneration every 5 minutes
- **CSRF Protection**: Built-in token generation and verification
- **Remember Me**: Secure persistent login functionality

#### Configuration

```php
$session = new SessionManager([
    'name' => 'islamwiki_session',
    'lifetime' => 86400, // 24 hours
    'secure' => false,    // Set to true in production
    'http_only' => true,
    'same_site' => 'Lax'
]);
```

#### Environment Variables

```env
SESSION_NAME=islamwiki_session
SESSION_LIFETIME=86400
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Lax
```

### Authentication Middleware

Protects routes that require authentication.

```php
use IslamWiki\Http\Middleware\AuthenticationMiddleware;

// Apply to routes
$router->addMiddleware(new AuthenticationMiddleware($session));
```

### CSRF Middleware

Protects forms from cross-site request forgery attacks.

```php
use IslamWiki\Http\Middleware\CsrfMiddleware;

// Apply to routes
$router->addMiddleware(new CsrfMiddleware($session));
```

## Usage

### Starting a Session

```php
$session = $container->get('session');
$session->start();
```

### User Login

```php
$session->login($userId, $username, $isAdmin);
```

### User Logout

```php
$session->logout();
```

### Checking Authentication

```php
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    $username = $session->getUsername();
    $isAdmin = $session->isAdmin();
}
```

### CSRF Protection

#### In Forms

```twig
<form method="POST" action="/login">
    <input type="hidden" name="_token" value="{{ csrf_token }}">
    <!-- form fields -->
</form>
```

#### In Controllers

```php
// Generate token
$token = $session->getCsrfToken();

// Verify token
if ($session->verifyCsrfToken($request->getPostParam('_token'))) {
    // Process form
}
```

### Remember Me

```php
// Set remember token
$token = bin2hex(random_bytes(32));
$user->setAttribute('remember_token', $token);
$user->save();
$session->setRememberToken($token);

// Set cookie
setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
```

## Security Features

### Session Security

- **HTTP-Only Cookies**: Prevents XSS attacks from accessing session data
- **SameSite=Lax**: Protects against CSRF while allowing legitimate navigation
- **Session Regeneration**: Prevents session fixation attacks
- **Secure Configuration**: Configurable for production environments

### CSRF Protection

- **Token Generation**: Cryptographically secure random tokens
- **Token Verification**: Constant-time comparison using `hash_equals()`
- **Form Protection**: All POST/PUT/PATCH/DELETE requests protected
- **Error Handling**: User-friendly error pages for token mismatches

### Authentication Security

- **Password Hashing**: Bcrypt with proper salt
- **Session Management**: Secure session handling with regeneration
- **Remember Me**: Secure token-based persistent login
- **Input Validation**: Comprehensive form validation

## Best Practices

### Development

1. **Always use CSRF tokens** in forms
2. **Validate user input** before processing
3. **Use secure session configuration** in production
4. **Implement proper error handling** for authentication failures

### Production

1. **Enable secure cookies** (`SESSION_SECURE=true`)
2. **Use HTTPS** for all authentication
3. **Set appropriate session lifetime** based on security requirements
4. **Monitor session activity** for suspicious behavior

## Troubleshooting

### Common Issues

1. **Session not starting**: Check if headers have already been sent
2. **CSRF token mismatch**: Ensure token is included in form
3. **Remember me not working**: Check cookie settings and token storage
4. **Session timeout**: Adjust `SESSION_LIFETIME` configuration

### Debugging

```php
// Check session status
if ($session->isLoggedIn()) {
    echo "User logged in: " . $session->getUsername();
} else {
    echo "No user logged in";
}

// Check CSRF token
$token = $session->getCsrfToken();
echo "CSRF token: " . substr($token, 0, 20) . "...";
```

## API Reference

### SessionManager Methods

- `start()`: Start the session with secure configuration
- `login(int $userId, string $username, bool $isAdmin)`: Log in a user
- `logout()`: Log out the current user
- `isLoggedIn()`: Check if user is authenticated
- `getUserId()`: Get current user ID
- `getUsername()`: Get current username
- `isAdmin()`: Check if current user is admin
- `getCsrfToken()`: Get current CSRF token
- `verifyCsrfToken(string $token)`: Verify CSRF token
- `setRememberToken(string $token)`: Set remember me token
- `getRememberToken()`: Get remember me token

### Session Data

- `user_id`: Current user ID
- `username`: Current username
- `is_admin`: Whether user is admin
- `logged_in_at`: Login timestamp
- `csrf_token`: Current CSRF token
- `remember_token`: Remember me token
- `last_regeneration`: Last session regeneration time 