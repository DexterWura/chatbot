<?php

namespace Chatbot\Core;

/**
 * Value Object for Provider Capabilities
 */
class ProviderCapabilities {
    private bool $supportsStreaming;
    private bool $supportsFunctionCalling;
    private bool $supportsVision;
    private int $maxTokens;
    private array $supportedFeatures;

    public function __construct(
        bool $supportsStreaming = false,
        bool $supportsFunctionCalling = false,
        bool $supportsVision = false,
        int $maxTokens = 4096,
        array $supportedFeatures = []
    ) {
        $this->supportsStreaming = $supportsStreaming;
        $this->supportsFunctionCalling = $supportsFunctionCalling;
        $this->supportsVision = $supportsVision;
        $this->maxTokens = $maxTokens;
        $this->supportedFeatures = $supportedFeatures;
    }

    public function supportsStreaming(): bool {
        return $this->supportsStreaming;
    }

    public function supportsFunctionCalling(): bool {
        return $this->supportsFunctionCalling;
    }

    public function supportsVision(): bool {
        return $this->supportsVision;
    }

    public function getMaxTokens(): int {
        return $this->maxTokens;
    }

    public function getSupportedFeatures(): array {
        return $this->supportedFeatures;
    }

    public function hasFeature(string $feature): bool {
        return in_array($feature, $this->supportedFeatures);
    }
}
