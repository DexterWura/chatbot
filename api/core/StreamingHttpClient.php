<?php

namespace Chatbot\Core;

/**
 * Streaming HTTP Client for Server-Sent Events (SSE)
 */
class StreamingHttpClient {
    private int $timeout;
    private bool $verifySSL;

    public function __construct(int $timeout = 300, bool $verifySSL = true) {
        $this->timeout = $timeout;
        $this->verifySSL = $verifySSL;
    }

    /**
     * Stream POST request with callback for each chunk
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param callable $callback Function(string $chunk, bool $isComplete)
     * @return void
     */
    public function streamPost(string $url, array $data, array $headers, callable $callback): void {
        $ch = curl_init($url);
        
        $headerStrings = [];
        foreach ($headers as $key => $value) {
            $headerStrings[] = "$key: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge(
                ['Content-Type: application/json'],
                $headerStrings
            ),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
            CURLOPT_WRITEFUNCTION => function($ch, $data) use ($callback) {
                $callback($data, false);
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $callback(json_encode(['error' => ['message' => "cURL error: $error"]]), true);
        } else {
            $callback('', true);
        }
    }
}
