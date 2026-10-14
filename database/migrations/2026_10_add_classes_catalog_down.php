<?php
/**
 * Rollback Migration: Drop foreign key, drop class_id column, drop classes table
 * Version: 2026_10_add_classes_catalog_down.php
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

$pdo = db();

try {
    // 1. Drop foreign key if exists
    $fkCheck = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'class_schedule' 
          AND CONSTRAINT_NAME = 'fk_schedule_class'
    ")->fetch();

    if ($fkCheck) {
        $pdo->exec("ALTER TABLE `class_schedule` DROP FOREIGN KEY `fk_schedule_class`;");
    }

    // 2. Drop class_id column if exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM `class_schedule` LIKE 'class_id'")->fetch();
    if ($colCheck) {
        $pdo->exec("ALTER TABLE `class_schedule` DROP COLUMN `class_id`;");
    }

    // 3. Drop classes table if exists
    $pdo->exec("DROP TABLE IF EXISTS `classes`;");

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    echo "Rollback completed successfully!" . PHP_EOL;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Rollback failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
