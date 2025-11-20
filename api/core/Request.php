<?php

namespace Chatbot\Core;

/**
 * Request Value Object
 */
class Request {
    private string $method;
    private array $data;
    private array $headers;
    private array $queryParams;

    public function __construct(
        string $method = 'GET',
        array $data = [],
        array $headers = [],
        array $queryParams = []
    ) {
        $this->method = $method;
        $this->data = $data;
        $this->headers = $headers;
        $this->queryParams = $queryParams;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getData(): array {
        return $this->data;
    }

    public function get(string $key, $default = null) {
        return $this->data[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public static function fromGlobals(): self {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $data = $method === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?: []) : [];
        $headers = getallheaders() ?: [];
        $queryParams = $_GET;

        return new self($method, $data, $headers, $queryParams);
    }
}
