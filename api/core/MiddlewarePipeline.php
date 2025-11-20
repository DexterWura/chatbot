<?php

namespace Chatbot\Core;

/**
 * Middleware Pipeline - Chain of Responsibility Pattern
 */
class MiddlewarePipeline {
    private array $middlewares = [];

    public function add(MiddlewareInterface $middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function handle(Request $request, callable $handler): Response {
        $stack = array_reverse($this->middlewares);
        $next = $handler;

        foreach ($stack as $middleware) {
            $next = function ($req) use ($middleware, $next) {
                return $middleware->handle($req, $next);
            };
        }

        return $next($request);
    }
}
