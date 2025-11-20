<?php

namespace Chatbot\Core;

/**
 * Conversation Repository Interface
 */
interface ConversationRepositoryInterface {
    public function save(string $conversationId, array $messages, array $metadata = []): bool;
    public function load(string $conversationId): ?array;
    public function delete(string $conversationId): bool;
    public function listAll(): array;
    public function updateMetadata(string $conversationId, array $metadata): bool;
}
