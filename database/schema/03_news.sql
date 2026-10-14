-- Module 03: News & Categories Tables
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

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
