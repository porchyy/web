<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$root = dirname(__DIR__);

echo "====================================================\n";
echo "   DBT Web - Production Setup & Deployment Wizard   \n";
echo "====================================================\n\n";

// Helper to read CLI options or environment
function get_opt_val(array $options, string $long, string $envName, ?string $default = null): ?string
{
    if (isset($options[$long]) && $options[$long] !== false) {
        return (string) $options[$long];
    }
    $envVal = getenv($envName);
    if ($envVal !== false && $envVal !== '') {
        return $envVal;
    }
    return $default;
}

function prompt_input(string $label, ?string $default = null, bool $isSecret = false): string
{
    $prompt = $default !== null ? "{$label} [{$default}]: " : "{$label}: ";
    echo $prompt;
    $val = trim((string) fgets(STDIN));
    if ($val === '' && $default !== null) {
        return $default;
    }
    return $val;
}

$cliOpts = getopt('', [
    'db-host:',
    'db-port:',
    'db-name:',
    'db-user:',
    'db-pass:',
    'base-url:',
    'admin-user:',
    'admin-pass:',
    'non-interactive',
    'skip-schema',
]);

$isNonInteractive = isset($cliOpts['non-interactive']);

// 1. Check PHP Version & Extensions
echo "[1/6] Verifying PHP environment...\n";
if (PHP_VERSION_ID < 80200) {
    fwrite(STDERR, "FAIL: PHP 8.2 or newer is required (current: " . PHP_VERSION . ")\n");
    exit(1);
}
$missingExts = [];
foreach (['pdo_mysql', 'fileinfo', 'mbstring', 'dom'] as $ext) {
    if (!extension_loaded($ext)) {
        $missingExts[] = $ext;
    }
}
if (!empty($missingExts)) {
    fwrite(STDERR, "FAIL: Missing required PHP extensions: " . implode(', ', $missingExts) . "\n");
    exit(1);
}
echo "      PHP " . PHP_VERSION . " and required extensions are available.\n\n";

// 2. Gather Configuration
echo "[2/6] Configuring Production Settings...\n";
$dbHost  = get_opt_val($cliOpts, 'db-host', 'DB_HOST');
$dbPort  = get_opt_val($cliOpts, 'db-port', 'DB_PORT');
$dbName  = get_opt_val($cliOpts, 'db-name', 'DB_NAME');
$dbUser  = get_opt_val($cliOpts, 'db-user', 'DB_USER');
$dbPass  = get_opt_val($cliOpts, 'db-pass', 'DB_PASS');
$baseUrl = get_opt_val($cliOpts, 'base-url', 'BASE_URL');
$adminUser = get_opt_val($cliOpts, 'admin-user', 'ADMIN_USERNAME');
$adminPass = get_opt_val($cliOpts, 'admin-pass', 'ADMIN_PASSWORD');

if (!$isNonInteractive) {
    if ($dbHost === null)  $dbHost  = prompt_input('Database Host', 'localhost');
    if ($dbPort === null)  $dbPort  = prompt_input('Database Port', '3306');
    if ($dbName === null)  $dbName  = prompt_input('Database Name', 'dbt_web');
    if ($dbUser === null)  $dbUser  = prompt_input('Dedicated Database User', 'dbt_app');
    if ($dbPass === null)  $dbPass  = prompt_input('Database Password', null, true);
    if ($baseUrl === null) $baseUrl = prompt_input('Production Site URL (HTTPS)', 'https://dbt.example.ac.th');
}

// Validate inputs
$errors = [];
if (empty($dbHost)) $errors[] = 'Database host is required';
if (empty($dbPort)) $errors[] = 'Database port is required';
if (empty($dbName)) $errors[] = 'Database name is required';
if (empty($dbUser) || strtolower($dbUser) === 'root') $errors[] = 'A dedicated database account is required (root is forbidden)';
if (empty($dbPass) || str_contains($dbPass, 'replace-with-')) $errors[] = 'A real database password is required';
if (empty($baseUrl) || !str_starts_with($baseUrl, 'https://') || str_contains($baseUrl, '.example')) {
    $errors[] = 'A valid HTTPS BASE_URL is required (e.g. https://dbt.your-college.ac.th)';
}

if (!empty($errors)) {
    fwrite(STDERR, "Configuration Validation Failed:\n");
    foreach ($errors as $err) {
        fwrite(STDERR, " - {$err}\n");
    }
    exit(1);
}

// 3. Test Database Connectivity
echo "\n[3/6] Testing database connectivity...\n";
try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "      Connected to database '{$dbName}' successfully.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "FAIL: Could not connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

// 4. Install Schema (if needed)
echo "\n[4/6] Verifying schema and tables...\n";
$tablesExist = (bool) $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
if (!$tablesExist && !isset($cliOpts['skip-schema'])) {
    echo "      Installing modular schema files from database/schema/...\n";
    $schemaFiles = glob($root . '/database/schema/*.sql');
    sort($schemaFiles);
    foreach ($schemaFiles as $file) {
        $sql = file_get_contents($file);
        if (preg_match('/^\s*(?:USE\b|CREATE\s+DATABASE\b)/mi', $sql)) {
            fwrite(STDERR, "FAIL: Schema file contains database-switching commands: " . basename($file) . "\n");
            exit(1);
        }
        $pdo->exec($sql);
    }
    echo "      Database schema installed successfully.\n";
} else {
    echo "      Tables already exist. Checking migrations...\n";
    if (!$pdo->query("SHOW TABLES LIKE 'class_timetables'")->fetch()) {
        echo "      Applying class_timetables migration...\n";
        require_once $root . '/database/migrations/2026_10_class_timetable_images.php';
    }
}

// 5. Admin Provisioning
if (!empty($adminUser) && !empty($adminPass)) {
    echo "\n[5/6] Provisioning Administrator...\n";
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/D', $adminUser)) {
        fwrite(STDERR, "FAIL: Invalid admin username format (3-50 letters/numbers/._-)\n");
        exit(1);
    }
    if (strlen($adminPass) < 12) {
        fwrite(STDERR, "FAIL: Admin password must be at least 12 characters long\n");
        exit(1);
    }
    
    $existing = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $existing->execute([$adminUser]);
    if ($existing->fetch()) {
        echo "      User '{$adminUser}' already exists. Updating password...\n";
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $upd->execute([password_hash($adminPass, PASSWORD_DEFAULT), $adminUser]);
        echo "      Administrator password updated.\n";
    } else {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        $ins = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, 'admin')");
        $ins->execute([$adminUser, password_hash($adminPass, $algo), 'ผู้ดูแลระบบ']);
        echo "      Administrator '{$adminUser}' created successfully.\n";
    }
} else {
    echo "\n[5/6] Skipping admin provisioning (none provided). Ensure an admin account is created via tools/create-admin.php.\n";
}

// 6. Write .env & Verify Directories
echo "\n[6/6] Writing production environment and verifying directories...\n";

// Ensure directories
foreach (['uploads', 'scratch', 'scratch/cache'] as $dir) {
    $dirPath = $root . '/' . $dir;
    if (!is_dir($dirPath)) {
        @mkdir($dirPath, 0775, true);
    }
}

$envContent = <<<ENV
# Production Environment Configuration
APP_ENV=production
DB_AUTO_BOOTSTRAP=0
BASE_URL={$baseUrl}

# Database Credentials
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_NAME={$dbName}
DB_USER={$dbUser}
DB_PASS={$dbPass}

ENV;

file_put_contents($root . '/.env', $envContent);
echo "      Written {$root}/.env successfully.\n";

echo "\nRunning final production readiness checks...\n";
passthru("php " . escapeshellarg($root . '/tools/check-production.php'), $checkStatus);

if ($checkStatus === 0) {
    echo "\n>>> SUCCESS: System is now configured and ready for production launch! <<<\n";
    exit(0);
} else {
    echo "\n>>> ATTENTION: Please address the remaining items above to achieve full readiness. <<<\n";
    exit(1);
}
