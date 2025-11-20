<?php

namespace Chatbot\Core;

/**
 * Caching Middleware
 */
class CacheMiddleware implements MiddlewareInterface {
    private array $cache = [];
    private int $ttl;

    public function __construct(int $ttl = 300) {
        $this->ttl = $ttl;
    }

    public function handle(Request $request, callable $next): Response {
        // Only cache GET requests
        if ($request->getMethod() !== 'GET') {
            return $next($request);
        }

        $key = $this->getCacheKey($request);
        
        if (isset($this->cache[$key]) && (time() - $this->cache[$key]['timestamp']) < $this->ttl) {
            return new Response(200, $this->cache[$key]['data']);
        }

        $response = $next($request);
        
        if ($response->getStatusCode() === 200) {
            $this->cache[$key] = [
                'data' => $response->getData(),
                'timestamp' => time()
            ];
        }

        return $response;
    }

    private function getCacheKey(Request $request): string {
        return md5($request->getMethod() . serialize($request->getQueryParams()));
    }
}
