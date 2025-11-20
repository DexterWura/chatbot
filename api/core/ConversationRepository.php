<?php

namespace Chatbot\Core;

/**
 * Conversation Repository - Repository Pattern
 * Manages conversation persistence and retrieval (File-based implementation)
 */
class ConversationRepository implements ConversationRepositoryInterface {
    private string $storagePath;
    private array $conversations = [];
    private ?LoggerInterface $logger;

    public function __construct(string $storagePath, ?LoggerInterface $logger = null) {
        $this->storagePath = rtrim($storagePath, '/') . '/';
        $this->logger = $logger;
        
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function save(string $conversationId, array $messages, array $metadata = []): bool {
        try {
            $data = [
                'id' => $conversationId,
                'messages' => $messages,
                'metadata' => array_merge([
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], $metadata),
            ];

            $file = $this->storagePath . $conversationId . '.json';
            $result = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
            
            if ($result !== false) {
                $this->conversations[$conversationId] = $data;
            }
            
            return $result !== false;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error("Failed to save conversation: " . $e->getMessage());
            }
            return false;
        }
    }

    public function load(string $conversationId): ?array {
        if (isset($this->conversations[$conversationId])) {
            return $this->conversations[$conversationId];
        }

        $file = $this->storagePath . $conversationId . '.json';
        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $this->conversations[$conversationId] = $data;
        }

        return $data;
    }

    public function delete(string $conversationId): bool {
        $file = $this->storagePath . $conversationId . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
        unset($this->conversations[$conversationId]);
        return true;
    }

    public function listAll(): array {
        $conversations = [];
        $files = glob($this->storagePath . '*.json');
        
        foreach ($files as $file) {
            $id = basename($file, '.json');
            $data = $this->load($id);
            if ($data) {
                $conversations[] = [
                    'id' => $id,
                    'title' => $data['metadata']['title'] ?? 'Untitled',
                    'updated_at' => $data['metadata']['updated_at'] ?? '',
                    'message_count' => count($data['messages'] ?? [])
                ];
            }
        }

        usort($conversations, fn($a, $b) => strtotime($b['updated_at']) - strtotime($a['updated_at']));
        return $conversations;
    }

    public function updateMetadata(string $conversationId, array $metadata): bool {
        $data = $this->load($conversationId);
        if (!$data) {
            return false;
        }

        $data['metadata'] = array_merge($data['metadata'], $metadata, [
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->save($conversationId, $data['messages'], $data['metadata']);
    }
}
