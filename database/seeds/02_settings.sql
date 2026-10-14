-- Seed 02: Default Site & Department Settings
-- DBT Web (เทคโนโลยีธุรกิจดิจิทัล)

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
