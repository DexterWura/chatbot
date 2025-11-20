<?php

namespace Chatbot\Core;

/**
 * Rate Limiting Middleware
 */
class RateLimitMiddleware implements MiddlewareInterface {
    private array $requests = [];
    private int $maxRequests;
    private int $timeWindow;

    public function __construct(int $maxRequests = 60, int $timeWindow = 60) {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    public function handle(Request $request, callable $next): Response {
        $clientId = $this->getClientId($request);
        $now = time();

        // Clean old entries
        $this->requests[$clientId] = array_filter(
            $this->requests[$clientId] ?? [],
            fn($timestamp) => ($now - $timestamp) < $this->timeWindow
        );

        // Check rate limit
        $count = count($this->requests[$clientId] ?? []);
        if ($count >= $this->maxRequests) {
            return Response::error('Rate limit exceeded', 429);
        }

        // Record request
        $this->requests[$clientId][] = $now;

        return $next($request);
    }

    private function getClientId(Request $request): string {
        $headers = $request->getHeaders();
        return $headers['X-Forwarded-For'] ?? 
               $headers['X-Real-IP'] ?? 
               $_SERVER['REMOTE_ADDR'] ?? 
               'unknown';
    }
}
