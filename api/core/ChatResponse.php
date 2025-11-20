<?php

namespace Chatbot\Core;

/**
 * Value Object for Chat Responses
 * Encapsulates response data with validation
 */
class ChatResponse {
    private bool $success;
    private string $content;
    private ?array $rawData;
    private ?string $errorMessage;
    private array $metadata;

    public function __construct(
        bool $success,
        string $content = '',
        ?array $rawData = null,
        ?string $errorMessage = null,
        array $metadata = []
    ) {
        $this->success = $success;
        $this->content = $content;
        $this->rawData = $rawData;
        $this->errorMessage = $errorMessage;
        $this->metadata = $metadata;
    }

    public function isSuccess(): bool {
        return $this->success;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getRawData(): ?array {
        return $this->rawData;
    }

    public function getErrorMessage(): ?string {
        return $this->errorMessage;
    }

    public function getMetadata(): array {
        return $this->metadata;
    }

    public function toArray(): array {
        return [
            'ok' => $this->success,
            'reply' => $this->content,
            'raw' => $this->rawData,
            'error' => $this->errorMessage,
            'metadata' => $this->metadata
        ];
    }

    public static function success(string $content, ?array $rawData = null, array $metadata = []): self {
        return new self(true, $content, $rawData, null, $metadata);
    }

    public static function failure(string $errorMessage, ?array $rawData = null): self {
        return new self(false, '', $rawData, $errorMessage);
    }
}
