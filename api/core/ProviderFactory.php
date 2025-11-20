<?php

namespace Chatbot\Core;

/**
 * Provider Factory - Factory Pattern
 * Creates provider instances based on configuration
 */
class ProviderFactory {
    private array $providers = [];
    private ?LoggerInterface $logger;
    private ?HttpClientInterface $httpClient;

    public function __construct(?LoggerInterface $logger = null, ?HttpClientInterface $httpClient = null) {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function create(string $providerName, string $apiKey, array $config = []): ProviderInterface {
        $key = strtolower($providerName);
        
        if (isset($this->providers[$key])) {
            return $this->providers[$key];
        }

        $providerClass = $this->getProviderClass($key);
        
        if (!class_exists($providerClass)) {
            throw new \InvalidArgumentException("Provider class not found: $providerClass");
        }

        $provider = new $providerClass($apiKey, $this->httpClient, $this->logger, $config);
        
        if (!$provider instanceof ProviderInterface) {
            throw new \InvalidArgumentException("Provider must implement ProviderInterface");
        }

        $this->providers[$key] = $provider;
        return $provider;
    }

    public function register(string $name, ProviderInterface $provider): void {
        $this->providers[strtolower($name)] = $provider;
    }

    private function getProviderClass(string $name): string {
        $classes = [
            'openai' => 'Chatbot\Providers\OpenAI',
            'claude' => 'Chatbot\Providers\Claude',
            'gemini' => 'Chatbot\Providers\Gemini',
            'deepseek' => 'Chatbot\Providers\Deepseek',
        ];

        return $classes[$name] ?? "Chatbot\\Providers\\" . ucfirst($name);
    }

    public function getAllProviders(): array {
        return $this->providers;
    }
}
