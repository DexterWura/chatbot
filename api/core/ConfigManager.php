<?php

namespace Chatbot\Core;

/**
 * Configuration Manager - Singleton Pattern
 */
class ConfigManager {
    private static ?self $instance = null;
    private array $config = [];

    private function __construct() {
        $this->loadConfig();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void {
        // Load from .env file first
        $envManager = new EnvManager();
        $env = $envManager->load();

        // Load from environment variables or .env
        $getEnv = function($key, $default = '') use ($env) {
            return $env[$key] ?? getenv($key) ?: $default;
        };

        $this->config = [
            'database' => [
                'driver' => $getEnv('DB_DRIVER', 'mysql'),
                'host' => $getEnv('DB_HOST', 'localhost'),
                'name' => $getEnv('DB_NAME', ''),
                'user' => $getEnv('DB_USER', ''),
                'pass' => $getEnv('DB_PASS', ''),
                'port' => $getEnv('DB_PORT', '3306'),
            ],
            'api_keys' => [
                'openai' => $getEnv('OPENAI_API_KEY', 'put_openai_api_key_here'),
                'deepseek' => $getEnv('DEEPSEEK_API_KEY', 'put_deepseek_api_key_here'),
                'gemini' => $getEnv('GEMINI_API_KEY', 'put_gemini_api_key_here'),
                'anthropic' => $getEnv('ANTHROPIC_API_KEY', 'put_anthropic_api_key_here'),
            ],
            'default_provider' => 'openai',
            'default_model' => null,
            'default_temperature' => 0.7,
            'max_tokens' => 2048,
            'rate_limit' => [
                'enabled' => $getEnv('RATE_LIMIT_ENABLED', 'true') === 'true',
                'max_requests' => (int)$getEnv('RATE_LIMIT_MAX', '60'),
                'time_window' => (int)$getEnv('RATE_LIMIT_WINDOW', '60'),
            ],
            'cache' => [
                'enabled' => $getEnv('CACHE_ENABLED', 'true') === 'true',
                'ttl' => (int)$getEnv('CACHE_TTL', '300'),
            ],
            'storage' => [
                'conversations_path' => __DIR__ . '/../../storage/conversations',
                'logs_path' => __DIR__ . '/../../storage/logs',
            ],
            'logging' => [
                'enabled' => true,
                'level' => $getEnv('LOG_LEVEL', 'INFO'),
            ],
        ];

        // Try to load from config file (legacy support)
        $configFile = __DIR__ . '/../../config.php';
        if (file_exists($configFile)) {
            $fileConfig = include $configFile;
            $this->config = array_merge_recursive($this->config, $fileConfig);
        }
    }

    public function get(string $key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function set(string $key, $value): void {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    public function getAll(): array {
        return $this->config;
    }
}
