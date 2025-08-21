<?php

declare(strict_types=1);

/**
 * Database Migration Runner
 * 
 * This script runs all database migrations to set up the authentication system.
 * 
 * @package IslamWiki\Scripts
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "🚀 **IslamWiki Database Migration Runner**\n";
echo "==========================================\n\n";

try {
    // Initialize database connection
    echo "📡 Connecting to database...\n";
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "✅ Database connection established\n\n";
    
    // Create migration tables if they don't exist
    echo "📋 Setting up migration system...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mizan_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ Migration system ready\n\n";
    
    // Run migrations
    echo "🔄 Running migrations...\n";
    
    $migrations = [
        '0001_create_auth_tables' => __DIR__ . '/../database/migrations/0001_create_auth_tables.php',
    ];
    
    $batch = 1;
    $executed = 0;
    
    foreach ($migrations as $name => $file) {
        if (!file_exists($file)) {
            echo "❌ Migration file not found: {$file}\n";
            continue;
        }
        
        // Check if migration already executed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mizan_migrations WHERE migration = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetchColumn() > 0) {
            echo "⏭️  Migration already executed: {$name}\n";
            continue;
        }
        
        echo "🔄 Running migration: {$name}\n";
        
        // Include migration file
        require_once $file;
        
        // Create migration instance
        $migrationClass = 'CreateAuthTables';
        if (!class_exists($migrationClass)) {
            echo "❌ Migration class not found: {$migrationClass}\n";
            continue;
        }
        
        // Create mock database object for migration
        $mockDatabase = new class($pdo) {
            private PDO $pdo;
            
            public function __construct(PDO $pdo) {
                $this->pdo = $pdo;
            }
            
            public function table(string $table) {
                return new class($this->pdo, $table) {
                    private PDO $pdo;
                    private string $table;
                    private array $where = [];
                    private array $orderBy = [];
                    private int $limit = 0;
                    
                    public function __construct(PDO $pdo, string $table) {
                        $this->pdo = $pdo;
                        $this->table = $table;
                    }
                    
                    public function where(string $column, string $operator, $value) {
                        $this->where[] = [$column, $operator, $value];
                        return $this;
                    }
                    
                    public function orderBy(string $column, string $direction = 'asc') {
                        $this->orderBy[] = [$column, $direction];
                        return $this;
                    }
                    
                    public function limit(int $limit) {
                        $this->limit = $limit;
                        return $this;
                    }
                    
                    public function first() {
                        $sql = "SELECT * FROM {$this->table}";
                        $params = [];
                        
                        if (!empty($this->where)) {
                            $sql .= " WHERE ";
                            $conditions = [];
                            foreach ($this->where as $where) {
                                $conditions[] = "{$where[0]} {$where[1]} ?";
                                $params[] = $where[2];
                            }
                            $sql .= implode(' AND ', $conditions);
                        }
                        
                        if (!empty($this->orderBy)) {
                            $sql .= " ORDER BY ";
                            $orders = [];
                            foreach ($this->orderBy as $order) {
                                $orders[] = "{$order[0]} {$order[1]}";
                            }
                            $sql .= implode(', ', $orders);
                        }
                        
                        if ($this->limit > 0) {
                            $sql .= " LIMIT {$this->limit}";
                        }
                        
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        return $stmt->fetch();
                    }
                    
                    public function get() {
                        $sql = "SELECT * FROM {$this->table}";
                        $params = [];
                        
                        if (!empty($this->where)) {
                            $sql .= " WHERE ";
                            $conditions = [];
                            foreach ($this->where as $where) {
                                $conditions[] = "{$where[0]} {$where[1]} ?";
                                $params[] = $where[2];
                            }
                            $sql .= implode(' AND ', $conditions);
                        }
                        
                        if (!empty($this->orderBy)) {
                            $sql .= " ORDER BY ";
                            $orders = [];
                            foreach ($this->orderBy as $order) {
                                $orders[] = "{$order[0]} {$order[1]}";
                            }
                            $sql .= implode(', ', $orders);
                        }
                        
                        if ($this->limit > 0) {
                            $sql .= " LIMIT {$this->limit}";
                        }
                        
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        return $stmt->fetchAll();
                    }
                    
                    public function count() {
                        $sql = "SELECT COUNT(*) FROM {$this->table}";
                        $params = [];
                        
                        if (!empty($this->where)) {
                            $sql .= " WHERE ";
                            $conditions = [];
                            foreach ($this->where as $where) {
                                $conditions[] = "{$where[0]} {$where[1]} ?";
                                $params[] = $where[2];
                            }
                            $sql .= implode(' AND ', $conditions);
                        }
                        
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        return (int) $stmt->fetchColumn();
                    }
                    
                    public function insert(array $data) {
                        $columns = array_keys($data);
                        $values = array_values($data);
                        $placeholders = str_repeat('?,', count($values) - 1) . '?';
                        
                        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
                        
                        $stmt = $this->pdo->prepare($sql);
                        return $stmt->execute($values);
                    }
                    
                    public function update(array $data) {
                        $set = [];
                        $params = [];
                        
                        foreach ($data as $column => $value) {
                            $set[] = "{$column} = ?";
                            $params[] = $value;
                        }
                        
                        $sql = "UPDATE {$this->table} SET " . implode(',', $set);
                        
                        if (!empty($this->where)) {
                            $sql .= " WHERE ";
                            $conditions = [];
                            foreach ($this->where as $where) {
                                $conditions[] = "{$where[0]} {$where[1]} ?";
                                $params[] = $where[2];
                            }
                            $sql .= implode(' AND ', $conditions);
                        }
                        
                        $stmt = $this->pdo->prepare($sql);
                        return $stmt->execute($params);
                    }
                    
                    public function delete() {
                        $sql = "DELETE FROM {$this->table}";
                        $params = [];
                        
                        if (!empty($this->where)) {
                            $sql .= " WHERE ";
                            $conditions = [];
                            foreach ($this->where as $where) {
                                $conditions[] = "{$where[0]} {$where[1]} ?";
                                $params[] = $where[2];
                            }
                            $sql .= implode(' AND ', $conditions);
                        }
                        
                        $stmt = $this->pdo->prepare($sql);
                        return $stmt->execute($params);
                    }
                    
                    public function updateOrInsert(array $unique, array $data) {
                        // Check if record exists
                        $this->where = [];
                        foreach ($unique as $column => $value) {
                            $this->where($column, '=', $value);
                        }
                        
                        if ($this->first()) {
                            return $this->update($data);
                        } else {
                            return $this->insert(array_merge($unique, $data));
                        }
                    }
                };
            }
            
            public function getSchema() {
                return new class($this->pdo) {
                    private PDO $pdo;
                    
                    public function __construct(PDO $pdo) {
                        $this->pdo = $pdo;
                    }
                    
                    public function create(string $table, callable $callback) {
                        $blueprint = new class($this->pdo, $table) {
                            private PDO $pdo;
                            private string $table;
                            private array $columns = [];
                            private array $indexes = [];
                            private array $foreignKeys = [];
                            
                            public function __construct(PDO $pdo, string $table) {
                                $this->pdo = $pdo;
                                $this->table = $table;
                            }
                            
                            public function id() {
                                $this->columns[] = "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
                                return $this;
                            }
                            
                            public function string(string $column, int $length = 255) {
                                $this->columns[] = "{$column} VARCHAR({$length})";
                                return $this;
                            }
                            
                            public function text(string $column) {
                                $this->columns[] = "{$column} TEXT";
                                return $this;
                            }
                            
                            public function boolean(string $column) {
                                $this->columns[] = "{$column} BOOLEAN";
                                return $this;
                            }
                            
                            public function timestamp(string $column) {
                                $this->columns[] = "{$column} TIMESTAMP";
                                return $this;
                            }
                            
                            public function integer(string $column) {
                                $this->columns[] = "{$column} INT";
                                return $this;
                            }
                            
                            public function json(string $column) {
                                $this->columns[] = "{$column} JSON";
                                return $this;
                            }
                            
                            public function unique() {
                                $this->columns[count($this->columns) - 1] .= " UNIQUE";
                                return $this;
                            }
                            
                            public function nullable() {
                                $this->columns[count($this->columns) - 1] .= " NULL";
                                return $this;
                            }
                            
                            public function default($value) {
                                if (is_string($value)) {
                                    $value = "'{$value}'";
                                }
                                $this->columns[count($this->columns) - 1] .= " DEFAULT {$value}";
                                return $this;
                            }
                            
                            public function index(array $columns) {
                                $this->indexes[] = $columns;
                                return $this;
                            }
                            
                            public function foreign(string $column) {
                                $this->foreignKeys[] = $column;
                                return $this;
                            }
                            
                            public function references(string $column) {
                                $this->foreignKeys[count($this->foreignKeys) - 1] .= " REFERENCES {$column}";
                                return $this;
                            }
                            
                            public function on(string $table) {
                                $this->foreignKeys[count($this->foreignKeys) - 1] .= "({$table})";
                                return $this;
                            }
                            
                            public function onDelete(string $action) {
                                $this->foreignKeys[count($this->foreignKeys) - 1] .= " ON DELETE {$action}";
                                return $this;
                            }
                            
                            public function softDeletes() {
                                $this->columns[] = "deleted_at TIMESTAMP NULL";
                                return $this;
                            }
                            
                            public function timestamps() {
                                $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                                $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                                return $this;
                            }
                            
                            public function build() {
                                $sql = "CREATE TABLE {$this->table} (" . implode(',', $this->columns);
                                
                                if (!empty($this->indexes)) {
                                    foreach ($this->indexes as $index) {
                                        $sql .= ", INDEX (" . implode(',', $index) . ")";
                                    }
                                }
                                
                                if (!empty($this->foreignKeys)) {
                                    $sql .= ", " . implode(',', $this->foreignKeys);
                                }
                                
                                $sql .= ")";
                                
                                $this->pdo->exec($sql);
                            }
                        };
                        
                        $callback($blueprint);
                        $blueprint->build();
                    }
                    
                    public function dropIfExists(string $table) {
                        $this->pdo->exec("DROP TABLE IF EXISTS {$table}");
                    }
                };
            }
        };
        
        // Run migration
        $migration = new $migrationClass($mockDatabase);
        $migration->up();
        
        // Mark migration as executed
        $stmt = $pdo->prepare("INSERT INTO mizan_migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$name, $batch]);
        
        echo "✅ Migration completed: {$name}\n";
        $executed++;
        
    }
    
    echo "\n🎉 **Migration Summary**\n";
    echo "========================\n";
    echo "✅ Total migrations executed: {$executed}\n";
    echo "✅ All migrations completed successfully!\n\n";
    
    // Create default admin user
    echo "👑 Creating default admin user...\n";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO mizan_users (username, email, password_hash, first_name, last_name, is_active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute(['admin', 'admin@islamwiki.com', $adminPassword, 'Admin', 'User']);
    $adminUserId = $pdo->lastInsertId();
    
    // Assign admin role
    $stmt = $pdo->prepare("
        INSERT INTO mizan_user_roles (user_id, role_id, created_at, updated_at)
        VALUES (?, (SELECT id FROM mizan_roles WHERE name = 'admin'), NOW(), NOW())
    ");
    
    $stmt->execute([$adminUserId]);
    
    echo "✅ Default admin user created:\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "   Email: admin@islamwiki.com\n\n";
    
    echo "🚀 **Setup Complete!**\n";
    echo "=====================\n";
    echo "Your IslamWiki authentication system is now ready!\n";
    echo "You can now:\n";
    echo "1. Login with admin/admin123\n";
    echo "2. Access the admin panel\n";
    echo "3. Create new users and manage roles\n";
    echo "4. Test the authentication system\n\n";
    
} catch (Exception $e) {
    echo "❌ **Error occurred:**\n";
    echo "=====================\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nPlease check your database configuration and try again.\n";
    exit(1);
} 