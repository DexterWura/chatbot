<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set your OpenAI API key
$apiKey = 'put_your_api_key_here';

$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (empty($message)) {
    echo json_encode(['reply' => 'Message cannot be empty.']);
    exit;
}

// Prepare the request payload for the OpenAI chat API
$payload = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => $message]
    ],
    'temperature' => 0.7,  // Adjust creativity level
];

// Initialize cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Execute the request and get the response
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['reply' => 'cURL error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$responseData = json_decode($response, true);

// Check if there's an error in the API response
if (isset($responseData['error'])) {
    $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
    echo json_encode(['reply' => "Error: $errorMessage"]);
} elseif (isset($responseData['choices'][0]['message']['content'])) {
    $reply = $responseData['choices'][0]['message']['content'];
    echo json_encode(['reply' => $reply]);
} else {
    // Log the raw response for debugging
    file_put_contents('debug_log.txt', print_r($responseData, true));
    echo json_encode(['reply' => 'Sorry, I could not process your request.']);
}
?>
