<?php

namespace Chatbot\Providers;

use Chatbot\Core\AbstractProvider;
use Chatbot\Core\ChatResponse;
use Chatbot\Core\ProviderCapabilities;

/**
 * Claude (Anthropic) Provider Implementation
 */
class Claude extends AbstractProvider {
    public function getName(): string {
        return 'claude';
    }

    public function getModels(): array {
        return [
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
            'claude-3-5-sonnet-20241022'
        ];
    }

    public function getCapabilities(): ProviderCapabilities {
        return new ProviderCapabilities(
            supportsStreaming: true,
            supportsFunctionCalling: true,
            supportsVision: true,
            maxTokens: 200000,
            supportedFeatures: ['streaming', 'functions', 'vision', 'long_context']
        );
    }

    protected function buildPayload(array $messages, array $options): array {
        $model = $options['model'] ?? 'claude-3-haiku-20240307';
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 1024;

        return [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => $messages,
        ];
    }

    protected function getApiEndpoint(): string {
        return 'https://api.anthropic.com/v1/messages';
    }

    protected function getHeaders(): array {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json'
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

        $content = '';
        if (isset($data['content'][0]['text'])) {
            $content = $data['content'][0]['text'];
        }

        $metadata = [
            'model' => $data['model'] ?? '',
            'usage' => $data['usage'] ?? [],
            'stop_reason' => $data['stop_reason'] ?? '',
        ];

        return ChatResponse::success($content, $data, $metadata);
    }
}