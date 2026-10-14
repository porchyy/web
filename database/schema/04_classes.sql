-- Module 04: Classes Master Catalog & Timetable Schedule Tables
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

CREATE TABLE IF NOT EXISTS `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_code` VARCHAR(20) NOT NULL UNIQUE,
  `subject_name` VARCHAR(100) NOT NULL,
  `course_type` ENUM('professional', 'regular') NOT NULL DEFAULT 'professional',
  `default_teacher` VARCHAR(100) NULL,
  `default_room` VARCHAR(50) NULL,
  `credit` TINYINT NOT NULL DEFAULT 3,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_class_code` (`subject_code`),
  INDEX `idx_class_type` (`course_type`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `class_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NULL,
  `day_of_week` TINYINT NOT NULL COMMENT '1=จันทร์, 2=อังคาร, 3=พุธ, 4=พฤหัสบดี, 5=ศุกร์',
  `time_start` TIME NOT NULL,
  `time_end` TIME NOT NULL,
  `subject_code` VARCHAR(20) NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `teacher_name` VARCHAR(100) NOT NULL,
  `room` VARCHAR(50) NOT NULL,
  `class_group` VARCHAR(50) NOT NULL DEFAULT 'ปวช.2/1',
  INDEX `idx_sched_class_id` (`class_id`),
  INDEX `idx_sched_group_day` (`class_group`, `day_of_week`, `time_start`),
  INDEX `idx_sched_day` (`day_of_week`),
  CONSTRAINT `fk_schedule_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
