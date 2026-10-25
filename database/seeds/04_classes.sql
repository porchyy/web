-- Seed 04: Master Courses Catalog & Class Schedules
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

-- 1. Master Courses Catalog (11 Courses)
INSERT INTO `classes` (`id`, `subject_code`, `subject_name`, `default_teacher`, `default_room`, `credit`)
VALUES
  (1, '30204-2001', 'การเขียนโปรแกรมเชิงวัตถุบนเว็บ', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 3),
  (2, '30204-2002', 'ระบบจัดการฐานข้อมูลดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 3),
  (3, '30204-2003', 'การตลาดดิจิทัลและการวิเคราะห์ข้อมูล', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 3),
  (4, '30204-2004', 'การออกแบบกราฟิกและสื่อดิจิทัล', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 3),
  (5, '30204-2005', 'การพาณิชย์อิเล็กทรอนิกส์บนคลาวด์', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 3),
  (6, '30204-2006', 'ความมั่นคงปลอดภัยบนเครือข่ายดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 3),
  (7, '30204-2007', 'การพัฒนาแอปพลิเคชันบนอุปกรณ์พกพา', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 3),
  (8, '30204-2101', 'โครงงานธุรกิจดิจิทัล 1', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 301', 3),
  (9, '30204-2102', 'สัมมนาการประยุกต์ใช้ปัญญาประดิษฐ์', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 3),
  (10, '30000-1201', 'ภาษาอังกฤษเพื่อธุรกิจเทคโนโลยี', 'อ.ชนิกานต์ เลิศปรีชา', 'Room 305', 2),
  (11, '30000-2001', 'กิจกรรมชมรมวิชาชีพเทคโนโลยีดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Club Room 306', 1)
ON DUPLICATE KEY UPDATE
  `subject_name` = VALUES(`subject_name`),
  `default_teacher` = VALUES(`default_teacher`),
  `default_room` = VALUES(`default_room`),
  `credit` = VALUES(`credit`);

-- 2. Class Schedule Slots (Linked to class_id)
INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 1, 1, '08:30:00', '10:30:00', '30204-2001', 'การเขียนโปรแกรมเชิงวัตถุบนเว็บ', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 'ปวช.2/1'
WHERE NOT EXISTS (SELECT 1 FROM `class_schedule` LIMIT 1);

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 2, 1, '10:30:00', '12:30:00', '30204-2002', 'ระบบจัดการฐานข้อมูลดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 1;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 8, 1, '13:00:00', '16:00:00', '30204-2101', 'โครงงานธุรกิจดิจิทัล 1', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 301', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 2;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 3, 2, '08:30:00', '11:30:00', '30204-2003', 'การตลาดดิจิทัลและการวิเคราะห์ข้อมูล', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 3;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 10, 2, '13:00:00', '15:00:00', '30000-1201', 'ภาษาอังกฤษเพื่อธุรกิจเทคโนโลยี', 'อ.ชนิกานต์ เลิศปรีชา', 'Room 305', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 4;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 4, 3, '08:30:00', '12:00:00', '30204-2004', 'การออกแบบกราฟิกและสื่อดิจิทัล', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 5;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 5, 3, '13:00:00', '16:00:00', '30204-2005', 'การพาณิชย์อิเล็กทรอนิกส์บนคลาวด์', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 6;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 6, 4, '08:30:00', '11:30:00', '30204-2006', 'ความมั่นคงปลอดภัยบนเครือข่ายดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 7;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 9, 4, '13:00:00', '15:00:00', '30204-2102', 'สัมมนาการประยุกต์ใช้ปัญญาประดิษฐ์', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 8;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 7, 5, '08:30:00', '11:30:00', '30204-2007', 'การพัฒนาแอปพลิเคชันบนอุปกรณ์พกพา', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 9;

INSERT INTO `class_schedule` (`class_id`, `day_of_week`, `time_start`, `time_end`, `subject_code`, `subject_name`, `teacher_name`, `room`, `class_group`)
SELECT 11, 5, '13:00:00', '15:00:00', '30000-2001', 'กิจกรรมชมรมวิชาชีพเทคโนโลยีดิจิทัล', 'อ.ธีรภัทร์ รัตนโชติ', 'Club Room 306', 'ปวช.2/1'
WHERE (SELECT COUNT(*) FROM `class_schedule`) = 10;
