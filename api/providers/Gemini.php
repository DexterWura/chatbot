<?php
require_once __DIR__ . '/../BaseProvider.php';

class Gemini implements BaseProvider {
    private string $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function name(): string { return 'gemini'; }

    public function models(): array {
        return ['gemini-1.5-pro', 'gemini-1.5-flash'];
    }

    public function chat(array $messages, array $options = []): array {
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

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . urlencode($this->apiKey);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return ['ok' => true, 'reply' => $content, 'raw' => $data];
    }
}
?>


