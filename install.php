<?php
/**
 * Auto-Installation Wizard
 * Run this file on first deployment to set up the application
 */

// Check if already installed
if (file_exists(__DIR__ . '/.installed')) {
    header('Location: index.html');
    exit;
}

// Handle installation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/api/autoload.php';
    
    use Chatbot\Core\{
        EnvManager,
        PDODatabase,
        DatabaseMigration,
        DatabaseRepository
    };

    header('Content-Type: application/json');
    
    $step = $_POST['step'] ?? '';
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($step) {
            case 'check_requirements':
                $response = checkRequirements();
                break;
            
            case 'database_test':
                $response = testDatabaseConnection($_POST);
                break;
            
            case 'install':
                $response = performInstallation($_POST);
                break;
            
            default:
                $response = ['success' => false, 'message' => 'Invalid step'];
        }
    } catch (\Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

function checkRequirements(): array {
    $checks = [
        'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'pdo' => extension_loaded('pdo'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'curl' => extension_loaded('curl'),
        'json' => extension_loaded('json'),
        'writable_storage' => is_writable(__DIR__ . '/storage') || is_writable(__DIR__),
    ];

    $allPassed = array_reduce($checks, fn($carry, $check) => $carry && $check, true);

    return [
        'success' => $allPassed,
        'checks' => $checks,
        'php_version' => PHP_VERSION,
    ];
}

function testDatabaseConnection(array $data): array {
    try {
        $db = new PDODatabase(
            $data['db_host'] ?? 'localhost',
            $data['db_name'] ?? '',
            $data['db_user'] ?? '',
            $data['db_pass'] ?? '',
            $data['db_driver'] ?? 'mysql'
        );

        if ($db->connect()) {
            return ['success' => true, 'message' => 'Database connection successful'];
        } else {
            return ['success' => false, 'message' => 'Failed to connect to database'];
        }
    } catch (\Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function performInstallation(array $data): array {
    try {
        // Create .env file
        $envManager = new EnvManager();
        $envData = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_DRIVER' => $data['db_driver'] ?? 'mysql',
            'DB_HOST' => $data['db_host'] ?? 'localhost',
            'DB_NAME' => $data['db_name'] ?? '',
            'DB_USER' => $data['db_user'] ?? '',
            'DB_PASS' => $data['db_pass'] ?? '',
            'DB_PORT' => $data['db_port'] ?? '3306',
            'OPENAI_API_KEY' => $data['openai_key'] ?? '',
            'DEEPSEEK_API_KEY' => $data['deepseek_key'] ?? '',
            'GEMINI_API_KEY' => $data['gemini_key'] ?? '',
            'ANTHROPIC_API_KEY' => $data['anthropic_key'] ?? '',
            'RATE_LIMIT_ENABLED' => 'true',
            'RATE_LIMIT_MAX' => '60',
            'RATE_LIMIT_WINDOW' => '60',
            'CACHE_ENABLED' => 'true',
            'CACHE_TTL' => '300',
            'LOG_LEVEL' => 'INFO',
        ];

        if (!$envManager->save($envData)) {
            return ['success' => false, 'message' => 'Failed to create .env file'];
        }

        // Create storage directories
        $dirs = ['storage/conversations', 'storage/logs'];
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Run database migrations
        $db = new PDODatabase(
            $data['db_host'] ?? 'localhost',
            $data['db_name'] ?? '',
            $data['db_user'] ?? '',
            $data['db_pass'] ?? '',
            $data['db_driver'] ?? 'mysql'
        );

        if (!$db->connect()) {
            return ['success' => false, 'message' => 'Failed to connect to database'];
        }

        $migration = new DatabaseMigration($db);
        if (!$migration->runMigrations()) {
            return ['success' => false, 'message' => 'Database migration failed'];
        }

        // Create installed flag
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));

        return ['success' => true, 'message' => 'Installation completed successfully'];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Installation</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .check-item {
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.pass {
            background: #d4edda;
            color: #155724;
        }
        .check-item.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .progress {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: #667eea;
            transition: width 0.3s;
            width: 0%;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="installer">
        <h1>🚀 Chatbot Installation</h1>
        <p class="subtitle">Welcome! Let's set up your chatbot in a few simple steps.</p>

        <div class="progress">
            <div class="progress-bar" id="progress-bar"></div>
        </div>

        <div class="error" id="error"></div>
        <div class="success" id="success" style="display: none;"></div>

        <!-- Step 1: Requirements Check -->
        <div class="step active" id="step-1">
            <h2>Step 1: System Requirements</h2>
            <p style="margin-bottom: 20px; color: #666;">Checking if your server meets the requirements...</p>
            <div id="requirements-list"></div>
            <button class="btn" onclick="checkRequirements()">Check Requirements</button>
        </div>

        <!-- Step 2: Database Configuration -->
        <div class="step" id="step-2">
            <h2>Step 2: Database Configuration</h2>
            <p style="margin-bottom: 20px; color: #666;">Enter your database credentials:</p>
            <form id="db-form">
                <div class="form-group">
                    <label>Database Driver</label>
                    <select name="db_driver" required>
                        <option value="mysql">MySQL</option>
                        <option value="pgsql">PostgreSQL</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" required>
                </div>
                <div class="form-group">
                    <label>Database User</label>
                    <input type="text" name="db_user" required>
                </div>
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass" required>
                </div>
                <div class="form-group">
                    <label>Database Port</label>
                    <input type="number" name="db_port" value="3306" required>
                </div>
                <button type="button" class="btn" onclick="testDatabase()">Test Connection</button>
            </form>
        </div>

        <!-- Step 3: API Keys -->
        <div class="step" id="step-3">
            <h2>Step 3: API Keys</h2>
            <p style="margin-bottom: 20px; color: #666;">Enter your AI provider API keys (at least one required):</p>
            <form id="api-form">
                <div class="form-group">
                    <label>OpenAI API Key</label>
                    <input type="text" name="openai_key" placeholder="sk-...">
                </div>
                <div class="form-group">
                    <label>DeepSeek API Key</label>
                    <input type="text" name="deepseek_key" placeholder="ds-...">
                </div>
                <div class="form-group">
                    <label>Google Gemini API Key</label>
                    <input type="text" name="gemini_key" placeholder="AIza...">
                </div>
                <div class="form-group">
                    <label>Anthropic (Claude) API Key</label>
                    <input type="text" name="anthropic_key" placeholder="sk-ant-...">
                </div>
                <button type="button" class="btn" onclick="install()">Complete Installation</button>
            </form>
        </div>

        <!-- Step 4: Installation Complete -->
        <div class="step" id="step-4">
            <h2>✅ Installation Complete!</h2>
            <p style="margin-bottom: 20px; color: #666;">Your chatbot is ready to use!</p>
            <a href="index.html" class="btn">Go to Chatbot</a>
        </div>

        <div class="loading" id="loading">
            <p>Processing...</p>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progress-bar').style.width = progress + '%';
        }

        function showStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById(`step-${step}`).classList.add('active');
            currentStep = step;
            updateProgress();
        }

        function showError(message) {
            const errorEl = document.getElementById('error');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
            setTimeout(() => errorEl.style.display = 'none', 5000);
        }

        function showSuccess(message) {
            const successEl = document.getElementById('success');
            successEl.textContent = message;
            successEl.style.display = 'block';
        }

        async function checkRequirements() {
            document.getElementById('loading').classList.add('active');
            try {
                const response = await fetch('install.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'step=check_requirements'
                });
                const data = await response.json();
                
                const listEl = document.getElementById('requirements-list');
                listEl.innerHTML = '';
                
                const checks = {
                    'php_version': `PHP Version (${data.php_version})`,
                    'pdo': 'PDO Extension',
                    'pdo_mysql': 'PDO MySQL Extension',
                    'curl': 'cURL Extension',
                    'json': 'JSON Extension',
                    'writable_storage': 'Writable Storage Directory'
                };

                Object.entries(data.checks).forEach(([key, passed]) => {
                    const item = document.createElement('div');
                    item.className = `check-item ${passed ? 'pass' : 'fail'}`;
                    item.innerHTML = `
                        <span>${checks[key] || key}</span>
                        <span>${passed ? '✓' : '✗'}</span>
                    `;
                    listEl.appendChild(item);
                });

                if (data.success) {
                    showSuccess('All requirements met!');
                    setTimeout(() => showStep(2), 1500);
                } else {
                    showError('Some requirements are not met. Please fix them before continuing.');
                }
            } catch (error) {
                showError('Failed to check requirements: ' + error.message);
            } finally {
                document.getElementById('loading').classList.remove('active');
            }
        }

        async function testDatabase() {
            const form = document.getElementById('db-form');
            const formData = new FormData(form);
            formData.append('step', 'database_test');
            
            document.getElementById('loading').classList.add('active');
            try {
                const response = await fetch('install.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Database connection successful!');
                    setTimeout(() => showStep(3), 1500);
                } else {
                    showError(data.message || 'Database connection failed');
                }
            } catch (error) {
                showError('Failed to test database: ' + error.message);
            } finally {
                document.getElementById('loading').classList.remove('active');
            }
        }

        async function install() {
            const dbForm = document.getElementById('db-form');
            const apiForm = document.getElementById('api-form');
            
            const formData = new FormData();
            formData.append('step', 'install');
            
            // Add database fields
            new FormData(dbForm).forEach((value, key) => {
                formData.append(key, value);
            });
            
            // Add API keys
            new FormData(apiForm).forEach((value, key) => {
                formData.append(key, value);
            });
            
            document.getElementById('loading').classList.add('active');
            try {
                const response = await fetch('install.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Installation completed successfully!');
                    setTimeout(() => showStep(4), 1500);
                } else {
                    showError(data.message || 'Installation failed');
                }
            } catch (error) {
                showError('Installation error: ' + error.message);
            } finally {
                document.getElementById('loading').classList.remove('active');
            }
        }

        // Auto-check requirements on load
        window.addEventListener('load', () => {
            setTimeout(checkRequirements, 500);
        });
    </script>
</body>
</html>
