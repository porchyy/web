# เทคโนโลยีธุรกิจดิจิทัล (Digital Business Technology - DBT)

เว็บไซต์ทางการของสาขาวิชาเทคโนโลยีธุรกิจดิจิทัล ออกแบบภายใต้แนวคิด **Mobile-First** และปรัชญา **"Human-designed Digital Technology"** (Modern Technology + Contemporary Education + Editorial Graphic Design + Clean Interface) ปราศจากความซ้ำซากจำเจของ AI Template

---

## 1. คุณลักษณะงานออกแบบ (Design System)

- **Mobile-First Base**: พัฒนาและทดสอบบนขนาดหน้าจอมือถือจริง (360px, 390px, 430px) พร้อมขยายสู่ Tablet และ Desktop อย่างเป็นธรรมชาติ
- **Color Ratio**:
  - พื้นหลังหลักขาว/เทาอ่อน (`#FFFFFF`, `#F4F8FA`): 65%
  - ฟ้าอ่อน Light Blue (`#E6F1F5`, `#B9DCEB`): 20%
  - กรมท่า Deep Navy (`#17324D`): 10%
  - ส้ม Accent Warm Orange (`#E99A4A` / `#B5601F`): 5% สำหรับจุดเน้น
- **Typography**: แบบอักษร _Anuphan_ สำหรับภาษาไทยร่วมสมัย และ _IBM Plex Mono_ สำหรับตัวเลขและรหัสทางเทคนิค
- **Visual Style**: ถ่ายทอดความเป็นเทคโนโลยีผ่าน Grid, เส้นคั่นบาง (Thin Dividers), ตัวเลขบอกลำดับ (01/, 02/), Monograms และ Technical Labels หลีกเลี่ยงเอฟเฟกต์ไซเบอร์พังค์หรือ 3D ลอยตัว

---

## 2. โครงสร้างระบบ 7 ส่วนหลัก (Strict Scope)

1. **หน้าหลัก (`index.php`)**: Hero editorial layout, รูปภาพห้องแล็บ, ชื่อสาขา, แถบสถิติหลักสูตร, ดัชนีสารบัญ (02-05), ข่าวเด่น และไฮไลต์กิจกรรมถัดไป
2. **เกี่ยวกับสาขา (`about.php`)**: ด้านการเรียนรู้ 5 มิติ (A.01–A.05), โปรไฟล์หัวหน้าสาขาวิชา, ข้อมูลการติดต่อราชการ
3. **ข่าวสาร (`news.php`)**: ตัวกรองชิปหมวดหมู่ข่าว, ข่าวเด่นปักหมุด, รายการข่าวสาร และหน้ารายละเอียดบทความข่าวฉบับเต็ม (`?id=`)
4. **ตารางเรียนและกิจกรรม (`schedule.php`)**: ตัวสลับเซกเมนต์ระหว่างตารางเรียนรายสัปดาห์ (แสดงแท็บวันจันทร์-ศุกร์บนมือถือ / ตารางกริดบนเดสก์ท็อป) และปฏิทินกิจกรรมรายเดือน
5. **หน้าล็อกอิน (`login.php`) / ออกจากระบบ (`logout.php`)**: เข้าสู่ระบบเจ้าหน้าที่ พร้อมปุ่มแสดง/ซ่อนรหัสผ่าน และระบบป้องกัน CSRF
6. **หน้า Admin (`admin/`)**:
   - `admin/index.php`: แดชบอร์ดภาพรวมและสรุปสถิติ
   - `admin/hero.php`: เปลี่ยนรูป Hero และแก้ไขข้อความ/ตัวเลขสถิติหน้าแรก
   - `admin/news.php`: จัดการข่าวสารและหมวดหมู่ (เพิ่ม/แก้ไข/ลบ/อัปโหลดรูป)
   - `admin/classes.php`: จัดการคาบเรียนในตารางเรียนรายสัปดาห์
   - `admin/activities.php`: จัดการตารางกิจกรรมและป้ายกำกับ
   - `admin/teachers.php`: จัดการทำเนียบอาจารย์และระบุหัวหน้าสาขา
   - `admin/club.php`: จัดการสมาชิกคณะกรรมการชมรมวิชาชีพ
   - `admin/users.php`: จัดการบัญชีผู้ดูแลระบบ
7. **อาจารย์ในสาขาและชมรม (`teachers.php`)**: ทำเนียบคณาจารย์ผู้สอน และทำเนียบประธาน/กรรมการชมรมวิชาชีพ

---

## 3. ข้อมูลเข้าสู่ระบบสำหรับ Development เท่านั้น

- **URL เข้าสู่ระบบ**: `http://localhost/web/login.php`
- **ชื่อผู้ใช้ (Username)**: `admin`
- **รหัสผ่าน (Password)**: `admin1234`

ห้ามใช้บัญชี/รหัสผ่านเริ่มต้นบน Production ดูขั้นตอนเตรียมระบบที่ [docs/production.md](docs/production.md) และรัน `php tools/check-production.php` ก่อนเปิดใช้งานจริง

---

## 4. โครงสร้างฐานข้อมูล

- ฐานข้อมูล: `dbt_web` (MySQL / MariaDB บน XAMPP)
- รันไฟล์ SQL นำเข้าข้อมูลเริ่มต้นที่: `database/schema.sql`

## 5. ตารางเรียนแบบรูปภาพ / จัดตารางเอง

สำหรับฐานข้อมูลเดิม ให้รันจากโฟลเดอร์โปรเจกต์ก่อนใช้โค้ดรุ่นนี้:

```powershell
C:/xampp/php/php.exe database/migrations/2026_10_class_timetable_images.php
```

Migration เพิ่ม `class_timetables` และลบ `classes.course_type` พร้อมดัชนีเดิม โดยคงรายวิชาและคาบเรียนไว้ทั้งหมด สำรองค่าประเภทเดิมที่ `scratch/migration-backups/` ก่อนลบคอลัมน์ สามารถรันซ้ำได้อย่างปลอดภัย สำหรับการติดตั้งใหม่ใช้ schema/seed ที่อัปเดตแล้วได้เลย

ที่ **Admin → ตารางเรียน** เลือกกลุ่มเรียน แล้วกด **จัดตารางเอง** หรือ **อัปโหลดรูป** เพื่อเปิดตัวแก้ไขที่ต้องการ อีกส่วนจะถูกซ่อน การสลับโหมดเก็บรูปภาพและคาบเรียนเดิมไว้ กด **บันทึกรูปแบบ** เพื่อเผยแพร่โหมดที่เลือกบนหน้าหลักและหน้าตารางเรียน รูปภาพรองรับ JPG/PNG/WebP ไม่เกิน 5 MB และแสดงเต็มรูปอยู่ภายในกรอบอัปโหลด หากเนื้อหาในโหมดที่เลือกไม่พร้อม ระบบจะแจ้งแทนโดยไม่สลับโหมดอัตโนมัติ

ตั้งค่า PHP `upload_max_filesize` อย่างน้อย `5M` และ `post_max_size` มากกว่า `5M` เพื่อรองรับขนาดไฟล์เต็มตามที่ระบุ รูปตารางพิมพ์/บันทึก PDF ผ่านหน้าต่างพิมพ์ของเบราว์เซอร์ และดาวน์โหลดไฟล์ภาพต้นฉบับได้

ตรวจสอบ regression จากโฟลเดอร์โปรเจกต์ (ต้องมี MySQL ที่เชื่อมต่อได้):

```powershell
C:/xampp/php/php.exe -d session.save_path=scratch tests/timetable.php
C:/xampp/php/php.exe tests/schema.php
C:/xampp/php/php.exe tests/schema.php --modular
node tests/timetable-mode-ui.cjs
node tests/production-http.cjs
C:/xampp/php/php.exe -d session.save_path=scratch tests/runtime.php
```

การทดสอบ timetable ใช้ transaction แล้ว rollback พร้อมลบเฉพาะไฟล์ fixture ที่สร้าง ส่วนการทดสอบ schema ใช้ฐานข้อมูลชั่วคราวและลบทิ้งเมื่อจบ จึงต้องใช้บัญชีฐานข้อมูลที่สร้างฐานข้อมูลชั่วคราวได้

ทดสอบ HTTP เพิ่มเติมด้วย `node tests/timetable-http.cjs` เมื่อเว็บนี้ทำงานที่ `http://localhost` โดยกำหนด `DBT_TEST_USERNAME` และ `DBT_TEST_PASSWORD` ใน environment ชั่วคราวสำหรับบัญชีทดสอบ ห้ามทดสอบกับฐานข้อมูล Production การทดสอบใช้กลุ่มเรียนชื่อสุ่มที่ขึ้นต้นด้วย `__test_http_` แล้วลบเฉพาะข้อมูลและไฟล์ fixture ของกลุ่มนั้นเมื่อจบ

## 6. สร้างแพ็กเกจ Production

รัน `powershell -File tools/package-release.ps1` เพื่อสร้าง ZIP และ SHA256 ใน `dist/` แพ็กเกจจะไม่รวม `.env`, รูปที่ผู้ใช้อัปโหลด, demo seeds, tests, screenshots, graph, cache และ backup เก็บไฟล์อัปโหลดเดิมเมื่ออัปเดตเว็บ และทำตาม [คู่มือ Production](docs/production.md) ก่อน Deploy
