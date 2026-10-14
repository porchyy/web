# 0004. การจัดกลุ่มหน้าเพจสาธารณะ (pages/) และระบบ Clean URL Routing

## สถานะ
ยอมรับแล้ว (Accepted)

## บริบท (Context)
ตาม RULES.md ข้อ 1 กำหนดหลักการ "ขจัดความแออัดของไฟล์ (Zero Clutter)" โดยห้ามเพิ่มหรือวางไฟล์หน้าเพจสาธารณะกระจัดกระจายอยู่ใน Root Directory เพื่อป้องกันความสับสน จัดหมวดหมู่ไฟล์ให้เป็นระเบียบ และรองรับระบบ URL ที่สวยงาม เป็นมิตรต่อผู้ใช้และ SEO

## การตัดสินใจ (Decision)
1. **การจัดกลุ่มหน้าสาธารณะ (Public Pages Cluster)**:
   - ย้ายหน้าสาธารณะทั้งหมดจาก Root Directory เข้าสู่ไดเรกทอรี `pages/`:
     - `pages/about.php` (เกี่ยวกับแผนกวิชา)
     - `pages/news.php` (ข่าวสารและประกาศ)
     - `pages/schedule.php` (ตารางเรียนและกิจกรรม)
     - `pages/teachers.php` (ทำเนียบคณาจารย์และชมรม)
     - `pages/login.php` (เข้าสู่ระบบเจ้าหน้าที่)
     - `pages/logout.php` (ออกจากระบบ)
   - Root Directory คงไว้เฉพาะไฟล์จำเป็น ได้แก่ `index.php` (หน้าแรก), `error.php` (ตัวจัดการ Error), `.htaccess`, `.env`, และไฟล์เอกสารหลัก
2. **ระบบ Clean URL Routing ผ่าน `.htaccess`**:
   - กำหนดกฎ Apache Rewrite Rules ให้สามารถเข้าถึงหน้าเพจด้วยเส้นทาง Clean URL (เช่น `/web/about`, `/web/news`, `/web/schedule`, `/web/teachers`, `/web/login`, `/web/logout`) โดยส่งต่อการทำงานไปยังไฟล์ใน `pages/` ภายในอย่างโปร่งใส
   - รองรับพารามิเตอร์ Query String (เช่น `?id=1`, `?tab=activities`, `?class_group=...`) อย่างสมบูรณ์
3. **การจัดการข้อผิดพลาดส่วนกลาง (`error.php`)**:
   - จัดการรหัสสถานะ HTTP 403 (Forbidden), 404 (Not Found), 419 (Page Expired / CSRF Token Mismatch) และ 500 (Internal Server Error) ผ่าน `error.php` เพียงไฟล์เดียว
   - ออกแบบหน้าแจ้งเตือนข้อผิดพลาดตามอัตลักษณ์ Refined Editorial Brutalism พร้อมปุ่มนำทางกลับหน้าแรกอย่างชัดเจน

## ผลลัพธ์และข้อดี (Consequences)
- โครงสร้าง Root Directory สะอาด ปราศจากไฟล์เพจปะปน
- ผู้ใช้งานได้รับประสบการณ์ URL ที่สั้นกระชับ สะอาด และปลอดภัย
- ระบบจัดการ Error เป็นเอกเทศ มีสไตล์ภาพที่สอดคล้องกับภาพรวมของเว็บไซต์
- การอ้างอิงไฟล์ Resource และ Include Path มีความสม่ำเสมอเป็นมาตรฐานเดียวกัน
