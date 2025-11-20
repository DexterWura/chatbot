<?php

namespace Chatbot\Core;

/**
 * Session Manager - Manages user sessions and conversation threads
 */
class SessionManager {
    private ConversationRepository $repository;
    private string $currentSessionId;
    private array $sessions = [];

    public function __construct(ConversationRepository $repository) {
        $this->repository = $repository;
        $this->currentSessionId = $this->generateSessionId();
    }

    public function getCurrentSessionId(): string {
        return $this->currentSessionId;
    }

    public function createSession(string $title = 'New Chat'): string {
        $sessionId = $this->generateSessionId();
        $this->repository->save($sessionId, [
            ['role' => 'system', 'content' => 'You are a helpful assistant.']
        ], ['title' => $title]);
        
        $this->sessions[$sessionId] = [
            'id' => $sessionId,
            'title' => $title,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $sessionId;
    }

    public function loadSession(string $sessionId): ?array {
        return $this->repository->load($sessionId);
    }

    public function saveSession(string $sessionId, array $messages, array $metadata = []): bool {
        return $this->repository->save($sessionId, $messages, $metadata);
    }

    public function listSessions(): array {
        return $this->repository->listAll();
    }

    public function deleteSession(string $sessionId): bool {
        return $this->repository->delete($sessionId);
    }

    public function switchSession(string $sessionId): bool {
        $session = $this->loadSession($sessionId);
        if ($session) {
            $this->currentSessionId = $sessionId;
            return true;
        }
        return false;
    }

    private function generateSessionId(): string {
        return 'session_' . uniqid() . '_' . time();
    }
}
