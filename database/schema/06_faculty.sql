-- Module 06: Faculty & Club Members Tables
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

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
