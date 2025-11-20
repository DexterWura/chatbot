<?php

namespace Chatbot\Core;

/**
 * Streaming Response Handler
 */
class StreamingResponse {
    private bool $started = false;

    public function start(): void {
        if ($this->started) {
            return;
        }

        $this->started = true;
        
        // Set headers for Server-Sent Events
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start output buffering with no buffering
        ob_start(null, 0);
    }

    public function send(string $data, string $event = 'message'): void {
        if (!$this->started) {
            $this->start();
        }

        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                echo "event: $event\n";
                echo "data: $line\n\n";
            }
        }
        
        flush();
        if (ob_get_level()) {
            ob_flush();
        }
    }

    public function sendJSON(array $data, string $event = 'message'): void {
        $this->send(json_encode($data), $event);
    }

    public function end(): void {
        $this->sendJSON(['done' => true], 'done');
        flush();
        if (ob_get_level()) {
            ob_end_flush();
        }
        exit;
    }
}
