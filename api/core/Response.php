<?php

namespace Chatbot\Core;

/**
 * Response Value Object
 */
class Response {
    private int $statusCode;
    private array $data;
    private array $headers;

    public function __construct(int $statusCode = 200, array $data = [], array $headers = []) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->headers = $headers;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getData(): array {
        return $this->data;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function send(): void {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        if (!isset($this->headers['Content-Type'])) {
            header('Content-Type: application/json');
        }

        echo json_encode($this->data);
    }

    public static function success(array $data = []): self {
        return new self(200, array_merge(['ok' => true], $data));
    }

    public static function error(string $message, int $statusCode = 400): self {
        return new self($statusCode, ['ok' => false, 'error' => $message]);
    }
}
