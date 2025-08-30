<?php

namespace IslamWiki\Core;

use Dotenv\Dotenv;

class Application
{
    private string $basePath;
    private static ?self $instance = null;
    private array $config = [];
    private ?\PDO $db = null;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        self::$instance = $this;
        
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initializeErrorHandling();
        $this->initializeDatabase();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been initialized');
        }
        return self::$instance;
    }

    public function run(): void
    {
        // Set headers for security
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // Start session with secure settings
        $this->startSession();
        
        // TODO: Add routing and request handling
        echo 'Welcome to IslamWiki!';
    }

    private function loadEnvironment(): void
    {
        if (!file_exists($this->basePath . '/.env')) {
            throw new \RuntimeException('.env file not found. Please create one based on .env.example');
        }
        
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();
    }

    private function loadConfiguration(): void
    {
        $configFile = $this->basePath . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }

    private function initializeErrorHandling(): void
    {
        error_reporting(E_ALL);
        
        if (getenv('APP_DEBUG') === 'true') {
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', $this->basePath . '/logs/error.log');
        }
        
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    private function initializeDatabase(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_DATABASE') ?: 'islamwiki'
        );
        
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $this->db = new \PDO(
            $dsn,
            getenv('DB_USERNAME') ?: 'root',
            getenv('DB_PASSWORD') ?: '',
            $options
        );
    }

    private function startSession(): void
    {
        $sessionConfig = [
            'name' => 'islamwiki_session',
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'use_strict_mode' => true,
            'cookie_samesite' => 'Lax',
        ];
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start($sessionConfig);
        }
    }

    public function handleException(\Throwable $e): void
    {
        error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        if (getenv('APP_DEBUG') !== 'true') {
            http_response_code(500);
            exit('An error occurred. Please try again later.');
        }
        
        throw $e;
    }

    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
    {
        error_log("Error [$errno] $errstr in $errfile on line $errline");
        return true; // Don't execute PHP internal error handler
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            error_log('Fatal error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
        }
    }

    public function getDb(): \PDO
    {
        return $this->db;
    }

    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? $default;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
