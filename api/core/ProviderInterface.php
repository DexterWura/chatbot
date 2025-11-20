<?php

namespace Chatbot\Core;

/**
 * Provider Interface - Strategy Pattern
 * Defines the contract for all AI providers
 */
interface ProviderInterface {
    public function getName(): string;
    public function getModels(): array;
    public function chat(array $messages, array $options = []): ChatResponse;
    public function streamChat(array $messages, array $options = [], callable $callback = null): void;
    public function isAvailable(): bool;
    public function getCapabilities(): ProviderCapabilities;
}
