<?php

namespace Chatbot\Core;

/**
 * File-based Logger Implementation
 */
class FileLogger implements LoggerInterface {
    private string $logFile;
    private string $logLevel;

    public function __construct(string $logFile, string $logLevel = 'INFO') {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }

    public function debug(string $message, array $context = []): void {
        if ($this->logLevel === 'DEBUG') {
            $this->log('DEBUG', $message, $context);
        }
    }

    private function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}
