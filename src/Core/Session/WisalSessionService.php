<?php

declare(strict_types=1);

namespace IslamWiki\Core\Session;

use IslamWiki\Core\Database\MizanDatabase;

/**
 * Wisal Session Service (وصل - Connection)
 * 
 * Manages user sessions, connections, and state persistence.
 * Part of the Application Layer in the Islamic core architecture.
 * 
 * @package IslamWiki\Core\Session
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WisalSessionService
{
    private MizanDatabase $database;
    private array $config;
    private bool $isStarted = false;

    public function __construct(MizanDatabase $database, array $config = [])
    {
        $this->database = $database;
        $this->config = array_merge([
            'driver' => 'file',
            'lifetime' => 7200,
            'expire_on_close' => false,
            'encrypt' => false,
            'files' => __DIR__ . '/../../storage/framework/sessions',
            'table' => 'mizan_sessions',
            'cookie' => 'islamwiki_session',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'http_only' => true,
            'same_site' => 'lax',
        ], $config);

        $this->configureSession();
    }

    /**
     * Configure session settings
     */
    private function configureSession(): void
    {
        // Set session configuration
        ini_set('session.gc_maxlifetime', $this->config['lifetime'] * 60);
        ini_set('session.cookie_lifetime', $this->config['lifetime'] * 60);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', $this->config['http_only'] ? 1 : 0);
        ini_set('session.cookie_secure', $this->config['secure'] ? 1 : 0);
        ini_set('session.cookie_samesite', $this->config['same_site']);

        // Set session name
        session_name($this->config['cookie']);

        // Set session save handler
        if ($this->config['driver'] === 'database') {
            $this->setDatabaseHandler();
        } elseif ($this->config['driver'] === 'file') {
            $this->setFileHandler();
        }
    }

    /**
     * Set database session handler
     */
    private function setDatabaseHandler(): void
    {
        $handler = new WisalDatabaseHandler($this->database, $this->config);
        session_set_save_handler($handler, true);
    }

    /**
     * Set file session handler
     */
    private function setFileHandler(): void
    {
        if (!is_dir($this->config['files'])) {
            mkdir($this->config['files'], 0755, true);
        }
        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', $this->config['files']);
    }

    /**
     * Start the session
     */
    public function start(): bool
    {
        if ($this->isStarted) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->isStarted = true;
            return true;
        }

        if (session_start()) {
            $this->isStarted = true;
            $this->regenerateIdIfNeeded();
            return true;
        }

        return false;
    }

    /**
     * Regenerate session ID if needed
     */
    private function regenerateIdIfNeeded(): void
    {
        $regenerateTime = $this->get('_regenerate_time', 0);
        $currentTime = time();

        if ($currentTime - $regenerateTime > 300) { // 5 minutes
            $this->regenerateId();
            $this->set('_regenerate_time', $currentTime);
        }
    }

    /**
     * Get session value
     */
    public function get(string $key, $default = null)
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, $value): void
    {
        if (!$this->isStarted) {
            $this->start();
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Check if session has key
     */
    public function has(string $key): bool
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        if (!$this->isStarted) {
            $this->start();
        }

        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        if (!$this->isStarted) {
            $this->start();
        }

        $_SESSION = [];
    }

    /**
     * Regenerate session ID
     */
    public function regenerateId(): bool
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return session_regenerate_id(true);
    }

    /**
     * Destroy the session
     */
    public function destroy(): bool
    {
        if (!$this->isStarted) {
            return true;
        }

        $this->clear();
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        return session_destroy();
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return session_id();
    }

    /**
     * Set session ID
     */
    public function setId(string $id): bool
    {
        if ($this->isStarted) {
            return false;
        }

        session_id($id);
        return true;
    }

    /**
     * Get session name
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Set session name
     */
    public function setName(string $name): bool
    {
        if ($this->isStarted) {
            return false;
        }

        return session_name($name);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->isStarted && session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Flash a value to the session
     */
    public function flash(string $key, $value): void
    {
        $this->set("flash.{$key}", $value);
    }

    /**
     * Get flashed value
     */
    public function getFlash(string $key, $default = null)
    {
        $value = $this->get("flash.{$key}", $default);
        $this->remove("flash.{$key}");
        return $value;
    }

    /**
     * Check if flash value exists
     */
    public function hasFlash(string $key): bool
    {
        return $this->has("flash.{$key}");
    }

    /**
     * Keep flash values
     */
    public function keepFlash(array $keys): void
    {
        foreach ($keys as $key) {
            if ($this->hasFlash($key)) {
                $value = $this->getFlash($key);
                $this->flash($key, $value);
            }
        }
    }

    /**
     * Get flash data
     */
    public function getFlashData(): array
    {
        $flash = [];
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'flash.') === 0) {
                $flash[substr($key, 6)] = $value;
            }
        }
        return $flash;
    }

    /**
     * Set user in session
     */
    public function setUser(int $userId, array $userData = []): void
    {
        $this->set('user_id', $userId);
        $this->set('user_data', $userData);
        $this->set('authenticated_at', time());
    }

    /**
     * Get current user ID
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    /**
     * Get current user data
     */
    public function getUserData(): array
    {
        return $this->get('user_data', []);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->has('user_id') && $this->get('user_id') > 0;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->remove('user_id');
        $this->remove('user_data');
        $this->remove('authenticated_at');
        $this->regenerateId();
    }

    /**
     * Get session configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set session configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->configureSession();
    }
}

/**
 * Database Session Handler for Wisal
 */
class WisalDatabaseHandler implements \SessionHandlerInterface
{
    private MizanDatabase $database;
    private array $config;

    public function __construct(MizanDatabase $database, array $config)
    {
        $this->database = $database;
        $this->config = $config;
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $session = $this->database->table($this->config['table'])
            ->where('id', $id)
            ->first();

        if ($session && $session->last_activity > (time() - ($this->config['lifetime'] * 60))) {
            return $session->payload;
        }

        return '';
    }

    public function write($id, $data): bool
    {
        $this->database->table($this->config['table'])->updateOrInsert(
            ['id' => $id],
            [
                'payload' => $data,
                'last_activity' => time(),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );

        return true;
    }

    public function destroy($id): bool
    {
        $this->database->table($this->config['table'])
            ->where('id', $id)
            ->delete();

        return true;
    }

    public function gc($max_lifetime): int|false
    {
        $cutoff = time() - $max_lifetime;
        
        $deleted = $this->database->table($this->config['table'])
            ->where('last_activity', '<', $cutoff)
            ->delete();

        return $deleted;
    }
} 