<?php

namespace Chatbot\Core;

/**
 * HTTP Client Interface - Adapter Pattern
 */
interface HttpClientInterface {
    public function post(string $url, array $data, array $headers = []): array;
    public function get(string $url, array $headers = []): array;
}
