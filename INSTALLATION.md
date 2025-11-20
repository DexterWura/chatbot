# Installation Guide

## Auto-Installation

The chatbot includes an automatic installation wizard that will guide you through the setup process.

### First-Time Installation

1. **Upload Files**: Upload all files to your web server
2. **Set Permissions**: Ensure the `storage/` directory is writable:
   ```bash
   chmod -R 755 storage/
   ```
3. **Access Installer**: Navigate to your domain in a browser. You will be automatically redirected to the installer if the application is not yet installed.
4. **Follow Wizard**: Complete the installation wizard:
   - **Step 1**: System requirements check
   - **Step 2**: Database configuration
   - **Step 3**: API keys setup
   - **Step 4**: Installation complete

### Manual Installation

If you prefer to install manually:

#### 1. Create `.env` File

Copy `.env.example` to `.env` and configure:

```env
# Database Configuration
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=chatbot_db
DB_USER=your_username
DB_PASS=your_password
DB_PORT=3306

# API Keys
OPENAI_API_KEY=sk-...
DEEPSEEK_API_KEY=ds-...
GEMINI_API_KEY=AIza...
ANTHROPIC_API_KEY=sk-ant-...

# Application Settings
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
CACHE_ENABLED=true
CACHE_TTL=300
LOG_LEVEL=INFO
```

#### 2. Create Database

```sql
CREATE DATABASE chatbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3. Run Migrations

The installer will automatically run migrations. To run manually:

```php
<?php
require_once 'api/autoload.php';
use Chatbot\Core\{PDODatabase, DatabaseMigration};

$db = new PDODatabase('localhost', 'chatbot_db', 'user', 'pass');
$db->connect();

$migration = new DatabaseMigration($db);
$migration->runMigrations();
```

#### 4. Create `.installed` File

```bash
touch .installed
```

## Requirements

- PHP 7.4 or higher
- PDO extension
- PDO MySQL/PostgreSQL extension (for database)
- cURL extension
- JSON extension
- Write permissions on `storage/` directory

## Database Support

The application supports:
- **MySQL** (recommended)
- **PostgreSQL**
- **SQLite** (for development)

## Post-Installation

After installation:
1. The `.installed` file is created to prevent re-installation
2. `.env` file contains your configuration
3. Database tables are created automatically
4. You can now access the chatbot at `index.html`

## Troubleshooting

### Installation Fails

- Check PHP error logs
- Verify database credentials
- Ensure `storage/` directory is writable
- Check that all required PHP extensions are installed

### Database Connection Issues

- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall rules
- Verify user has CREATE TABLE permissions

### Permission Errors

```bash
chmod -R 755 storage/
chmod 644 .env
```

## Security Notes

- Never commit `.env` file to version control
- Use strong database passwords
- Keep API keys secure
- Regularly update the application
- Use HTTPS in production

## Uninstallation

To uninstall:
1. Delete `.installed` file
2. Delete database tables (optional)
3. Delete `.env` file (optional)

The installer will run again on next access.
