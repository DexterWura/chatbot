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

📄 License
This project is licensed under the MIT License. See the LICENSE file for more details.

📧 Contact
For any issues or feedback, feel free to contact:

Dexter (Repository Owner)

🎉 Acknowledgements
OpenAI for providing the API
Bootstrap for styling
Inspired by ChatGPT!
