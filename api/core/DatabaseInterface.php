<?php

namespace Chatbot\Core;

/**
 * Database Interface - Abstraction for database operations
 */
interface DatabaseInterface {
    public function connect(): bool;
    public function disconnect(): void;
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): bool;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
    public function tableExists(string $table): bool;
    public function createTable(string $table, array $columns): bool;
}
