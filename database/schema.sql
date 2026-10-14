-- Database Schema for DBT Web (เทคโนโลยีธุรกิจดิจิทัล)
-- Idempotent Migration: Safe to run anytime, will NOT drop tables or delete existing data.
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
CREATE DATABASE IF NOT EXISTS `dbt_web` CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `dbt_web`;

-- 1. Users Table
CREATE TABLE
  IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 2. Settings Table (Key/Value)
CREATE TABLE
  IF NOT EXISTS `settings` (
    `skey` VARCHAR(50) PRIMARY KEY,
    `svalue` TEXT NOT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 3. News Categories Table
CREATE TABLE
  IF NOT EXISTS `news_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4. News Articles Table
CREATE TABLE
  IF NOT EXISTS `news` (
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

-- 5. Class Schedule Table
CREATE TABLE
  IF NOT EXISTS `class_schedule` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `day_of_week` TINYINT NOT NULL COMMENT '1=จันทร์, 2=อังคาร, 3=พุธ, 4=พฤหัสบดี, 5=ศุกร์',
    `time_start` TIME NOT NULL,
    `time_end` TIME NOT NULL,
    `subject_code` VARCHAR(20) NOT NULL,
    `subject_name` VARCHAR(100) NOT NULL,
    `teacher_name` VARCHAR(100) NOT NULL,
    `room` VARCHAR(50) NOT NULL,
    `class_group` VARCHAR(50) NOT NULL DEFAULT 'ปวช.2/1',
    INDEX `idx_sched_group_day` (`class_group`, `day_of_week`, `time_start`),
    INDEX `idx_sched_day` (`day_of_week`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 6. Activities Table
CREATE TABLE
  IF NOT EXISTS `activities` (
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

-- 7. Teachers Table
CREATE TABLE
  IF NOT EXISTS `teachers` (
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

-- 8. Club Members Table
CREATE TABLE
  IF NOT EXISTS `club_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(20) NULL,
    `name` VARCHAR(100) NOT NULL,
    `club_role` VARCHAR(100) NOT NULL,
    `is_club_head` TINYINT (1) NOT NULL DEFAULT 0,
    `image_path` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    INDEX `idx_club_order` (`is_club_head` DESC, `sort_order` ASC, `id` ASC)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA (Idempotent: Skip if already exists)
-- ============================================================================
-- Default Admin User: admin / admin1234
INSERT INTO
  `users` (
    `id`,
    `username`,
    `password_hash`,
    `full_name`,
    `role`
  )
VALUES
  (
    1,
    'admin',
    '$2y$10$4Fj6cR3juFRqnPpc7bRe5uDrYrhTDK6NY/VK2ryY928msB.isyIu6',
    'ผู้ดูแลระบบแผนกวิชา',
    'admin'
  ) ON DUPLICATE KEY
UPDATE `username` = `username`;

-- News Categories
INSERT INTO
  `news_categories` (`id`, `name`, `slug`)
VALUES
  (1, 'ข่าวสารทั่วไป', 'general'),
  (2, 'วิชาการและอบรม', 'academic'),
  (3, 'กิจกรรมแผนก', 'activities'),
  (4, 'ผลงานนักศึกษา', 'showcase') ON DUPLICATE KEY
UPDATE `name` =
VALUES
  (`name`);

-- Default Settings (Preserves existing customized settings)
INSERT INTO
  `settings` (`skey`, `svalue`)
VALUES
  ('hero_title_1', 'เทคโนโลยี'),
  ('hero_title_2', 'ธุรกิจดิจิทัล'),
  (
    'hero_intro',
    'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล'
  ),
  ('hero_image', 'uploads/seed/hero-lab.jpg'),
  (
    'hero_caption',
    'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์'
  ),
  ('stat_courses', '18'),
  ('stat_labs', '04'),
  ('stat_employment', '96%'),
  ('stat_mode', 'dynamic'),
  ('curriculum_code', 'DBT-2567-V02'),
  ('contact_phone', '0918390785'),
  ('contact_email', 'danucha@bpcc.ac.th'),
  (
    'contact_facebook_name',
    'แผนกเทคโนโลยีธุรกิจดิจิทัล'
  ),
  (
    'contact_facebook_url',
    'https://www.facebook.com/profile.php?id=61569347616102'
  ),
  (
    'contact_address',
    'อาคารวิทยบริการเฉลิมพระเกียรติ ชั้น 3 ห้อง 301–305'
  ),
  ('contact_hours', 'จันทร์–ศุกร์ 08:00–16:30 น.') ON DUPLICATE KEY
UPDATE `skey` = `skey`;

-- News Seed (Only insert if news table is completely empty)
INSERT INTO
  `news` (
    `category_id`,
    `title`,
    `summary`,
    `content`,
    `image_path`,
    `is_featured`,
    `published_at`
  )
SELECT
  2,
  'เปิดรับสมัครอบรมเชิงปฏิบัติการ Full-Stack Web Development ด้วย PHP 8 และ React',
  'แผนกเทคโนโลยีธุรกิจดิจิทัลจัดอบรมเข้มข้น 30 ชั่วโมง พัฒนาทักษะการสร้างเว็บแอปพลิเคชันเชิงธุรกิจยุคใหม่สำหรับนักศึกษา ปวช. และ ปวส.',
  '<p>แผนกเทคโนโลยีธุรกิจดิจิทัลร่วมกับเครือข่ายสถานประกอบการด้านดิจิทัล จัดโครงการอบรมเชิงปฏิบัติการพิเศษเพื่อเสริมสร้างสมรรถนะนักศึกษาในด้านการพัฒนาเว็บแอปพลิเคชันสมัยใหม่</p><p>ผู้เข้าร่วมอบรมจะได้เรียนรู้กระบวนการพัฒนาตั้งแต่การออกแบบฐานข้อมูลเชิงสัมพันธ์, การเขียน RESTful API ด้วย PHP 8 และสถาปัตยกรรม MVC ตลอดจนการทำ Modern Frontend ด้วย React และ Bootstrap Framework</p><p>เปิดรับสมัครตั้งแต่วันนี้ถึงวันที่ 25 ตุลาคม ณ ห้องปฏิบัติการคอมพิวเตอร์ 303</p>',
  'uploads/seed/news-coding.jpg',
  1,
  CURDATE ()
WHERE
  NOT EXISTS (
    SELECT
      1
    FROM
      `news`
    LIMIT
      1
  );

INSERT INTO
  `news` (
    `category_id`,
    `title`,
    `summary`,
    `content`,
    `image_path`,
    `is_featured`,
    `published_at`
  )
SELECT
  4,
  'นักศึกษาแผนก DBT คว้า 2 รางวัลการแข่งขันทักษะการพัฒนาซอฟต์แวร์ระดับภาค',
  'ขอแสดงความยินดีกับตัวแทนนักศึกษาที่คว้ารางวัลชนะเลิศและรองชนะเลิศอันดับหนึ่ง ในการประกวดสิ่งประดิษฐ์และซอฟต์แวร์ธุรกิจดิจิทัล',
  '<p>ในการแข่งขันทักษะวิชาชีพและทักษะพื้นฐานระดับภาค ประจำปีการศึกษา 2567 ทีมตัวแทนนักศึกษาจากแผนกเทคโนโลยีธุรกิจดิจิทัลได้นำเสนอผลงานระบบจัดการสต็อกสินค้าอัจฉริยะสำหรับวิสาหกิจชุมชน และสามารถคว้ารางวัลชนะเลิศอันดับ 1 มาครองได้อย่างน่าภาคภูมิใจ</p><p>ผลงานดังกล่าวได้รับการพัฒนาบนเทคโนโลยีคลาวด์และพร้อมนำไปทดลองใช้งานจริงในวิสาหกิจชุมชนต้นแบบในไตรมาสถัดไป</p>',
  'uploads/seed/news-award.jpg',
  0,
  DATE_SUB (CURDATE (), INTERVAL 3 DAY)
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `news`
  ) = 1;

INSERT INTO
  `news` (
    `category_id`,
    `title`,
    `summary`,
    `content`,
    `image_path`,
    `is_featured`,
    `published_at`
  )
SELECT
  3,
  'ประมวลภาพกิจกรรมปฐมนิเทศและ Workshop เตรียมความพร้อมสู่โลกธุรกิจดิจิทัล',
  'กิจกรรมต้อนรับนักศึกษาใหม่ประจำภาคเรียน สร้างแรงบันดาลใจและแนะนำแนวทางการต่อยอดสู่สายอาชีพด้านไอที',
  '<p>บรรยากาศเต็มไปด้วยความอบอุ่นและความกระตือรือร้น ในกิจกรรมปฐมนิเทศนักศึกษาใหม่และเวิร์กช็อปเตรียมความพร้อม</p><p>โดยได้รับเกียรติจากศิษย์เก่าที่กำลังทำงานในตำแหน่ง Data Analyst และ Frontend Developer ในบริษัทเทคโนโลยีชั้นนำ มาร่วมแบ่งปันประสบการณ์และเทคนิคการฝึกฝนตนเองในรั้วสถานศึกษา</p>',
  'uploads/seed/news-orientation.jpg',
  0,
  DATE_SUB (CURDATE (), INTERVAL 7 DAY)
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `news`
  ) = 2;

-- Class Schedule Seed (Only insert if class_schedule table is completely empty)
INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  1,
  '08:30:00',
  '10:30:00',
  '30204-2001',
  'การเขียนโปรแกรมเชิงวัตถุบนเว็บ',
  'ดร.ศิริพร บุญเสริมประสิทธิ์',
  'Lab 301',
  'ปวช.2/1'
WHERE
  NOT EXISTS (
    SELECT
      1
    FROM
      `class_schedule`
    LIMIT
      1
  );

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  1,
  '10:30:00',
  '12:30:00',
  '30204-2002',
  'ระบบจัดการฐานข้อมูลดิจิทัล',
  'อ.ธีรภัทร์ รัตนโชติ',
  'Lab 302',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 1;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  1,
  '13:00:00',
  '16:00:00',
  '30204-2101',
  'โครงงานธุรกิจดิจิทัล 1',
  'อ.ธีรภัทร์ รัตนโชติ',
  'Lab 301',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 2;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  2,
  '08:30:00',
  '11:30:00',
  '30204-2003',
  'การตลาดดิจิทัลและการวิเคราะห์ข้อมูล',
  'อ.กัญญารัตน์ วัฒนเสถียร',
  'Room 304',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 3;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  2,
  '13:00:00',
  '15:00:00',
  '30000-1201',
  'ภาษาอังกฤษเพื่อธุรกิจเทคโนโลยี',
  'อ.ชนิกานต์ เลิศปรีชา',
  'Room 305',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 4;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  3,
  '08:30:00',
  '12:00:00',
  '30204-2004',
  'การออกแบบกราฟิกและสื่อดิจิทัล',
  'อ.วรวิทย์ สุวรรณเวช',
  'Lab 303',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 5;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  3,
  '13:00:00',
  '16:00:00',
  '30204-2005',
  'การพาณิชย์อิเล็กทรอนิกส์บนคลาวด์',
  'ดร.ศิริพร บุญเสริมประสิทธิ์',
  'Lab 301',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 6;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  4,
  '08:30:00',
  '11:30:00',
  '30204-2006',
  'ความมั่นคงปลอดภัยบนเครือข่ายดิจิทัล',
  'อ.ธีรภัทร์ รัตนโชติ',
  'Lab 302',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 7;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  4,
  '13:00:00',
  '15:00:00',
  '30204-2102',
  'สัมมนาการประยุกต์ใช้ปัญญาประดิษฐ์',
  'อ.กัญญารัตน์ วัฒนเสถียร',
  'Room 304',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 8;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  5,
  '08:30:00',
  '11:30:00',
  '30204-2007',
  'การพัฒนาแอปพลิเคชันบนอุปกรณ์พกพา',
  'อ.วรวิทย์ สุวรรณเวช',
  'Lab 303',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 9;

INSERT INTO
  `class_schedule` (
    `day_of_week`,
    `time_start`,
    `time_end`,
    `subject_code`,
    `subject_name`,
    `teacher_name`,
    `room`,
    `class_group`
  )
SELECT
  5,
  '13:00:00',
  '15:00:00',
  '30000-2001',
  'กิจกรรมชมรมวิชาชีพเทคโนโลยีดิจิทัล',
  'อ.ธีรภัทร์ รัตนโชติ',
  'Club Room 306',
  'ปวช.2/1'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `class_schedule`
  ) = 10;

-- Activities Seed (Only insert if activities table is empty)
INSERT INTO
  `activities` (
    `title`,
    `description`,
    `event_date`,
    `time_start`,
    `time_end`,
    `location`,
    `badge_text`
  )
SELECT
  'DBT Hackathon: นวัตกรรมดิจิทัลเพื่อชุมชน',
  'การแข่งขันระดมไอเดียและสร้างโปรโตไทป์แอปพลิเคชันแก้ปัญหาชุมชนในเวลา 24 ชั่วโมง',
  DATE_ADD (CURDATE (), INTERVAL 5 DAY),
  '09:00:00',
  '17:00:00',
  'หอประชุมใหญ่ อาคาร 1',
  'SOON'
WHERE
  NOT EXISTS (
    SELECT
      1
    FROM
      `activities`
    LIMIT
      1
  );

INSERT INTO
  `activities` (
    `title`,
    `description`,
    `event_date`,
    `time_start`,
    `time_end`,
    `location`,
    `badge_text`
  )
SELECT
  'งานเปิดบ้านวิชาการ DBT Open House',
  'นิทรรศการแสดงผลงานนักศึกษา โครงงานดิจิทัล และแนะแนวการศึกษาต่อระดับประกาศนียบัตรวิชาชีพ',
  DATE_ADD (CURDATE (), INTERVAL 14 DAY),
  '08:30:00',
  '16:00:00',
  'อาคารวิทยบริการ ชั้น 3',
  'UPCOMING'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `activities`
  ) = 1;

INSERT INTO
  `activities` (
    `title`,
    `description`,
    `event_date`,
    `time_start`,
    `time_end`,
    `location`,
    `badge_text`
  )
SELECT
  'เสวนาพิเศษ: แนวโน้ม AI ในงานธุรกิจและไอที',
  'พบกับวิทยากรผู้เชี่ยวชาญจากบริษัทเทคโนโลยีชั้นนำ แลกเปลี่ยนมุมมองความพร้อมในการทำงาน',
  DATE_ADD (CURDATE (), INTERVAL 22 DAY),
  '13:00:00',
  '15:30:00',
  'ห้องประชุมเอนกประสงค์ 305',
  'REGISTRATION'
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `activities`
  ) = 2;

-- Teachers Seed (Only insert if teachers table is empty)
INSERT INTO
  `teachers` (
    `name`,
    `position`,
    `specialization`,
    `email`,
    `image_path`,
    `is_head`,
    `sort_order`
  )
SELECT
  'ดร.ศิริพร บุญเสริมประสิทธิ์',
  'หัวหน้าแผนกวิชาเทคโนโลยีธุรกิจดิจิทัล',
  'Enterprise Software, Cloud Computing & Web Architectures',
  'siriporn.b@vocational.ac.th',
  'uploads/seed/teacher-siriporn.jpg',
  1,
  1
WHERE
  NOT EXISTS (
    SELECT
      1
    FROM
      `teachers`
    LIMIT
      1
  );

INSERT INTO
  `teachers` (
    `name`,
    `position`,
    `specialization`,
    `email`,
    `image_path`,
    `is_head`,
    `sort_order`
  )
SELECT
  'อาจารย์ธีรภัทร์ รัตนโชติ',
  'ครูประจำแผนกวิชา / หัวหน้างานโครงงาน',
  'Database Systems, Data Analytics & Network Infrastructure',
  'teerapat.r@vocational.ac.th',
  'uploads/seed/teacher-teerapat.jpg',
  0,
  2
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `teachers`
  ) = 1;

INSERT INTO
  `teachers` (
    `name`,
    `position`,
    `specialization`,
    `email`,
    `image_path`,
    `is_head`,
    `sort_order`
  )
SELECT
  'อาจารย์กัญญารัตน์ วัฒนเสถียร',
  'ครูประจำแผนกวิชา / หัวหน้างานหลักสูตร',
  'Digital Marketing, E-Commerce & Business Intelligence',
  'kanyarat.w@vocational.ac.th',
  'uploads/seed/teacher-kanyarat.jpg',
  0,
  3
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `teachers`
  ) = 2;

INSERT INTO
  `teachers` (
    `name`,
    `position`,
    `specialization`,
    `email`,
    `image_path`,
    `is_head`,
    `sort_order`
  )
SELECT
  'อาจารย์วรวิทย์ สุวรรณเวช',
  'ครูประจำแผนกวิชา / ครูที่ปรึกษาชมรม',
  'Mobile App Development, UI/UX Design & Front-End',
  'worawit.s@vocational.ac.th',
  'uploads/seed/teacher-worawit.jpg',
  0,
  4
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `teachers`
  ) = 3;

INSERT INTO
  `teachers` (
    `name`,
    `position`,
    `specialization`,
    `email`,
    `image_path`,
    `is_head`,
    `sort_order`
  )
SELECT
  'อาจารย์ชนิกานต์ เลิศปรีชา',
  'ครูประจำแผนกวิชา / งานกิจกรรมนักศึกษา',
  'Digital Business Communication & Professional Media',
  'chanikan.l@vocational.ac.th',
  'uploads/seed/teacher-chanikan.jpg',
  0,
  5
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `teachers`
  ) = 4;

-- Club Members Seed (Only insert if club_members table is empty)
INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '66209010001',
  'นายกิตติภูมิ ทวีโชคประเสริฐ',
  'ประธานชมรมเทคโนโลยีธุรกิจดิจิทัล',
  1,
  'uploads/seed/club-kittiphum.jpg',
  1
WHERE
  NOT EXISTS (
    SELECT
      1
    FROM
      `club_members`
    LIMIT
      1
  );

INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '66209010014',
  'นางสาวพิมพ์ลดา ศรีสุวรรณ',
  'รองประธานชมรมฝ่ายวิชาการ',
  0,
  'uploads/seed/club-pimlada.jpg',
  2
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `club_members`
  ) = 1;

INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '66209010022',
  'นายณัฐวัตร เมธาอัครกุล',
  'หัวหน้าฝ่ายพัฒนาเทคโนโลยีและซอฟต์แวร์',
  0,
  'uploads/seed/club-nattawat.jpg',
  3
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `club_members`
  ) = 2;

INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '66209010035',
  'นางสาวปัณฑิตา เจริญผล',
  'หัวหน้าฝ่ายสื่อและประชาสัมพันธ์ดิจิทัล',
  0,
  'uploads/seed/club-pantita.jpg',
  4
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `club_members`
  ) = 3;

INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '66209010048',
  'นายวริทธิ์ธร พงษ์ศิริ',
  'หัวหน้าฝ่ายกิจกรรมและประสานงาน',
  0,
  'uploads/seed/club-waritthorn.jpg',
  5
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `club_members`
  ) = 4;

INSERT INTO
  `club_members` (
    `student_id`,
    `name`,
    `club_role`,
    `is_club_head`,
    `image_path`,
    `sort_order`
  )
SELECT
  '67209010012',
  'นางสาวชลธิชา สิทธิรักษ์',
  'เหรัญญิกและงานทะเบียนชมรม',
  0,
  'uploads/seed/club-chonticha.jpg',
  6
WHERE
  (
    SELECT
      COUNT(*)
    FROM
      `club_members`
  ) = 5;