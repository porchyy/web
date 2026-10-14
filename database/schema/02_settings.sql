-- Module 02: Settings Table (Key/Value Store)
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(50) PRIMARY KEY,
  `svalue` TEXT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
