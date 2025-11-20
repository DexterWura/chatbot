<?php

/**
 * Legacy BaseProvider Interface - Backward Compatibility
 * New code should use Chatbot\Core\ProviderInterface
 */
interface BaseProvider {
    public function name(): string;
    public function models(): array;
    /**
     * @param array $messages Array of [role => 'system'|'user'|'assistant', content => string]
     * @param array $options  Provider specific options like model, temperature, etc.
     * @return array          [ok => bool, reply => string, raw => mixed]
     */
    public function chat(array $messages, array $options = []): array;
}

// Adapter for new providers to work with legacy interface
if (!class_exists('Chatbot\\Core\\ProviderAdapter')) {
    require_once __DIR__ . '/autoload.php';
    
    class ProviderAdapter implements BaseProvider {
        private \Chatbot\Core\ProviderInterface $provider;
        
        public function __construct(\Chatbot\Core\ProviderInterface $provider) {
            $this->provider = $provider;
        }
        
        public function name(): string {
            return $this->provider->getName();
        }
        
        public function models(): array {
            return $this->provider->getModels();
        }
        
        public function chat(array $messages, array $options = []): array {
            $result = $this->provider->chat($messages, $options);
            return $result->toArray();
        }
    }
}