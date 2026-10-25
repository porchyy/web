-- Database Schema for DBT Web (เทคโนโลยีธุรกิจดิจิทัล)
-- Idempotent Migration: Safe to run anytime, will NOT drop tables or delete existing data.
-- Character Set: utf8mb4 / utf8mb4_unicode_ci

CREATE DATABASE IF NOT EXISTS `dbt_web` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dbt_web`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 2. Settings Table (Key/Value)
CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(50) PRIMARY KEY,
  `svalue` TEXT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 3. News Categories Table
CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4. News Articles Table
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `summary` TEXT NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `is_featured` TINYINT (1) NOT NULL DEFAULT 0,
  `published_at` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_news_cat` (`category_id`),
  INDEX `idx_news_featured_pub` (`is_featured`, `published_at` DESC),
  INDEX `idx_news_pub` (`published_at` DESC),
  CONSTRAINT `fk_news_category` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 5. Classes Master Catalog Table
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
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 6. Class Schedule Table
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

-- 7. Activities Table
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

-- 8. Teachers Table
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) NOT NULL,
  `specialization` VARCHAR(255) NULL,
  `email` VARCHAR(100) NULL,
  `image_path` VARCHAR(255) NULL,
  `is_head` TINYINT (1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  INDEX `idx_teacher_order` (`is_head` DESC, `sort_order` ASC, `id` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 9. Club Members Table
CREATE TABLE IF NOT EXISTS `club_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(20) NULL,
  `name` VARCHAR(100) NOT NULL,
  `club_role` VARCHAR(100) NOT NULL,
  `is_club_head` TINYINT (1) NOT NULL DEFAULT 0,
  `image_path` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  INDEX `idx_club_order` (`is_club_head` DESC, `sort_order` ASC, `id` ASC)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 9. Per-group timetable presentation
CREATE TABLE IF NOT EXISTS `class_timetables` (
  `class_group` VARCHAR(50) PRIMARY KEY,
  `display_mode` ENUM('manual', 'image') NOT NULL DEFAULT 'manual',
  `image_path` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NOT NULL DEFAULT '',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
