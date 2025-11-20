# Architecture Documentation

## Overview

This chatbot application has been enhanced with a sophisticated Object-Oriented Programming (OOP) architecture using multiple design patterns and advanced features.

## Design Patterns Implemented

### 1. Strategy Pattern
- **Location**: `api/core/ProviderInterface.php`, `api/core/AbstractProvider.php`
- **Purpose**: Allows runtime selection of AI providers (OpenAI, Claude, Gemini, DeepSeek)
- **Implementation**: Each provider implements `ProviderInterface` and extends `AbstractProvider`

### 2. Factory Pattern
- **Location**: `api/core/ProviderFactory.php`
- **Purpose**: Centralized creation of provider instances
- **Benefits**: Decouples provider creation from usage, enables easy addition of new providers

### 3. Repository Pattern
- **Location**: `api/core/ConversationRepository.php`
- **Purpose**: Abstracts data persistence layer for conversations
- **Features**: Save, load, delete, and list conversations with metadata

### 4. Observer Pattern
- **Location**: `api/core/EventDispatcher.php`
- **Purpose**: Event-driven architecture for logging and analytics
- **Events**: `chat.request`, `chat.response`

### 5. Middleware Pattern (Chain of Responsibility)
- **Location**: `api/core/MiddlewarePipeline.php`, `api/core/MiddlewareInterface.php`
- **Purpose**: Request processing pipeline
- **Middleware**: Rate limiting, caching, authentication (extensible)

### 6. Singleton Pattern
- **Location**: `api/core/ConfigManager.php`
- **Purpose**: Single source of configuration
- **Benefits**: Consistent configuration access across the application

### 7. Template Method Pattern
- **Location**: `api/core/AbstractProvider.php`
- **Purpose**: Defines skeleton of algorithm in base class
- **Implementation**: Common chat flow with provider-specific implementations

### 8. Adapter Pattern
- **Location**: `api/core/HttpClientInterface.php`, `api/core/CurlHttpClient.php`
- **Purpose**: Abstracts HTTP client implementation
- **Benefits**: Easy to swap HTTP clients (cURL, Guzzle, etc.)

## Core Components

### Value Objects
- **ChatResponse**: Encapsulates chat response data with validation
- **ProviderCapabilities**: Describes provider features (streaming, vision, etc.)
- **Request/Response**: HTTP request/response value objects

### Services

#### AnalyticsService
- Tracks usage statistics per provider and model
- Token usage tracking
- Request counting
- Time-based analytics (daily, weekly, monthly)

#### SessionManager
- Manages multiple conversation threads
- Session creation, loading, deletion
- Session switching

#### ExportService
- Export conversations in multiple formats (JSON, TXT, Markdown)
- Import conversations from JSON

### Middleware

#### RateLimitMiddleware
- Prevents API abuse
- Configurable request limits per time window
- Client identification via IP address

#### CacheMiddleware
- Caches GET requests
- Configurable TTL
- Reduces API calls

## Provider Architecture

Each provider extends `AbstractProvider` and implements:

1. **getName()**: Returns provider identifier
2. **getModels()**: Returns available models
3. **getCapabilities()**: Returns feature set
4. **buildPayload()**: Constructs API-specific request
5. **getApiEndpoint()**: Returns API URL
6. **getHeaders()**: Returns authentication headers
7. **parseResponse()**: Parses API response to ChatResponse

## Request Flow

```
Client Request
    ↓
Middleware Pipeline (Rate Limit, Cache)
    ↓
Router Handler
    ↓
Provider Factory → Provider Instance
    ↓
AbstractProvider.chat() (Template Method)
    ↓
Provider-specific Implementation
    ↓
HTTP Client → API
    ↓
Response Parsing
    ↓
Analytics Tracking
    ↓
Session Persistence
    ↓
Event Dispatch
    ↓
Response to Client
```

## Configuration

Configuration is managed through:
1. Environment variables (preferred)
2. `config.php` file
3. Default values in `ConfigManager`

### Configuration Keys
- `api_keys.*`: API keys for each provider
- `rate_limit.*`: Rate limiting settings
- `cache.*`: Caching configuration
- `storage.*`: Storage paths
- `logging.*`: Logging configuration

## Storage Structure

```
storage/
├── conversations/     # JSON files for each conversation
├── logs/              # Application logs
└── analytics.json     # Usage analytics
```

## API Endpoints

### GET `/api/router.php`
- Lists all available providers and models
- Returns provider capabilities

### POST `/api/router.php`
- Main chat endpoint
- Accepts: `provider`, `model`, `messages`, `temperature`, `session_id`

### GET `/api/router.php?path=sessions`
- Lists all conversation sessions

### GET `/api/router.php?path=analytics&days=7`
- Returns analytics for specified days

### POST `/api/router.php` (with `path: 'session'`)
- `action: 'create'`: Create new session
- `action: 'load'`: Load existing session
- `action: 'delete'`: Delete session
- `action: 'list'`: List all sessions

## Frontend Architecture

### ChatApp Class (OOP JavaScript)
- Encapsulates all chat functionality
- Manages UI state
- Handles API communication
- Session management

### Features
- Multiple conversation threads
- Provider/model selection
- Settings panel
- Analytics dashboard
- Export/import functionality

## Error Handling

- Comprehensive exception handling
- Error logging via `FileLogger`
- User-friendly error messages
- Debug logging for development

## Extensibility

### Adding a New Provider

1. Create class extending `AbstractProvider`
2. Implement required methods
3. Register in `ProviderFactory`
4. Add API key to configuration

### Adding Middleware

1. Implement `MiddlewareInterface`
2. Add to `MiddlewarePipeline` in router
3. Configure in `ConfigManager`

### Adding Events

1. Dispatch event via `EventDispatcher`
2. Subscribe listeners in router
3. Handle events in services

## Performance Optimizations

- Request caching
- Connection pooling (via HTTP client)
- Lazy loading of providers
- Efficient session management
- Analytics aggregation

## Security Features

- Rate limiting
- API key validation
- Input sanitization
- Error message sanitization
- Secure session management

## Testing Considerations

- Dependency injection enables easy mocking
- Interface-based design allows test doubles
- Value objects are easily testable
- Services can be tested in isolation

## Future Enhancements

- Streaming responses
- Function calling support
- Vision/image input
- Plugin system
- Multi-user support
- Database persistence
- WebSocket support
- Real-time collaboration
