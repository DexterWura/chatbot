# Changelog

## [2.0.0] - Auto-Installation & Database Support

### Added
- **Auto-Installation Wizard**: Complete web-based installer for first-time setup
- **Database Support**: MySQL, PostgreSQL, and SQLite support
- **Database Migrations**: Automatic schema creation and updates
- **.env Configuration**: Environment-based configuration management
- **DatabaseRepository**: Database-backed conversation storage
- **PDODatabase**: PDO-based database abstraction layer
- **EnvManager**: .env file management
- **Installation Detection**: Automatic redirect to installer if not installed
- **Requirements Checker**: Validates server requirements during installation
- **Database Connection Testing**: Tests database credentials before installation

### Enhanced
- **ConfigManager**: Now loads from .env file with fallback to environment variables
- **Router**: Automatically uses database if configured, falls back to file storage
- **ConversationRepository**: Now implements ConversationRepositoryInterface
- **Error Handling**: Improved error messages and logging

### Removed
- `chat.php` - Legacy backward-compatible shim (no longer needed)
- `debug_log.txt` - Replaced by proper logging system
- `script.js` - Duplicate file (functionality in assets/js/app.js)
- `style.css` - Moved to assets/css/style.css

### Security
- Added `.htaccess` rules to protect sensitive files
- `.env` file excluded from version control
- Installation flag prevents re-installation

### Documentation
- Added `INSTALLATION.md` with detailed installation guide
- Updated `README.md` with installation instructions
- Added `ARCHITECTURE.md` with design patterns documentation

## [1.0.0] - Initial Release

### Features
- Multi-provider chatbot (OpenAI, DeepSeek, Gemini, Claude)
- OOP architecture with design patterns
- File-based conversation storage
- Modern UI
