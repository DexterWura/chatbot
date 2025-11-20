<?php

namespace Chatbot\Providers;

use Chatbot\Core\AbstractProvider;
use Chatbot\Core\ChatResponse;
use Chatbot\Core\ProviderCapabilities;

/**
 * OpenAI Provider Implementation
 */
class OpenAI extends AbstractProvider {
    public function getName(): string {
        return 'openai';
    }

    public function getModels(): array {
        return [
            'gpt-4o-mini',
            'gpt-4o',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo'
        ];
    }

    public function getCapabilities(): ProviderCapabilities {
        return new ProviderCapabilities(
            supportsStreaming: true,
            supportsFunctionCalling: true,
            supportsVision: true,
            maxTokens: 16384,
            supportedFeatures: ['streaming', 'functions', 'vision', 'json_mode']
        );
    }

    protected function buildPayload(array $messages, array $options): array {
        $model = $options['model'] ?? 'gpt-4o-mini';
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? $this->config['max_tokens'] ?? 2048;

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        if (isset($options['stream']) && $options['stream']) {
            $payload['stream'] = true;
        }

        if (isset($options['functions'])) {
            $payload['functions'] = $options['functions'];
        }

        return $payload;
    }

    protected function getApiEndpoint(): string {
        return 'https://api.openai.com/v1/chat/completions';
    }

    protected function getHeaders(): array {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey
        ];
    }

    protected function parseResponse(array $response): ChatResponse {
        $httpCode = $response['status_code'];
        $data = $response['body'];

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Unknown error';
            return ChatResponse::failure($errorMsg, $data);
        }

        if (isset($data['error'])) {
            return ChatResponse::failure(
                $data['error']['message'] ?? 'Unknown error',
                $data
            );
        }

        $content = $data['choices'][0]['message']['content'] ?? '';
        $metadata = [
            'model' => $data['model'] ?? '',
            'usage' => $data['usage'] ?? [],
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? '',
        ];

        return ChatResponse::success($content, $data, $metadata);
    }

    public function streamChat(array $messages, array $options = [], callable $callback = null): void {
        if (!$this->isAvailable()) {
            if ($callback) {
                $callback(['error' => 'Provider API key not configured'], true);
            }
            return;
        }

        try {
            $this->validateMessages($messages);
            $this->validateOptions($options);
            
            $options['stream'] = true;
            $payload = $this->buildPayload($messages, $options);
            
            $streamingClient = new \Chatbot\Core\StreamingHttpClient();
            $buffer = '';
            $fullContent = '';
            
            $streamingClient->streamPost(
                $this->getApiEndpoint(),
                $payload,
                $this->getHeaders(),
                function($chunk, $isComplete) use ($callback, &$buffer, &$fullContent) {
                    if ($isComplete && empty($chunk)) {
                        if ($callback) {
                            $callback(['done' => true, 'content' => $fullContent], true);
                        }
                        return;
                    }

                    $buffer .= $chunk;
                    $lines = explode("\n", $buffer);
                    $buffer = array_pop($lines); // Keep incomplete line in buffer

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line) || !str_starts_with($line, 'data: ')) {
                            continue;
                        }

                        $data = substr($line, 6); // Remove 'data: ' prefix
                        
                        if ($data === '[DONE]') {
                            if ($callback) {
                                $callback(['done' => true, 'content' => $fullContent], true);
                            }
                            return;
                        }

                        $json = json_decode($data, true);
                        if ($json && isset($json['choices'][0]['delta']['content'])) {
                            $token = $json['choices'][0]['delta']['content'];
                            $fullContent .= $token;
                            
                            if ($callback) {
                                $callback([
                                    'token' => $token,
                                    'content' => $fullContent,
                                    'done' => false
                                ], false);
                            }
                        }
                    }
                }
            );
        } catch (\Exception $e) {
            $this->logError($e);
            if ($callback) {
                $callback(['error' => $e->getMessage()], true);
            }
        }
    }
}