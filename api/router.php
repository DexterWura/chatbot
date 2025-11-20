<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Load autoloader
require_once __DIR__ . '/autoload.php';

use Chatbot\Core\{
    ConfigManager,
    ProviderFactory,
    Request,
    Response,
    MiddlewarePipeline,
    RateLimitMiddleware,
    CacheMiddleware,
    ConversationRepository,
    ConversationRepositoryInterface,
    DatabaseRepository,
    PDODatabase,
    SessionManager,
    AnalyticsService,
    FileLogger,
    EventDispatcher
};

// Initialize configuration
$config = ConfigManager::getInstance();

// Initialize logger
$logger = null;
if ($config->get('logging.enabled')) {
    $logPath = $config->get('storage.logs_path');
    if (!is_dir($logPath)) {
        mkdir($logPath, 0755, true);
    }
    $logger = new FileLogger(
        $logPath . '/app.log',
        $config->get('logging.level', 'INFO')
    );
}

// Initialize conversation repository (database or file-based)
$conversationRepo = null;
$dbConfig = $config->get('database', []);

// Try to use database if configured
if (!empty($dbConfig['name']) && !empty($dbConfig['user'])) {
    try {
        $db = new PDODatabase(
            $dbConfig['host'] ?? 'localhost',
            $dbConfig['name'],
            $dbConfig['user'],
            $dbConfig['pass'] ?? '',
            $dbConfig['driver'] ?? 'mysql'
        );
        
        if ($db->connect()) {
            $conversationRepo = new DatabaseRepository($db, 'conversations', $logger);
        }
    } catch (\Exception $e) {
        if ($logger) {
            $logger->warning("Database connection failed, using file storage: " . $e->getMessage());
        }
    }
}

// Fallback to file-based storage
if (!$conversationRepo) {
    $conversationRepo = new ConversationRepository(
        $config->get('storage.conversations_path'),
        $logger
    );
}

$sessionManager = new SessionManager($conversationRepo);

$analyticsService = new AnalyticsService(
    $conversationRepo,
    __DIR__ . '/../storage/analytics.json',
    $logger
);

$eventDispatcher = new EventDispatcher();

// Initialize provider factory
$factory = new ProviderFactory($logger);
$apiKeys = $config->get('api_keys', []);

$providers = [];
foreach (['openai', 'deepseek', 'gemini', 'claude'] as $providerName) {
    $keyName = $providerName === 'claude' ? 'anthropic' : $providerName;
    $apiKey = $apiKeys[$keyName] ?? '';
    
    try {
        $provider = $factory->create($providerName, $apiKey);
        $providers[$providerName] = $provider;
    } catch (\Exception $e) {
        if ($logger) {
            $logger->error("Failed to initialize provider $providerName: " . $e->getMessage());
        }
    }
}

// Setup middleware pipeline
$pipeline = new MiddlewarePipeline();

if ($config->get('rate_limit.enabled')) {
    $pipeline->add(new RateLimitMiddleware(
        $config->get('rate_limit.max_requests', 60),
        $config->get('rate_limit.time_window', 60)
    ));
}

if ($config->get('cache.enabled')) {
    $pipeline->add(new CacheMiddleware(
        $config->get('cache.ttl', 300)
    ));
}

// Request handler
$handler = function (Request $request): Response {
    global $providers, $sessionManager, $analyticsService, $eventDispatcher, $logger;

    $method = $request->getMethod();
    $path = $request->get('path', 'chat');

    // GET: List providers and models
    if ($method === 'GET') {
        if ($path === 'sessions') {
            $sessions = $sessionManager->listSessions();
            return Response::success(['sessions' => $sessions]);
        }

        if ($path === 'analytics') {
            $days = (int)($request->get('days', 7));
            $stats = $analyticsService->getStats($days);
            return Response::success(['analytics' => $stats]);
        }

        $out = [];
        foreach ($providers as $key => $provider) {
            if (!$provider->isAvailable()) continue;
            
            $out[] = [
                'provider' => $key,
                'name' => $provider->getName(),
                'models' => $provider->getModels(),
                'capabilities' => [
                    'streaming' => $provider->getCapabilities()->supportsStreaming(),
                    'function_calling' => $provider->getCapabilities()->supportsFunctionCalling(),
                    'vision' => $provider->getCapabilities()->supportsVision(),
                    'max_tokens' => $provider->getCapabilities()->getMaxTokens(),
                ]
            ];
        }
        return Response::success(['providers' => $out]);
    }

    // POST: Handle chat requests
    if ($method === 'POST') {
        $data = $request->getData();
        $messages = $data['messages'] ?? [];
        $providerKey = $data['provider'] ?? 'openai';
        $model = $data['model'] ?? null;
        $temperature = $data['temperature'] ?? 0.7;
        $sessionId = $data['session_id'] ?? $sessionManager->getCurrentSessionId();
        $options = $data['options'] ?? [];

        // Handle session operations
        if ($path === 'session') {
            $action = $data['action'] ?? 'get';
            
            switch ($action) {
                case 'create':
                    $title = $data['title'] ?? 'New Chat';
                    $newSessionId = $sessionManager->createSession($title);
                    return Response::success(['session_id' => $newSessionId]);
                
                case 'load':
                    $session = $sessionManager->loadSession($sessionId);
                    if ($session) {
                        return Response::success(['session' => $session]);
                    }
                    return Response::error('Session not found', 404);
                
                case 'delete':
                    $sessionManager->deleteSession($sessionId);
                    return Response::success(['deleted' => true]);
                
                case 'list':
                    $sessions = $sessionManager->listSessions();
                    return Response::success(['sessions' => $sessions]);
                
                default:
                    return Response::error('Invalid action', 400);
            }
        }

        if (empty($messages)) {
            return Response::error('Messages cannot be empty');
        }

        if (!isset($providers[$providerKey])) {
            return Response::error("Unknown provider: $providerKey");
        }

        $provider = $providers[$providerKey];
        
        if (!$provider->isAvailable()) {
            return Response::error("Provider $providerKey is not available (API key not configured)");
        }

        // Dispatch event
        $eventDispatcher->dispatch('chat.request', [
            'provider' => $providerKey,
            'model' => $model,
            'session_id' => $sessionId
        ]);

        // Make chat request
        $chatOptions = array_merge([
            'model' => $model,
            'temperature' => $temperature,
        ], $options);

        $result = $provider->chat($messages, $chatOptions);

        // Track analytics
        if ($result->isSuccess()) {
            $metadata = $result->getMetadata();
            $tokensUsed = $metadata['usage']['total_tokens'] ?? 
                         $metadata['usage']['input_tokens'] ?? 0;
            $analyticsService->trackRequest($providerKey, $model ?? 'default', $tokensUsed);
        }

        // Save conversation
        if ($result->isSuccess()) {
            $sessionData = $sessionManager->loadSession($sessionId);
            $sessionMessages = $sessionData['messages'] ?? [];
            
            // Add new messages
            $sessionMessages = array_merge($sessionMessages, $messages);
            $sessionMessages[] = [
                'role' => 'assistant',
                'content' => $result->getContent()
            ];

            $sessionManager->saveSession($sessionId, $sessionMessages, [
                'last_provider' => $providerKey,
                'last_model' => $model,
                'title' => generateTitle($sessionMessages)
            ]);
        }

        // Dispatch event
        $eventDispatcher->dispatch('chat.response', [
            'provider' => $providerKey,
            'success' => $result->isSuccess(),
            'session_id' => $sessionId
        ]);

        // Log errors
        if (!$result->isSuccess() && $logger) {
            $logger->error("Chat error: " . $result->getErrorMessage(), [
                'provider' => $providerKey,
                'raw' => $result->getRawData()
            ]);
        }

        return Response::success($result->toArray());
    }

    return Response::error('Method not allowed', 405);
};

// Helper function for title generation
function generateTitle(array $messages): string {
    $userMessages = array_filter($messages, fn($m) => $m['role'] === 'user');
    $firstUserMessage = reset($userMessages);
    if ($firstUserMessage) {
        $title = substr($firstUserMessage['content'], 0, 50);
        return strlen($firstUserMessage['content']) > 50 ? $title . '...' : $title;
    }
    return 'New Chat';
}

// Process request through pipeline
try {
    $request = Request::fromGlobals();
    $response = $pipeline->handle($request, $handler);
    $response->send();
} catch (\Exception $e) {
    if ($logger) {
        $logger->error("Router error: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    $errorResponse = Response::error('Internal server error', 500);
    $errorResponse->send();
}