<?php

namespace Chatbot\Core;

/**
 * Database Migration Manager
 */
class DatabaseMigration {
    private DatabaseInterface $db;
    private string $migrationsTable = 'migrations';

    public function __construct(DatabaseInterface $db) {
        $this->db = $db;
    }

    public function runMigrations(): bool {
        if (!$this->db->connect()) {
            return false;
        }

        $this->createMigrationsTable();

        $migrations = $this->getMigrations();
        $executed = $this->getExecutedMigrations();

        foreach ($migrations as $migration) {
            if (!in_array($migration['name'], $executed)) {
                if ($this->runMigration($migration)) {
                    $this->recordMigration($migration['name']);
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    private function createMigrationsTable(): void {
        if (!$this->db->tableExists($this->migrationsTable)) {
            $this->db->createTable($this->migrationsTable, [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name' => 'VARCHAR(255) UNIQUE NOT NULL',
                'executed_at' => 'DATETIME NOT NULL'
            ]);
        }
    }

    private function getMigrations(): array {
        return [
            [
                'name' => '001_create_conversations_table',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS conversations (
                        id VARCHAR(255) PRIMARY KEY,
                        messages TEXT NOT NULL,
                        metadata TEXT,
                        created_at DATETIME NOT NULL,
                        updated_at DATETIME NOT NULL,
                        INDEX idx_updated_at (updated_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                "
            ],
            [
                'name' => '002_create_analytics_table',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS analytics (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        date DATE NOT NULL,
                        provider VARCHAR(50) NOT NULL,
                        model VARCHAR(100),
                        requests INT DEFAULT 0,
                        tokens INT DEFAULT 0,
                        UNIQUE KEY unique_date_provider_model (date, provider, model),
                        INDEX idx_date (date)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                "
            ],
            [
                'name' => '003_create_settings_table',
                'sql' => "
                    CREATE TABLE IF NOT EXISTS settings (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        key_name VARCHAR(100) UNIQUE NOT NULL,
                        value TEXT,
                        updated_at DATETIME NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                "
            ],
        ];
    }

    private function getExecutedMigrations(): array {
        try {
            $result = $this->db->query("SELECT name FROM {$this->migrationsTable}");
            return array_column($result, 'name');
        } catch (\Exception $e) {
            return [];
        }
    }

    private function runMigration(array $migration): bool {
        try {
            return $this->db->execute($migration['sql']);
        } catch (\Exception $e) {
            error_log("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    private function recordMigration(string $name): void {
        $this->db->execute(
            "INSERT INTO {$this->migrationsTable} (name, executed_at) VALUES (?, ?)",
            [$name, date('Y-m-d H:i:s')]
        );
    }
}
