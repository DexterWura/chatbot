<?php

namespace Chatbot\Providers;

use Chatbot\Core\AbstractProvider;
use Chatbot\Core\ChatResponse;
use Chatbot\Core\ProviderCapabilities;

/**
 * Google Gemini Provider Implementation
 */
class Gemini extends AbstractProvider {
    public function getName(): string {
        return 'gemini';
    }

    public function getModels(): array {
        return [
            'gemini-1.5-pro',
            'gemini-1.5-flash',
            'gemini-1.5-pro-latest',
            'gemini-pro'
        ];
    }

    public function getCapabilities(): ProviderCapabilities {
        return new ProviderCapabilities(
            supportsStreaming: true,
            supportsFunctionCalling: true,
            supportsVision: true,
            maxTokens: 8192,
            supportedFeatures: ['streaming', 'functions', 'vision', 'multimodal']
        );
    }

    protected function buildPayload(array $messages, array $options): array {
        $model = $options['model'] ?? 'gemini-1.5-flash';
        
        // Convert OpenAI-like messages to Gemini format
        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $m['content']]],
            ];
        }

        $payload = [
            'contents' => $contents,
        ];

        if (isset($options['temperature'])) {
            $payload['generationConfig'] = [
                'temperature' => $options['temperature']
            ];
        }

        return $payload;
    }

    protected function getApiEndpoint(): string {
        $model = $this->config['model'] ?? 'gemini-1.5-flash';
        return 'https://generativelanguage.googleapis.com/v1beta/models/' . 
               urlencode($model) . ':generateContent?key=' . urlencode($this->apiKey);
    }

    protected function getHeaders(): array {
        return [
            'Content-Type' => 'application/json'
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

        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $metadata = [
            'model' => $data['modelVersion'] ?? '',
            'usage' => $data['usageMetadata'] ?? [],
            'finish_reason' => $data['candidates'][0]['finishReason'] ?? '',
        ];

        return ChatResponse::success($content, $data, $metadata);
    }
}