<?php
// Backward-compatible shim to the new router
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$message = $data['message'] ?? '';
if (!$message) {
    echo json_encode(['reply' => 'Message cannot be empty.']);
    exit;
}

$payload = [
    'provider' => 'openai',
    'model' => null,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => $message]
    ]
];

$ch = curl_init('api/router.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['reply' => 'cURL error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);
$r = json_decode($response, true);
echo json_encode(['reply' => $r['reply'] ?? '']);
?>
