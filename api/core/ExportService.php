<?php

namespace Chatbot\Core;

/**
 * Export Service - Handles conversation export/import
 */
class ExportService {
    private ConversationRepository $repository;

    public function __construct(ConversationRepository $repository) {
        $this->repository = $repository;
    }

    public function export(string $sessionId, string $format = 'json'): ?string {
        $session = $this->repository->load($sessionId);
        if (!$session) {
            return null;
        }

        switch (strtolower($format)) {
            case 'json':
                return json_encode($session, JSON_PRETTY_PRINT);
            
            case 'txt':
                return $this->exportToText($session);
            
            case 'markdown':
                return $this->exportToMarkdown($session);
            
            default:
                return null;
        }
    }

    public function import(string $data, string $format = 'json'): ?string {
        switch (strtolower($format)) {
            case 'json':
                $session = json_decode($data, true);
                break;
            
            default:
                return null;
        }

        if (!$session || !isset($session['messages'])) {
            return null;
        }

        $sessionId = $session['id'] ?? 'imported_' . uniqid() . '_' . time();
        $this->repository->save($sessionId, $session['messages'], $session['metadata'] ?? []);

        return $sessionId;
    }

    private function exportToText(array $session): string {
        $text = "Conversation: " . ($session['metadata']['title'] ?? 'Untitled') . "\n";
        $text .= "Date: " . ($session['metadata']['created_at'] ?? '') . "\n\n";

        foreach ($session['messages'] ?? [] as $message) {
            $role = ucfirst($message['role']);
            $text .= "[$role]:\n" . $message['content'] . "\n\n";
        }

        return $text;
    }

    private function exportToMarkdown(array $session): string {
        $md = "# " . ($session['metadata']['title'] ?? 'Untitled') . "\n\n";
        $md .= "**Date:** " . ($session['metadata']['created_at'] ?? '') . "\n\n";
        $md .= "---\n\n";

        foreach ($session['messages'] ?? [] as $message) {
            $role = ucfirst($message['role']);
            $md .= "## $role\n\n";
            $md .= $message['content'] . "\n\n";
        }

        return $md;
    }
}
