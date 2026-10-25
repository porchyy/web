<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$projectRoot = dirname(__DIR__);
$releaseDir  = $projectRoot . DIRECTORY_SEPARATOR . 'dist';

if (!is_dir($releaseDir)) {
    mkdir($releaseDir, 0775, true);
}

$timestamp   = date('Ymd-His');
$archiveName = 'dbt-web-' . $timestamp . '.zip';
$archivePath = $releaseDir . DIRECTORY_SEPARATOR . $archiveName;

if (!class_exists('ZipArchive')) {
    if (PHP_OS_FAMILY === 'Windows') {
        $psScript = __DIR__ . DIRECTORY_SEPARATOR . 'package-release.ps1';
        if (file_exists($psScript)) {
            passthru("powershell -ExecutionPolicy Bypass -File \"{$psScript}\"", $exitCode);
            exit($exitCode);
        }
    }
    fwrite(STDERR, "Error: PHP ZipArchive extension is required to package release.\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Error: Cannot create archive at {$archivePath}\n");
    exit(1);
}

// Single root and specific files
$specificFiles = [
    '.htaccess',
    '.user.ini',
    '.env.example',
    'index.php',
    'error.php',
    'favicon.ico',
    'uploads/.htaccess',
    'scratch/.gitkeep',
    'tools/check-production.php',
    'tools/create-admin.php',
    'tools/install-schema.php',
    'tools/setup-production.php',
    'tools/package-release.php',
    'docs/production.md',
    'deploy/php-production.ini',
    'deploy/apache-production.conf',
    'database/migrations/2026_10_class_timetable_images.php',
];

foreach ($specificFiles as $rel) {
    $full = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (file_exists($full)) {
        $zip->addFile($full, $rel);
    }
}

// Ensure empty directories are registered
$zip->addEmptyDir('uploads');
$zip->addEmptyDir('scratch');
$zip->addEmptyDir('scratch/cache');

// Recursive directories
$recursiveDirs = [
    'admin',
    'api',
    'assets',
    'includes',
    'pages',
    'database/schema',
];

foreach ($recursiveDirs as $relDir) {
    $fullDir = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relDir);
    if (!is_dir($fullDir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isLink()) {
            throw new RuntimeException("Symlinks are not allowed in release packages: " . $file->getPathname());
        }
        if ($file->isFile()) {
            $filePath = $file->getPathname();
            $entryPath = str_replace(
                '\\',
                '/',
                substr($filePath, strlen($projectRoot) + 1)
            );
            $zip->addFile($filePath, $entryPath);
        }
    }
}

$zip->close();

$hash = hash_file('sha256', $archivePath);
file_put_contents($archivePath . '.sha256', $hash . '  ' . $archiveName . PHP_EOL);

echo "Release archive created successfully:\n";
echo "  Path:   {$archivePath}\n";
echo "  Size:   " . number_format(filesize($archivePath) / 1024, 2) . " KB\n";
echo "  SHA256: {$hash}\n";
