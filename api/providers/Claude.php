<?php
require_once __DIR__ . '/../BaseProvider.php';

class Claude implements BaseProvider {
    private string $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function name(): string { return 'claude'; }

    public function models(): array {
        return ['claude-3-opus-20240229', 'claude-3-sonnet-20240229', 'claude-3-haiku-20240307'];
    }

    public function chat(array $messages, array $options = []): array {
        $model = $options['model'] ?? 'claude-3-haiku-20240307';
        $temperature = $options['temperature'] ?? 0.7;

        $payload = [
            'model' => $model,
            'max_tokens' => 1024,
            'temperature' => $temperature,
            'messages' => $messages,
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'reply' => 'cURL error: ' . $err, 'raw' => null];
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['error'])) {
            return ['ok' => false, 'reply' => ($data['error']['message'] ?? 'Unknown error'), 'raw' => $data];
        }
        $content = '';
        if (isset($data['content'][0]['text'])) {
            $content = $data['content'][0]['text'];
        }
        return ['ok' => true, 'reply' => $content, 'raw' => $data];
    }
}
?>


