<?php

namespace Chatbot\Core;

use PDO;
use PDOException;

/**
 * PDO Database Implementation
 */
class PDODatabase implements DatabaseInterface {
    private ?PDO $connection = null;
    private string $dsn;
    private string $username;
    private string $password;
    private array $options;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password,
        string $driver = 'mysql',
        array $options = []
    ) {
        $this->username = $username;
        $this->password = $password;
        
        switch ($driver) {
            case 'mysql':
                $this->dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
                break;
            case 'pgsql':
                $this->dsn = "pgsql:host=$host;dbname=$database";
                break;
            case 'sqlite':
                $this->dsn = "sqlite:$database";
                break;
            default:
                throw new \InvalidArgumentException("Unsupported database driver: $driver");
        }

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->options = array_merge($defaultOptions, $options);
    }

    public function connect(): bool {
        try {
            $this->connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            return true;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }

    public function disconnect(): void {
        $this->connection = null;
    }

    public function query(string $sql, array $params = []): array {
        if (!$this->connection) {
            throw new \RuntimeException("Database not connected");
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function execute(string $sql, array $params = []): bool {
        if (!$this->connection) {
            throw new \RuntimeException("Database not connected");
        }

        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction(): bool {
        if (!$this->connection) {
            return false;
        }
        return $this->connection->beginTransaction();
    }

    public function commit(): bool {
        if (!$this->connection) {
            return false;
        }
        return $this->connection->commit();
    }

    public function rollback(): bool {
        if (!$this->connection) {
            return false;
        }
        return $this->connection->rollBack();
    }

    public function tableExists(string $table): bool {
        try {
            $result = $this->query("SHOW TABLES LIKE ?", [$table]);
            return !empty($result);
        } catch (\Exception $e) {
            // Try alternative for different databases
            try {
                $result = $this->query(
                    "SELECT name FROM sqlite_master WHERE type='table' AND name=?",
                    [$table]
                );
                return !empty($result);
            } catch (\Exception $e2) {
                return false;
            }
        }
    }

    public function createTable(string $table, array $columns): bool {
        $columnDefs = [];
        foreach ($columns as $name => $definition) {
            $columnDefs[] = "$name $definition";
        }
        $sql = "CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $columnDefs) . ")";
        return $this->execute($sql);
    }

    public function getConnection(): ?PDO {
        return $this->connection;
    }
}
