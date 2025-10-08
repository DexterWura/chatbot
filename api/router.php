<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/BaseProvider.php';
require_once __DIR__ . '/providers/OpenAI.php';
require_once __DIR__ . '/providers/Deepseek.php';
require_once __DIR__ . '/providers/Gemini.php';
require_once __DIR__ . '/providers/Claude.php';

// Load API keys from env or fallback placeholders
$OPENAI_API_KEY = getenv('OPENAI_API_KEY') ?: 'put_openai_api_key_here';
$DEEPSEEK_API_KEY = getenv('DEEPSEEK_API_KEY') ?: 'put_deepseek_api_key_here';
$GEMINI_API_KEY = getenv('GEMINI_API_KEY') ?: 'put_gemini_api_key_here';
$ANTHROPIC_API_KEY = getenv('ANTHROPIC_API_KEY') ?: 'put_anthropic_api_key_here';

$providers = [
    'openai' => new OpenAI($OPENAI_API_KEY),
    'deepseek' => new Deepseek($DEEPSEEK_API_KEY),
    'gemini' => new Gemini($GEMINI_API_KEY),
    'claude' => new Claude($ANTHROPIC_API_KEY),
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    // List providers and models
    $out = [];
    foreach ($providers as $key => $prov) {
        $out[] = [
            'provider' => $key,
            'models' => $prov->models(),
        ];
    }
    echo json_encode(['providers' => $out]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$messages = $data['messages'] ?? [];
$providerKey = $data['provider'] ?? 'openai';
$model = $data['model'] ?? null;
$temperature = $data['temperature'] ?? 0.7;

if (!$messages) {
    echo json_encode(['ok' => false, 'reply' => 'Message cannot be empty.']);
    exit;
}

if (!isset($providers[$providerKey])) {
    echo json_encode(['ok' => false, 'reply' => 'Unknown provider: ' . $providerKey]);
    exit;
}

$provider = $providers[$providerKey];
$result = $provider->chat($messages, [
    'model' => $model,
    'temperature' => $temperature,
]);

if (!$result['ok']) {
    file_put_contents(__DIR__ . '/../debug_log.txt', date('c') . "\n" . print_r($result['raw'], true) . "\n\n", FILE_APPEND);
}

echo json_encode($result);
?>


