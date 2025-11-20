<?php

namespace Chatbot\Core;

/**
 * Abstract Base Provider - Template Method Pattern
 * Provides common functionality for all providers
 */
abstract class AbstractProvider implements ProviderInterface {
    protected string $apiKey;
    protected ?HttpClientInterface $httpClient;
    protected ?LoggerInterface $logger;
    protected array $config;

    public function __construct(
        string $apiKey,
        ?HttpClientInterface $httpClient = null,
        ?LoggerInterface $logger = null,
        array $config = []
    ) {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? new CurlHttpClient();
        $this->logger = $logger;
        $this->config = $config;
    }

    public function isAvailable(): bool {
        return !empty($this->apiKey) && $this->apiKey !== 'put_' . $this->getName() . '_api_key_here';
    }

    public function chat(array $messages, array $options = []): ChatResponse {
        if (!$this->isAvailable()) {
            return ChatResponse::failure('Provider API key not configured');
        }

        try {
            $this->validateMessages($messages);
            $this->validateOptions($options);
            
            $payload = $this->buildPayload($messages, $options);
            $response = $this->httpClient->post($this->getApiEndpoint(), $payload, $this->getHeaders());
            
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            $this->logError($e);
            return ChatResponse::failure($e->getMessage());
        }
    }

    protected function validateMessages(array $messages): void {
        if (empty($messages)) {
            throw new \InvalidArgumentException('Messages cannot be empty');
        }
    }

    protected function validateOptions(array $options): void {
        // Override in subclasses for specific validation
    }

    abstract protected function buildPayload(array $messages, array $options): array;
    abstract protected function getApiEndpoint(): string;
    abstract protected function getHeaders(): array;
    abstract protected function parseResponse(array $response): ChatResponse;

    protected function logError(\Exception $e): void {
        if ($this->logger) {
            $this->logger->error($e->getMessage(), [
                'provider' => $this->getName(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
