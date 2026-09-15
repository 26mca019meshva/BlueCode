<?php
/**
 * SupplyGuard AI - Database Core Class
 * 
 * PDO database wrapper with prepared statements.
 * Implements singleton pattern for connection reuse.
 */

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;
    private ?PDOStatement $stmt = null;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Prepare a query
     */
    public function query(string $sql): self {
        $this->stmt = $this->pdo->prepare($sql);
        return $this;
    }

    /**
     * Bind a value to a parameter
     */
    public function bind(string $param, mixed $value, ?int $type = null): self {
        if ($type === null) {
            $type = match(true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute the prepared statement
     */
    public function execute(): bool {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get result set as array of objects
     */
    public function resultSet(): array {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get single record
     */
    public function single(): mixed {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get row count
     */
    public function rowCount(): int {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->pdo->rollBack();
    }

    /**
     * Get PDO instance directly (for advanced use)
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }

    // Prevent cloning
    private function __clone() {}
}
