-- Seed 01: Default Admin User
-- Credentials: admin / admin1234

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`)
VALUES (
  1,
  'admin',
  '$2y$10$4Fj6cR3juFRqnPpc7bRe5uDrYrhTDK6NY/VK2ryY928msB.isyIu6',
  'ผู้ดูแลระบบสาขาวิชา',
  'admin'
) ON DUPLICATE KEY UPDATE `username` = `username`;
