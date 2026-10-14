-- Seed 06: Faculty Teachers & Student Club Members
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

-- Teachers Seed
INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT
  'ดร.ศิริพร บุญเสริมประสิทธิ์',
  'หัวหน้าแผนกวิชาเทคโนโลยีธุรกิจดิจิทัล',
  'Enterprise Software, Cloud Computing & Web Architectures',
  'siriporn.b@vocational.ac.th',
  'uploads/seed/teacher-siriporn.jpg',
  1,
  1
WHERE NOT EXISTS (SELECT 1 FROM `teachers` LIMIT 1);

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT
  'อาจารย์ธีรภัทร์ รัตนโชติ',
  'ครูประจำแผนกวิชา / หัวหน้างานโครงงาน',
  'Database Systems, Data Analytics & Network Infrastructure',
  'teerapat.r@vocational.ac.th',
  'uploads/seed/teacher-teerapat.jpg',
  0,
  2
WHERE (SELECT COUNT(*) FROM `teachers`) = 1;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT
  'อาจารย์กัญญารัตน์ วัฒนเสถียร',
  'ครูประจำแผนกวิชา / หัวหน้างานหลักสูตร',
  'Digital Marketing, E-Commerce & Business Intelligence',
  'kanyarat.w@vocational.ac.th',
  'uploads/seed/teacher-kanyarat.jpg',
  0,
  3
WHERE (SELECT COUNT(*) FROM `teachers`) = 2;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT
  'อาจารย์วรวิทย์ สุวรรณเวช',
  'ครูประจำแผนกวิชา / ครูที่ปรึกษาชมรม',
  'Mobile App Development, UI/UX Design & Front-End',
  'worawit.s@vocational.ac.th',
  'uploads/seed/teacher-worawit.jpg',
  0,
  4
WHERE (SELECT COUNT(*) FROM `teachers`) = 3;

INSERT INTO `teachers` (`name`, `position`, `specialization`, `email`, `image_path`, `is_head`, `sort_order`)
SELECT
  'อาจารย์ชนิกานต์ เลิศปรีชา',
  'ครูประจำแผนกวิชา / งานกิจกรรมนักศึกษา',
  'Digital Business Communication & Professional Media',
  'chanikan.l@vocational.ac.th',
  'uploads/seed/teacher-chanikan.jpg',
  0,
  5
WHERE (SELECT COUNT(*) FROM `teachers`) = 4;

-- Club Members Seed
INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '66209010001',
  'นายกิตติภูมิ ทวีโชคประเสริฐ',
  'ประธานชมรมเทคโนโลยีธุรกิจดิจิทัล',
  1,
  'uploads/seed/club-kittiphum.jpg',
  1
WHERE NOT EXISTS (SELECT 1 FROM `club_members` LIMIT 1);

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '66209010014',
  'นางสาวพิมพ์ลดา ศรีสุวรรณ',
  'รองประธานชมรมฝ่ายวิชาการ',
  0,
  'uploads/seed/club-pimlada.jpg',
  2
WHERE (SELECT COUNT(*) FROM `club_members`) = 1;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '66209010022',
  'นายณัฐวัตร เมธาอัครกุล',
  'หัวหน้าฝ่ายพัฒนาเทคโนโลยีและซอฟต์แวร์',
  0,
  'uploads/seed/club-nattawat.jpg',
  3
WHERE (SELECT COUNT(*) FROM `club_members`) = 2;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '66209010035',
  'นางสาวปัณฑิตา เจริญผล',
  'หัวหน้าฝ่ายสื่อและประชาสัมพันธ์ดิจิทัล',
  0,
  'uploads/seed/club-pantita.jpg',
  4
WHERE (SELECT COUNT(*) FROM `club_members`) = 3;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '66209010048',
  'นายวริทธิ์ธร พงษ์ศิริ',
  'หัวหน้าฝ่ายกิจกรรมและประสานงาน',
  0,
  'uploads/seed/club-waritthorn.jpg',
  5
WHERE (SELECT COUNT(*) FROM `club_members`) = 4;

INSERT INTO `club_members` (`student_id`, `name`, `club_role`, `is_club_head`, `image_path`, `sort_order`)
SELECT
  '67209010012',
  'นางสาวชลธิชา สิทธิรักษ์',
  'เหรัญญิกและงานทะเบียนชมรม',
  0,
  'uploads/seed/club-chonticha.jpg',
  6
WHERE (SELECT COUNT(*) FROM `club_members`) = 5;
