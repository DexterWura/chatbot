<?php

namespace Chatbot\Core;

/**
 * Environment File Manager
 */
class EnvManager {
    private string $envPath;

    public function __construct(string $envPath = null) {
        $this->envPath = $envPath ?? __DIR__ . '/../../.env';
    }

    public function load(): array {
        $env = [];
        if (file_exists($this->envPath)) {
            $lines = file($this->envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $env[trim($key)] = trim($value, '"\'');
                }
            }
        }
        return $env;
    }

    public function save(array $data): bool {
        $content = "# Chatbot Configuration\n";
        $content .= "# Generated automatically - do not edit manually\n\n";

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }
            $content .= "$key=\"$value\"\n";
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    public function exists(): bool {
        return file_exists($this->envPath);
    }

    public function get(string $key, $default = null) {
        $env = $this->load();
        return $env[$key] ?? $default;
    }
}
