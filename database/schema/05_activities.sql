-- Module 05: Activities Table
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

CREATE TABLE IF NOT EXISTS `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `event_date` DATE NOT NULL,
  `event_date_end` DATE NULL,
  `time_start` TIME NULL,
  `time_end` TIME NULL,
  `location` VARCHAR(100) NULL,
  `badge_text` VARCHAR(50) NULL,
  `action_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_act_date_time` (`event_date` ASC, `time_start` ASC),
  INDEX `idx_act_date` (`event_date`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
