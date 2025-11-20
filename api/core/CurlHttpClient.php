<?php

namespace Chatbot\Core;

/**
 * cURL HTTP Client Implementation
 */
class CurlHttpClient implements HttpClientInterface {
    private int $timeout;
    private bool $verifySSL;

    public function __construct(int $timeout = 30, bool $verifySSL = true) {
        $this->timeout = $timeout;
        $this->verifySSL = $verifySSL;
    }

    public function post(string $url, array $data, array $headers = []): array {
        $ch = curl_init($url);
        
        $headerStrings = [];
        foreach ($headers as $key => $value) {
            $headerStrings[] = "$key: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge(
                ['Content-Type: application/json'],
                $headerStrings
            ),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: $error");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        return [
            'status_code' => $httpCode,
            'body' => $decoded,
            'raw' => $response
        ];
    }

    public function get(string $url, array $headers = []): array {
        $ch = curl_init($url);
        
        $headerStrings = [];
        foreach ($headers as $key => $value) {
            $headerStrings[] = "$key: $value";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array_merge(
                ['Content-Type: application/json'],
                $headerStrings
            ),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        return [
            'status_code' => $httpCode,
            'body' => $decoded ?? [],
            'raw' => $response
        ];
    }
}
