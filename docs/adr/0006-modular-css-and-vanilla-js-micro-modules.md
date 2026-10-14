# 0006. การแยกส่วนโมดูลาร์ CSS Design Tokens และ Vanilla JS Micro-Modules

## สถานะ
ยอมรับแล้ว (Accepted)

## บริบท (Context)
ไฟล์สไตล์ `site.css` (45KB) และ `admin.css` (35KB) เดิมมีลักษณะเป็นไฟล์เดี่ยวขนาดใหญ่ (God CSS) ยากต่อการแยกแยะและบำรุงรักษา อีกทั้งตรรกะ JavaScript บางจุดเคยถูกเขียนแบบ Inline ในไฟล์ PHP หรือมีการประกอบสตริง HTML ผ่าน `innerHTML` ซึ่งเสี่ยงต่อ DOM XSS และขัดกับ RULES.md (ข้อ 4.1 และ 4.2)

## การตัดสินใจ (Decision)
1. **สถาปัตยกรรม Modular CSS ผ่าน Native `@import`**:
   - แตกไฟล์ CSS ออกเป็นเลเยอร์ที่ชัดเจนภายใต้ไดเรกทอรี `assets/css/`:
     - `tokens.css`: กำหนดตัวแปรและโทเคนระบบ (Colors, Typography, Spacing, Radii 0–4px, 1px Dividers)
     - `base.css`: การรีเซ็ตเลย์เอาต์พื้นฐาน (Reboot & Layout)
     - `components/`: สไตล์สำหรับองค์ประกอบ UI เฉพาะชิ้น (`badges.css`, `buttons.css`, `cards.css`, `forms.css`, `modals.css`, `navigation.css`, `tables.css`)
     - `pages/`: สไตล์เฉพาะสำหรับแต่ละหน้าสาธารณะ (`page-home.css`, `page-about.css`, `page-schedule.css`, `page-teachers.css`, `page-login.css`, `page-news.css`)
     - `admin/`: สไตล์เฉพาะระบบจัดการหลังบ้าน (`admin-layout.css`, `admin-scheduler.css`, `admin-tree.css`, `admin-components.css`)
   - ใช้ `assets/css/site.css` และ `assets/css/admin.css` เป็น Entry Points ที่สะอาด นำเข้าโมดูลย่อยผ่านคำสั่ง `@import` มาตรฐานของ CSS
   - **รักษาอัตลักษณ์และ Design Decisions ทั้งหมดอย่างเคร่งครัด**: โทนสี 65/20/10/5%, ขอบมน 0–4px, เส้นขอบบาง 1px คมชัด, ตัวหนังสือ Kanit/Prompt/IBM Plex Mono
2. **Vanilla JS Micro-Modules & ปราศจาก External Libraries สำหรับ UI**:
   - แยกสคริปต์ JavaScript หน้าบ้านและหลังบ้านออกเป็นไมโครโมดูลใน `assets/js/`:
     - `modal.js`: คอมโพเนนต์หน้าต่างแจ้งเตือนและยืนยันตัวตน (Accessible Styled Modal) แทนที่ `alert()` และ `confirm()` ดั้งเดิมของเบราว์เซอร์
     - `app.js`: การโต้ตอบหน้าบ้านทั่วไป เช่น Mobile Drawer Navigation และ Viewport Sync
     - `admin-scheduler.js`: ตารางจัดตารางเรียนบนเดสก์ท็อปและมือถือ พร้อมตัวเลือกแท็บวัน (Day Selector) และ Handle ลากย้าย (`⠿`) ป้องกันการขัดขวางการเลื่อนหน้าจอ
     - `admin-tree-slots.js`: ผังโครงสร้างอาจารย์และชมรม รองรับ Drag & Drop พร้อมการสลับตำแหน่งหัวหน้า (Position Swapping)
     - `admin-quill.js`: ระบบจัดรูปแบบข้อความ Quill Editor ที่แปลง Markdown อัตโนมัติและรองรับการจัดหัวเรื่องแบบ Sub-phrase โดยไม่ทำลาย DOM เดิม
     - `admin-hero.js`: สคริปต์พรีวิว Hero Section แบบเรียลไทม์พร้อมปุ่มสลับมุมมอง Desktop/Mobile
     - `admin-image-upload.js`: ตัวจัดการอัปโหลดภาพแบบลากวาง (Drag-and-Drop) จัดวางพรีวิวคู่ขนาน (Side-by-Side)
3. **การสร้าง DOM ปลอดภัย (Safe Native DOM Construction)**:
   - ห้ามการแทรก HTML ผ่านสตริงดิบหรือ `innerHTML` โดยเด็ดขาด ให้สร้างผ่าน Native DOM APIs เช่น `document.createElement()`, `setAttribute()`, `textContent` และใช้ `DocumentFragment` เพื่อลด Browser Reflow

## ผลลัพธ์และข้อดี (Consequences)
- สถาปัตยกรรมโค้ดฝั่ง Client สะอาด บำรุงรักษาง่าย โหลดเร็วโดยไม่ต้องผ่าน Bundler หรือ Node.js
- ขจัดช่องโหว่ DOM XSS จากการต่อสตริง HTML
- การโต้ตอบบนหน้าจอมือถือราบรื่น (Smooth Scroll) ไม่มีปัญหา Touch Drag ขัดแย้งกับการเลื่อนหน้าจอ
- รูปลักษณ์ยังคงความเนี้ยบตามมาตรฐาน Refined Editorial Brutalism 100%
