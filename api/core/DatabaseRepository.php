<?php

namespace Chatbot\Core;

/**
 * Database-backed Conversation Repository
 */
class DatabaseRepository implements ConversationRepositoryInterface {
    private DatabaseInterface $db;
    private string $tableName;
    private ?LoggerInterface $logger;

    public function __construct(
        DatabaseInterface $db,
        string $tableName = 'conversations',
        ?LoggerInterface $logger = null
    ) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->logger = $logger;
    }

    public function save(string $conversationId, array $messages, array $metadata = []): bool {
        try {
            $existing = $this->load($conversationId);
            
            $data = [
                'id' => $conversationId,
                'messages' => json_encode($messages),
                'metadata' => json_encode(array_merge([
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], $metadata)),
            ];

            if ($existing) {
                $updatedAt = date('Y-m-d H:i:s');
                $sql = "UPDATE {$this->tableName} SET messages = ?, metadata = ?, updated_at = ? WHERE id = ?";
                return $this->db->execute($sql, [
                    $data['messages'],
                    $data['metadata'],
                    $updatedAt,
                    $conversationId
                ]);
            } else {
                $createdAt = $data['metadata']['created_at'] ?? date('Y-m-d H:i:s');
                $updatedAt = $data['metadata']['updated_at'] ?? date('Y-m-d H:i:s');
                $sql = "INSERT INTO {$this->tableName} (id, messages, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, ?)";
                return $this->db->execute($sql, [
                    $conversationId,
                    $data['messages'],
                    $data['metadata'],
                    $createdAt,
                    $updatedAt
                ]);
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Failed to save conversation: " . $e->getMessage());
            }
            return false;
        }
    }

    public function load(string $conversationId): ?array {
        try {
            $result = $this->db->query(
                "SELECT * FROM {$this->tableName} WHERE id = ?",
                [$conversationId]
            );

            if (empty($result)) {
                return null;
            }

            $row = $result[0];
            return [
                'id' => $row['id'],
                'messages' => json_decode($row['messages'], true) ?? [],
                'metadata' => json_decode($row['metadata'], true) ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Failed to load conversation: " . $e->getMessage());
            }
            return null;
        }
    }

    public function delete(string $conversationId): bool {
        try {
            return $this->db->execute(
                "DELETE FROM {$this->tableName} WHERE id = ?",
                [$conversationId]
            );
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Failed to delete conversation: " . $e->getMessage());
            }
            return false;
        }
    }

    public function listAll(): array {
        try {
            $result = $this->db->query(
                "SELECT id, messages, metadata, created_at, updated_at FROM {$this->tableName} ORDER BY updated_at DESC"
            );

            $conversations = [];
            foreach ($result as $row) {
                $metadata = json_decode($row['metadata'] ?? '{}', true) ?? [];
                $messages = json_decode($row['messages'] ?? '[]', true) ?? [];
                $conversations[] = [
                    'id' => $row['id'],
                    'title' => $metadata['title'] ?? 'Untitled',
                    'updated_at' => $row['updated_at'] ?? $metadata['updated_at'] ?? '',
                    'message_count' => count($messages)
                ];
            }

            return $conversations;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Failed to list conversations: " . $e->getMessage());
            }
            return [];
        }
    }

    public function updateMetadata(string $conversationId, array $metadata): bool {
        $existing = $this->load($conversationId);
        if (!$existing) {
            return false;
        }

        $existingMetadata = $existing['metadata'] ?? [];
        $mergedMetadata = array_merge($existingMetadata, $metadata, [
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->save($conversationId, $existing['messages'], $mergedMetadata);
    }
}
