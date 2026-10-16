-- Master Seeds File for DBT Web (เทคโนโลยีธุรกิจดิจิทัล)
-- Idempotent initial seed datasets for all modules

USE `dbt_web`;

-- 1. Users
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`)
VALUES (1, 'admin', '$2y$10$4Fj6cR3juFRqnPpc7bRe5uDrYrhTDK6NY/VK2ryY928msB.isyIu6', 'ผู้ดูแลระบบสาขาวิชา', 'admin')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- 2. Settings
INSERT INTO `settings` (`skey`, `svalue`)
VALUES
  ('hero_title_1', 'เทคโนโลยี'),
  ('hero_title_2', 'ธุรกิจดิจิทัล'),
  ('hero_intro', 'หลักสูตรที่เชื่อมโยงทักษะการพัฒนาระบบซอฟต์แวร์ การจัดการข้อมูล และการสร้างสรรค์โมเดลธุรกิจยุคใหม่ สู่การทำงานจริงในอุตสาหกรรมดิจิทัล'),
  ('hero_image', 'uploads/seed/hero-lab.jpg'),
  ('hero_caption', 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์'),
  ('stat_employment', '96%'),
  ('curriculum_code', 'DBT-2567-V02'),
  ('contact_phone', '0918390785'),
  ('contact_email', 'danucha@bpcc.ac.th'),
  ('contact_facebook_name', 'แผนกเทคโนโลยีธุรกิจดิจิทัล'),
  ('contact_facebook_url', 'https://www.facebook.com/profile.php?id=61569347616102'),
  ('contact_address', 'อาคารวิทยบริการเฉลิมพระเกียรติ ชั้น 3 ห้อง 301–305'),
  ('contact_hours', 'จันทร์–ศุกร์ 08:00–16:30 น.')
ON DUPLICATE KEY UPDATE `skey` = `skey`;

-- 3. News Categories & Articles
INSERT INTO `news_categories` (`id`, `name`, `slug`)
VALUES
  (1, 'ข่าวสารทั่วไป', 'general'),
  (2, 'วิชาการและอบรม', 'academic'),
  (3, 'กิจกรรมแผนก', 'activities'),
  (4, 'ผลงานนักศึกษา', 'showcase')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `news` (`category_id`, `title`, `summary`, `content`, `image_path`, `is_featured`, `published_at`)
SELECT 2, 'เปิดรับสมัครอบรมเชิงปฏิบัติการ Full-Stack Web Development ด้วย PHP 8 และ React', 'แผนกเทคโนโลยีธุรกิจดิจิทัลจัดอบรมเข้มข้น 30 ชั่วโมง พัฒนาทักษะการสร้างเว็บแอปพลิเคชันเชิงธุรกิจยุคใหม่สำหรับนักศึกษา ปวช. และ ปวส.', '<p>แผนกเทคโนโลยีธุรกิจดิจิทัลร่วมกับเครือข่ายสถานประกอบการด้านดิจิทัล จัดโครงการอบรมเชิงปฏิบัติการพิเศษเพื่อเสริมสร้างสมรรถนะนักศึกษาในด้านการพัฒนาเว็บแอปพลิเคชันสมัยใหม่</p><p>ผู้เข้าร่วมอบรมจะได้เรียนรู้กระบวนการพัฒนาตั้งแต่การออกแบบฐานข้อมูลเชิงสัมพันธ์, การเขียน RESTful API ด้วย PHP 8 และสถาปัตยกรรม MVC ตลอดจนการทำ Modern Frontend ด้วย React และ Bootstrap Framework</p><p>เปิดรับสมัครตั้งแต่วันนี้ถึงวันที่ 25 ตุลาคม ณ ห้องปฏิบัติการคอมพิวเตอร์ 303</p>', 'uploads/seed/news-coding.jpg', 1, CURDATE()
WHERE NOT EXISTS (SELECT 1 FROM `news` LIMIT 1);

INSERT INTO `news` (`category_id`, `title`, `summary`, `content`, `image_path`, `is_featured`, `published_at`)
SELECT 4, 'นักศึกษาแผนก DBT คว้า 2 รางวัลการแข่งขันทักษะการพัฒนาซอฟต์แวร์ระดับภาค', 'ขอแสดงความยินดีกับตัวแทนนักศึกษาที่คว้ารางวัลชนะเลิศและรองชนะเลิศอันดับหนึ่ง ในการประกวดสิ่งประดิษฐ์และซอฟต์แวร์ธุรกิจดิจิทัล', '<p>ในการแข่งขันทักษะวิชาชีพและทักษะพื้นฐานระดับภาค ประจำปีการศึกษา 2567 ทีมตัวแทนนักศึกษาจากแผนกเทคโนโลยีธุรกิจดิจิทัลได้นำเสนอผลงานระบบจัดการสต็อกสินค้าอัจฉริยะสำหรับวิสาหกิจชุมชน และสามารถคว้ารางวัลชนะเลิศอันดับ 1 มาครองได้อย่างน่าภาคภูมิใจ</p><p>ผลงานดังกล่าวได้รับการพัฒนาบนเทคโนโลยีคลาวด์และพร้อมนำไปทดลองใช้งานจริงในวิสาหกิจชุมชนต้นแบบในไตรมาสถัดไป</p>', 'uploads/seed/news-award.jpg', 0, DATE_SUB(CURDATE(), INTERVAL 3 DAY)
WHERE (SELECT COUNT(*) FROM `news`) = 1;

INSERT INTO `news` (`category_id`, `title`, `summary`, `content`, `image_path`, `is_featured`, `published_at`)
SELECT 3, 'ประมวลภาพกิจกรรมปฐมนิเทศและ Workshop เตรียมความพร้อมสู่โลกธุรกิจดิจิทัล', 'กิจกรรมต้อนรับนักศึกษาใหม่ประจำภาคเรียน สร้างแรงบันดาลใจและแนะนำแนวทางการต่อยอดสู่สายอาชีพด้านไอที', '<p>บรรยากาศเต็มไปด้วยความอบอุ่นและความกระตือรือร้น ในกิจกรรมปฐมนิเทศนักศึกษาใหม่และเวิร์กช็อปเตรียมความพร้อม</p><p>โดยได้รับเกียรติจากศิษย์เก่าที่กำลังทำงานในตำแหน่ง Data Analyst และ Frontend Developer ในบริษัทเทคโนโลยีชั้นนำ มาร่วมแบ่งปันประสบการณ์และเทคนิคการฝึกฝนตนเองในรั้วสถานศึกษา</p>', 'uploads/seed/news-orientation.jpg', 0, DATE_SUB(CURDATE(), INTERVAL 7 DAY)
WHERE (SELECT COUNT(*) FROM `news`) = 2;

-- 4. Classes Master Catalog & Schedules
INSERT INTO `classes` (`id`, `subject_code`, `subject_name`, `course_type`, `default_teacher`, `default_room`, `credit`)
VALUES
  (1, '30204-2001', 'การเขียนโปรแกรมเชิงวัตถุบนเว็บ', 'professional', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 3),
  (2, '30204-2002', 'ระบบจัดการฐานข้อมูลดิจิทัล', 'professional', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 3),
  (3, '30204-2003', 'การตลาดดิจิทัลและการวิเคราะห์ข้อมูล', 'professional', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 3),
  (4, '30204-2004', 'การออกแบบกราฟิกและสื่อดิจิทัล', 'professional', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 3),
  (5, '30204-2005', 'การพาณิชย์อิเล็กทรอนิกส์บนคลาวด์', 'professional', 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'Lab 301', 3),
  (6, '30204-2006', 'ความมั่นคงปลอดภัยบนเครือข่ายดิจิทัล', 'professional', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 302', 3),
  (7, '30204-2007', 'การพัฒนาแอปพลิเคชันบนอุปกรณ์พกพา', 'professional', 'อ.วรวิทย์ สุวรรณเวช', 'Lab 303', 3),
  (8, '30204-2101', 'โครงงานธุรกิจดิจิทัล 1', 'professional', 'อ.ธีรภัทร์ รัตนโชติ', 'Lab 301', 3),
  (9, '30204-2102', 'สัมมนาการประยุกต์ใช้ปัญญาประดิษฐ์', 'professional', 'อ.กัญญารัตน์ วัฒนเสถียร', 'Room 304', 3),
  (10, '30000-1201', 'ภาษาอังกฤษเพื่อธุรกิจเทคโนโลยี', 'regular', 'อ.ชนิกานต์ เลิศปรีชา', 'Room 305', 2),
  (11, '30000-2001', 'กิจกรรมชมรมวิชาชีพเทคโนโลยีดิจิทัล', 'regular', 'อ.ธีรภัทร์ รัตนโชติ', 'Club Room 306', 1)
ON DUPLICATE KEY UPDATE
  `subject_name` = VALUES(`subject_name`),
  `course_type` = VALUES(`course_type`),
  `default_teacher` = VALUES(`default_teacher`),
  `default_room` = VALUES(`default_room`),
  `credit` = VALUES(`credit`);

-- 5. Activities
INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT 'DBT Hackathon: นวัตกรรมดิจิทัลเพื่อชุมชน', 'การแข่งขันระดมไอเดียและสร้างโปรโตไทป์แอปพลิเคชันแก้ปัญหาชุมชนในเวลา 24 ชั่วโมง', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '09:00:00', '17:00:00', 'หอประชุมใหญ่ อาคาร 1', 'SOON'
WHERE NOT EXISTS (SELECT 1 FROM `activities` LIMIT 1);

INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT 'งานเปิดบ้านวิชาการ DBT Open House', 'นิทรรศการแสดงผลงานนักศึกษา โครงงานดิจิทัล และแนะแนวการศึกษาต่อระดับประกาศนียบัตรวิชาชีพ', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '08:30:00', '16:00:00', 'อาคารวิทยบริการ ชั้น 3', 'UPCOMING'
WHERE (SELECT COUNT(*) FROM `activities`) = 1;

INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT 'เสวนาพิเศษ: แนวโน้ม AI ในงานธุรกิจและไอที', 'พบกับวิทยากรผู้เชี่ยวชาญจากบริษัทเทคโนโลยีชั้นนำ แลกเปลี่ยนมุมมองความพร้อมในการทำงาน', DATE_ADD(CURDATE(), INTERVAL 22 DAY), '13:00:00', '15:30:00', 'ห้องประชุมเอนกประสงค์ 305', 'REGISTRATION'
WHERE (SELECT COUNT(*) FROM `activities`) = 2;

-- 6. Faculty
INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT 'ดร.ศิริพร บุญเสริมประสิทธิ์', 'หัวหน้าสาขาวิชาเทคโนโลยีธุรกิจดิจิทัล', 'Enterprise Software, Cloud Computing & Web Architectures', 'siriporn.b@vocational.ac.th', 'uploads/seed/teacher-siriporn.jpg', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `teachers` LIMIT 1);

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT 'อาจารย์ธีรภัทร์ รัตนโชติ', 'ครูประจำสาขาวิชา / หัวหน้างานโครงงาน', 'Database Systems, Data Analytics & Network Infrastructure', 'teerapat.r@vocational.ac.th', 'uploads/seed/teacher-teerapat.jpg', 0, 2
WHERE (SELECT COUNT(*) FROM `teachers`) = 1;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT 'อาจารย์กัญญารัตน์ วัฒนเสถียร', 'ครูประจำสาขาวิชา / หัวหน้างานหลักสูตร', 'Digital Marketing, E-Commerce & Business Intelligence', 'kanyarat.w@vocational.ac.th', 'uploads/seed/teacher-kanyarat.jpg', 0, 3
WHERE (SELECT COUNT(*) FROM `teachers`) = 2;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT 'อาจารย์วรวิทย์ สุวรรณเวช', 'ครูประจำสาขาวิชา / ครูที่ปรึกษาชมรม', 'Mobile App Development, UI/UX Design & Front-End', 'worawit.s@vocational.ac.th', 'uploads/seed/teacher-worawit.jpg', 0, 4
WHERE (SELECT COUNT(*) FROM `teachers`) = 3;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT 'อาจารย์ชนิกานต์ เลิศปรีชา', 'ครูประจำสาขาวิชา / งานกิจกรรมนักศึกษา', 'Digital Business Communication & Professional Media', 'chanikan.l@vocational.ac.th', 'uploads/seed/teacher-chanikan.jpg', 0, 5
WHERE (SELECT COUNT(*) FROM `teachers`) = 4;

-- 7. Club Members
INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '66209010001', 'นายกิตติภูมิ ทวีโชคประเสริฐ', 'ประธานชมรมเทคโนโลยีธุรกิจดิจิทัล', 1, 'uploads/seed/club-kittiphum.jpg', 1
WHERE NOT EXISTS (SELECT 1 FROM `club_members` LIMIT 1);

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '66209010014', 'นางสาวพิมพ์ลดา ศรีสุวรรณ', 'รองประธานชมรมฝ่ายวิชาการ', 0, 'uploads/seed/club-pimlada.jpg', 2
WHERE (SELECT COUNT(*) FROM `club_members`) = 1;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '66209010022', 'นายณัฐวัตร เมธาอัครกุล', 'หัวหน้าฝ่ายพัฒนาเทคโนโลยีและซอฟต์แวร์', 0, 'uploads/seed/club-nattawat.jpg', 3
WHERE (SELECT COUNT(*) FROM `club_members`) = 2;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '66209010035', 'นางสาวปัณฑิตา เจริญผล', 'หัวหน้าฝ่ายสื่อและประชาสัมพันธ์ดิจิทัล', 0, 'uploads/seed/club-pantita.jpg', 4
WHERE (SELECT COUNT(*) FROM `club_members`) = 3;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '66209010048', 'นายวริทธิ์ธร พงษ์ศิริ', 'หัวหน้าฝ่ายกิจกรรมและประสานงาน', 0, 'uploads/seed/club-waritthorn.jpg', 5
WHERE (SELECT COUNT(*) FROM `club_members`) = 4;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT '67209010012', 'นางสาวชลธิชา สิทธิรักษ์', 'เหรัญญิกและงานทะเบียนชมรม', 0, 'uploads/seed/club-chonticha.jpg', 6
WHERE (SELECT COUNT(*) FROM `club_members`) = 5;
