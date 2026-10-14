-- Seed 05: Activities & Events
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT
  'DBT Hackathon: นวัตกรรมดิจิทัลเพื่อชุมชน',
  'การแข่งขันระดมไอเดียและสร้างโปรโตไทป์แอปพลิเคชันแก้ปัญหาชุมชนในเวลา 24 ชั่วโมง',
  DATE_ADD(CURDATE(), INTERVAL 5 DAY),
  '09:00:00',
  '17:00:00',
  'หอประชุมใหญ่ อาคาร 1',
  'SOON'
WHERE NOT EXISTS (SELECT 1 FROM `activities` LIMIT 1);

INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT
  'งานเปิดบ้านวิชาการ DBT Open House',
  'นิทรรศการแสดงผลงานนักศึกษา โครงงานดิจิทัล และแนะแนวการศึกษาต่อระดับประกาศนียบัตรวิชาชีพ',
  DATE_ADD(CURDATE(), INTERVAL 14 DAY),
  '08:30:00',
  '16:00:00',
  'อาคารวิทยบริการ ชั้น 3',
  'UPCOMING'
WHERE (SELECT COUNT(*) FROM `activities`) = 1;

INSERT INTO `activities` (`title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `badge_text`)
SELECT
  'เสวนาพิเศษ: แนวโน้ม AI ในงานธุรกิจและไอที',
  'พบกับวิทยากรผู้เชี่ยวชาญจากบริษัทเทคโนโลยีชั้นนำ แลกเปลี่ยนมุมมองความพร้อมในการทำงาน',
  DATE_ADD(CURDATE(), INTERVAL 22 DAY),
  '13:00:00',
  '15:30:00',
  'ห้องประชุมเอนกประสงค์ 305',
  'REGISTRATION'
WHERE (SELECT COUNT(*) FROM `activities`) = 2;
