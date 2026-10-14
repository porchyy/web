<?php
declare(strict_types=1);

/**
 * CLI Database Seeder Tool
 * Usage: php database/seed.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "=== DBT Web: Database Seeder Tool ===\n";

$pdo = db();
$seedDir = __DIR__ . '/seeds';

if (!is_dir($seedDir)) {
    echo "❌ Seed directory not found: {$seedDir}\n";
    exit(1);
}

$files = glob($seedDir . '/*.sql');
sort($files);

if (empty($files)) {
    echo "⚠️ No SQL seed files found in {$seedDir}\n";
    exit(0);
}

$pdo->beginTransaction();

try {
    foreach ($files as $file) {
        $basename = basename($file);
        echo "Applying seed: {$basename}... ";
        $sql = file_get_contents($file);
        if (!empty(trim($sql))) {
            $pdo->exec($sql);
        }
        echo "✓ OK\n";
    }
    $pdo->commit();
    echo "\n🎉 All database seeds executed successfully!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n❌ Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
