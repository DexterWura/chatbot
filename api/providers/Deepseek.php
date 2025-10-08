<?php
require_once __DIR__ . '/../BaseProvider.php';

class Deepseek implements BaseProvider {
    private string $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function name(): string { return 'deepseek'; }

    public function models(): array {
        return ['deepseek-chat', 'deepseek-reasoner'];
    }

    public function chat(array $messages, array $options = []): array {
        $model = $options['model'] ?? 'deepseek-chat';
        $temperature = $options['temperature'] ?? 0.7;

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
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
        $content = $data['choices'][0]['message']['content'] ?? '';
        return ['ok' => true, 'reply' => $content, 'raw' => $data];
    }
}
?>


