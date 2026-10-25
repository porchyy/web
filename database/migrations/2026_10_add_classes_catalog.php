<?php
/**
 * Migration: Create classes table, migrate distinct courses, and link class_schedule
 * Version: 2026_10_add_classes_catalog.php
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

$pdo = db();

try {
    // 1. Create classes table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `classes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `subject_code` VARCHAR(20) NOT NULL UNIQUE,
            `subject_name` VARCHAR(100) NOT NULL,
            `default_teacher` VARCHAR(100) NULL,
            `default_room` VARCHAR(50) NULL,
            `credit` TINYINT NOT NULL DEFAULT 3,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_class_code` (`subject_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Add class_id to class_schedule if not exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM `class_schedule` LIKE 'class_id'")->fetch();
    if (!$colCheck) {
        $pdo->exec("
            ALTER TABLE `class_schedule`
            ADD COLUMN `class_id` INT NULL AFTER `id`,
            ADD INDEX `idx_sched_class_id` (`class_id`);
        ");
    }

    // 3. Migrate DISTINCT courses from class_schedule into classes
    $pdo->exec("
        INSERT INTO `classes` (`subject_code`, `subject_name`, `default_teacher`, `default_room`)
        SELECT 
            TRIM(`subject_code`) AS `subject_code`,
            MAX(TRIM(`subject_name`)) AS `subject_name`,
            MAX(TRIM(`teacher_name`)) AS `default_teacher`,
            MAX(TRIM(`room`)) AS `default_room`
        FROM `class_schedule`
        WHERE `subject_code` NOT IN ('FLAG', 'LUNCH', '')
          AND `subject_name` NOT LIKE '%เสาธง%'
          AND `subject_name` NOT LIKE '%พักกลางวัน%'
        GROUP BY TRIM(`subject_code`)
        ON DUPLICATE KEY UPDATE 
            `subject_name` = VALUES(`subject_name`),
            `default_teacher` = VALUES(`default_teacher`),
            `default_room` = VALUES(`default_room`);
    ");

    // 4. Link class_id back to class_schedule
    $pdo->exec("
        UPDATE `class_schedule` s
        JOIN `classes` c ON TRIM(s.subject_code) = c.subject_code
        SET s.class_id = c.id
        WHERE s.class_id IS NULL;
    ");

    // 5. Add Foreign Key if not exists
    $fkCheck = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'class_schedule' 
          AND CONSTRAINT_NAME = 'fk_schedule_class'
    ")->fetch();

    if (!$fkCheck) {
        $pdo->exec("
            ALTER TABLE `class_schedule`
            ADD CONSTRAINT `fk_schedule_class`
            FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)
            ON UPDATE CASCADE ON DELETE SET NULL;
        ");
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    echo "Migration completed successfully!" . PHP_EOL;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
