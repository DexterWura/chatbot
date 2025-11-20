AI Chatbot – Multi‑Provider (OpenAI, DeepSeek, Gemini, Claude)
This app is now a clean OOP PHP project with a provider-based architecture. The UI has been redesigned to resemble ChatGPT with a sidebar, center canvas, and a bottom composer. Users can select the provider and model at runtime.

🚀 Features
- Real-time chat interface
- Multi-provider support (OpenAI, DeepSeek, Gemini, Claude)
- Runtime provider/model selector
- **Auto-installation wizard** with database setup
- **Database support** (MySQL, PostgreSQL, SQLite)
- **.env configuration** management
- **Session management** with multiple conversation threads
- **Analytics tracking** per provider/model
- **Rate limiting** and caching
- **Export/Import** conversations
- OOP architecture with design patterns
- Modern ChatGPT-like UI
🛠️ Technologies Used
HTML: For the front-end UI
CSS: For styling the chat interface
JavaScript: For handling interactions and AJAX calls
PHP: For backend API communication with OpenAI
OpenAI API: For fetching AI-generated responses
🖥️ Installation and Setup

## Quick Start (Auto-Installation)

1. **Upload Files**: Upload all files to your web server
2. **Set Permissions**: Make `storage/` directory writable:
   ```bash
   chmod -R 755 storage/
   ```
3. **Access Installer**: Navigate to your domain - you'll be redirected to the installer
4. **Complete Wizard**: Follow the installation wizard:
   - System requirements check
   - Database configuration (MySQL/PostgreSQL/SQLite)
   - API keys setup
   - Automatic database migration
5. **Done!**: Start using your chatbot

## Manual Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed manual installation instructions.

## Requirements

- PHP 7.4 or higher
- PDO extension
- PDO MySQL/PostgreSQL extension (for database)
- cURL extension
- JSON extension
- Write permissions on `storage/` directory

## Local Development

```bash
# Start PHP development server
php -S localhost:8000

# Access installer
open http://localhost:8000/install.php
```
🔧 File Structure

```
/chatbot
├── api/
│   ├── autoload.php
│   ├── router.php
│   ├── core/              # Core OOP classes
│   │   ├── AbstractProvider.php
│   │   ├── ConfigManager.php
│   │   ├── DatabaseInterface.php
│   │   ├── DatabaseMigration.php
│   │   ├── DatabaseRepository.php
│   │   ├── EnvManager.php
│   │   └── ...
│   └── providers/         # AI provider implementations
│       ├── OpenAI.php
│       ├── Deepseek.php
│       ├── Gemini.php
│       └── Claude.php
├── assets/
│   ├── css/style.css
│   └── js/
│       ├── app.js
│       └── ChatApp.js
├── storage/               # Auto-created during installation
│   ├── conversations/
│   └── logs/
├── install.php            # Auto-installation wizard
├── index.html
├── index.php              # Entry point
├── .env                   # Configuration (auto-generated)
├── .installed             # Installation flag
└── README.md
```
⚙️ Configuration
Model Selection
You can modify the model used in the chat.php file. By default, it uses gpt-3.5-turbo.

php
Copy code
'model' => 'gpt-3.5-turbo',
Adjusting Temperature
Control how creative the responses are by adjusting the temperature parameter in chat.php. Higher values (like 0.9) make output more random, while lower values (like 0.2) make it more focused and deterministic.

php
Copy code
'temperature' => 0.7,
📋 Usage
Choose a provider and model using the dropdowns in the header. Type a message and press Enter or click Send. Use New chat to clear the thread.
🛠️ Troubleshooting
Error: "You exceeded your current quota"
This error indicates your OpenAI free credits have been used up, or your API usage has exceeded the quota.
Solution: Add a billing method at OpenAI Billing to continue using the API.

Empty Message Handling:
The chatbot will warn if the input field is left empty.

API Debug Logs:
If the chatbot fails, check the debug_log.txt for the raw API response to diagnose the issue.

## 🗺️ Roadmap & Future Features

We're continuously working on improving the chatbot. Here's what's coming next:

### 🎯 Phase 1: Enhanced User Experience (Q1 2024)
- [ ] **Streaming Responses**: Real-time token streaming for faster response display
- [ ] **Message Editing**: Edit and regenerate previous messages
- [ ] **Code Syntax Highlighting**: Better code block rendering with syntax highlighting
- [ ] **Markdown Support**: Full markdown rendering in chat messages
- [ ] **Dark/Light Theme Toggle**: User preference for UI themes
- [ ] **Message Search**: Search through conversation history
- [ ] **Keyboard Shortcuts**: Power user keyboard navigation

### 🚀 Phase 2: Advanced Features (Q2 2024)
- [ ] **Function Calling Support**: Execute functions/tools via AI providers
- [ ] **Vision/Image Input**: Upload and analyze images with vision-capable models
- [ ] **Voice Input/Output**: Speech-to-text and text-to-speech integration
- [ ] **Multi-modal Support**: Handle images, documents, and other file types
- [ ] **Custom System Prompts**: Save and manage custom system prompts
- [ ] **Prompt Templates**: Library of reusable prompt templates
- [ ] **Conversation Sharing**: Share conversations via public links
- [ ] **Export Formats**: Export to PDF, DOCX, and more formats

### 🔐 Phase 3: Multi-User & Enterprise (Q3 2024)
- [ ] **User Authentication**: Login system with user accounts
- [ ] **Role-Based Access Control**: Admin, user, and guest roles
- [ ] **API Key Management**: Per-user API key configuration
- [ ] **Usage Quotas**: Set usage limits per user/organization
- [ ] **Billing Integration**: Stripe/PayPal integration for subscriptions
- [ ] **Team Collaboration**: Shared workspaces and team conversations
- [ ] **Audit Logging**: Track all user actions and API usage
- [ ] **SSO Integration**: Single Sign-On with OAuth providers

### 🤖 Phase 4: AI Enhancements (Q4 2024)
- [ ] **Model Fine-tuning**: Support for custom fine-tuned models
- [ ] **RAG (Retrieval Augmented Generation)**: Connect to knowledge bases
- [ ] **Vector Database Integration**: Semantic search capabilities
- [ ] **Multi-Agent Systems**: Multiple AI agents working together
- [ ] **Chain of Thought**: Advanced reasoning capabilities
- [ ] **Custom Provider Support**: Plugin system for custom AI providers
- [ ] **Model Comparison**: Side-by-side comparison of different models
- [ ] **A/B Testing**: Test different prompts and models

### 🔧 Phase 5: Infrastructure & Performance (2025)
- [ ] **WebSocket Support**: Real-time bidirectional communication
- [ ] **Redis Caching**: Advanced caching with Redis
- [ ] **Queue System**: Background job processing for long tasks
- [ ] **CDN Integration**: Fast asset delivery
- [ ] **Docker Support**: Containerized deployment
- [ ] **Kubernetes Helm Charts**: Easy Kubernetes deployment
- [ ] **Horizontal Scaling**: Support for multiple server instances
- [ ] **Database Sharding**: Scale database for large deployments

### 📱 Phase 6: Mobile & Integrations (2025)
- [ ] **Mobile App**: Native iOS and Android applications
- [ ] **PWA Support**: Progressive Web App capabilities
- [ ] **Slack Integration**: Bot integration for Slack workspaces
- [ ] **Discord Bot**: Discord server integration
- [ ] **Telegram Bot**: Telegram bot support
- [ ] **WordPress Plugin**: WordPress integration
- [ ] **REST API**: Public API for third-party integrations
- [ ] **Webhooks**: Event webhooks for integrations

### 🎨 Phase 7: UI/UX Improvements (Ongoing)
- [ ] **Customizable UI**: User-customizable interface layouts
- [ ] **Accessibility**: WCAG 2.1 AA compliance
- [ ] **Internationalization**: Multi-language support (i18n)
- [ ] **Responsive Design**: Enhanced mobile experience
- [ ] **Animation & Transitions**: Smooth UI animations
- [ ] **Custom Branding**: White-label options
- [ ] **Dashboard Analytics**: Visual analytics dashboard
- [ ] **Real-time Notifications**: Browser notifications

### 🔬 Phase 8: Advanced Analytics (2025)
- [ ] **Cost Tracking**: Track API costs per conversation/user
- [ ] **Performance Metrics**: Response time and quality metrics
- [ ] **Usage Reports**: Detailed usage reports and insights
- [ ] **Predictive Analytics**: Usage predictions and recommendations
- [ ] **A/B Testing Dashboard**: Visual A/B test results
- [ ] **Custom Dashboards**: User-configurable analytics dashboards

### 💡 Ideas & Considerations
- [ ] **Plugin System**: Extensible plugin architecture
- [ ] **Marketplace**: Plugin and template marketplace
- [ ] **Community Features**: User forums and sharing
- [ ] **Documentation Generator**: Auto-generate docs from conversations
- [ ] **AI Training Data Export**: Export conversations for training
- [ ] **Compliance Tools**: GDPR, HIPAA compliance features
- [ ] **Backup & Restore**: Automated backup system
- [ ] **Migration Tools**: Import from other chatbot platforms

### 🤝 Contributing
We welcome contributions! If you'd like to help implement any of these features, please:
1. Check existing issues and pull requests
2. Fork the repository
3. Create a feature branch
4. Submit a pull request

See our [Contributing Guidelines](CONTRIBUTING.md) for more details.

### 📅 Timeline Notes
- Timeline is approximate and subject to change
- Features may be reprioritized based on user feedback
- Some features may be released earlier if community contributions accelerate development
- We're open to suggestions - [open an issue](https://github.com/DexterWura/chatbot/issues) to propose new features!

📄 License
This project is licensed under the MIT License. See the LICENSE file for more details.

📧 Contact
For any issues or feedback, feel free to contact:

Dexter (Repository Owner)

🎉 Acknowledgements
OpenAI for providing the API
Bootstrap for styling
Inspired by ChatGPT!
