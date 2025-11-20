<?php

namespace Chatbot\Core;

/**
 * Middleware Interface - Chain of Responsibility Pattern
 */
interface MiddlewareInterface {
    public function handle(Request $request, callable $next): Response;
}
