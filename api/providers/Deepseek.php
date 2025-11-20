<?php

namespace Chatbot\Providers;

use Chatbot\Core\AbstractProvider;
use Chatbot\Core\ChatResponse;
use Chatbot\Core\ProviderCapabilities;

/**
 * DeepSeek Provider Implementation
 */
class Deepseek extends AbstractProvider {
    public function getName(): string {
        return 'deepseek';
    }

    public function getModels(): array {
        return [
            'deepseek-chat',
            'deepseek-reasoner',
            'deepseek-coder'
        ];
    }

    public function getCapabilities(): ProviderCapabilities {
        return new ProviderCapabilities(
            supportsStreaming: true,
            supportsFunctionCalling: false,
            supportsVision: false,
            maxTokens: 16384,
            supportedFeatures: ['streaming', 'reasoning']
        );
    }

    protected function buildPayload(array $messages, array $options): array {
        $model = $options['model'] ?? 'deepseek-chat';
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2048;

        return [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];
    }

    protected function getApiEndpoint(): string {
        return 'https://api.deepseek.com/chat/completions';
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
}