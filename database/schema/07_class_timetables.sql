CREATE TABLE IF NOT EXISTS `class_timetables` (
  `class_group` VARCHAR(50) PRIMARY KEY,
  `display_mode` ENUM('manual', 'image') NOT NULL DEFAULT 'manual',
  `image_path` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NOT NULL DEFAULT '',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
