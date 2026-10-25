# Digital Business Technology (DBT) — Codebase Summary & Style Guide

เอกสารสรุปภาพรวมของโค้ดเบส (Codebase Summary) และคู่มือมาตรฐานงานออกแบบและเขียนโค้ด (Stylize & Engineering Guide) สำหรับโครงการ **เว็บไซต์ทางการและระบบบริหารจัดการข้อมูล แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล**

---

## ส่วนที่ 1: สรุปภาพรวมโค้ดเบส (Codebase Summary)

### 1.1 จุดประสงค์และเป้าหมายของระบบ (System Purpose)
ระบบเว็บท่า (Portal) และระบบบริหารจัดการเนื้อหา (CMS/Admin) แบบ Mobile-First สำหรับแผนกวิชาเทคโนโลยีธุรกิจดิจิทัล สร้างขึ้นเพื่อแก้ไขปัญหาเว็บไซต์สถานศึกษาแบบดั้งเดิมที่มักติดกับดักการออกแบบแบบ Desktop-first ย่อส่วนลงมา และมีสุนทรียศาสตร์แบบ AI-template สำเร็จรูป (เช่น แสงนีออน, Cyberpunk, ภาพหุ่นยนต์ 3D ลอยตัว)

ระบบนี้สร้างสรรค์ภายใต้แนวคิด **"Human-designed Digital Technology"** ที่มุ่งเน้น:
- การใช้งานผ่านสมาร์ตโฟนด้วยมือเดียวอย่างคล่องตัว (เน้นฐานหน้าจอ 360px – 430px)
- รูปลักษณ์สไตล์งานบรรณาธิการดิจิทัล (Editorial Graphic Design) เรียบ สะอาด แม่นยำ และมีระเบียบ
- ระบบหลังบ้านที่ตอบสนองรวดเร็ว ปลอดภัย และจัดการข้อมูลสำคัญได้ครบถ้วนบนอุปกรณ์พกพา

---

### 1.2 เทคโนโลยีและโครงสร้างทางเทคนิค (Tech Stack)
- **Runtime & Language**: PHP 8.x (ไม่ใช้ Framework ขนาดใหญ่ เพื่อความเร็วและความเข้ากันได้กับโฮสติ้งสถานศึกษา เช่น XAMPP / Apache ทั่วไป)
- **Database**: MySQL / MariaDB (ใช้ PDO พร้อม Prepared Statements 100%)
- **CSS Architecture**: Bootstrap 5.3 (CDN) ใช้เฉพาะส่วน Reboot, Flexbox และ Grid System โดยทำการ Override CSS Variables (`--bs-*`) และสร้าง Design Tokens ทั้งหมดใน `assets/css/site.css`
- **Typography Fonts**:
  - `Anuphan` (Google Fonts): ฟอนต์เนื้อหาภาษาไทยร่วมสมัย อ่านสบายตา
  - `IBM Plex Mono` (Google Fonts): ฟอนต์ Monospace สำหรับตัวเลขทางเทคนิค รหัสวิชา วันที่ และ Section Labels
- **Iconography & Graphics**: สัญลักษณ์แบบพิมพ์ (Typography Glyphs), เส้นคั่นบาง, ตัวเลขนำหน้า (01/, 02/), Monograms อักษรย่อ ไม่พึ่งพาชุดไอคอนภายนอกขนาดใหญ่

---

### 1.3 สถาปัตยกรรมและโครงสร้างไดเรกทอรี (Directory Structure)

```
web/
├── .gitignore
├── .scratch/                  # บันทึกงาน ปัญหา และข้อกำหนดการพัฒนา
├── CONTEXT.md                 # คำศัพท์เฉพาะของโดเมน (Domain Glossary)
├── README.md                  # สรุปคู่มือและสเปกหลักของโครงการ
├── STYLE.md                   # เอกสารมาตรฐานนี้
├── about.php                  # 02 / หน้าเกี่ยวกับแผนก (5 มิติ, หัวหน้าแผนก, ติดต่อ)
├── index.php                  # 01 / หน้าแรก (Hero editorial, ตัวเลขสถิติ, ข่าวเด่น)
├── login.php                  # 05 / หน้าเข้าสู่ระบบเจ้าหน้าที่ (CSRF, Toggle Password)
├── logout.php                 # ออกจากระบบและทำลาย Session
├── news.php                   # 03 / ข่าวสารและกิจกรรม (ตัวกรองชิป, หน้าอ่านบทความ ?id=)
├── schedule.php               # 04 / ตารางเรียนและกิจกรรม (Day Tabs บนมือถือ, Grid บนเดสก์ท็อป)
├── teachers.php               # 07 / บุคลากรและชมรมวิชาชีพ (แยกครูและนักศึกษาชัดเจน)
│
├── admin/                     # 06 / ระบบจัดการหลังบ้าน (Admin Console)
│   ├── index.php              # แดชบอร์ดภาพรวมและสรุปสถิติ
│   ├── hero.php               # จัดการภาพ Hero, คำบรรยาย และข้อความหน้าแรก
│   ├── news.php               # จัดการบทความข่าวสาร (เพิ่ม/แก้ไข/ลบ/อัปโหลดภาพ)
│   ├── classes.php            # จัดการคาบเรียนในตารางเรียนรายสัปดาห์
│   ├── activities.php         # จัดการปฏิทินกิจกรรม
│   ├── teachers.php           # จัดการทำเนียบอาจารย์และระบุหัวหน้าแผนก
│   ├── club.php               # จัดการสมาชิกและหัวหน้าชมรมวิชาชีพ
│   └── users.php              # จัดการบัญชีผู้ใช้งานระบบ (RBAC: Admin / Editor)
│
├── assets/                    # ทรัพยากรระบบ
│   ├── css/
│   │   ├── site.css           # Design Tokens, Editorial Components, Mobile Nav, Responsive Rules
│   │   └── admin.css          # สไตล์เฉพาะของแผงควบคุมหลังบ้าน
│   └── js/
│       └── app.js             # การโต้ตอบหน้าบ้าน (Tabs, Filters, Confirm Dialogs)
│
├── database/
│   └── schema.sql             # โครงสร้างตารางและข้อมูลเริ่มต้น (Seed Data)
│
├── docs/
│   ├── spec.md                # ข้อกำหนดระบบและ User Stories ทั้ง 32 ข้อ
│   └── adr/                   # บันทึกการตัดสินใจทางสถาปัตยกรรม (Architecture Decision Records)
│       ├── 0001-editorial-technology-design-system.md
│       └── 0002-dual-domain-faculty-and-club.md
│
├── includes/                  # โมดูลและส่วนประกอบส่วนกลาง
│   ├── config.php             # กำหนดค่าคงที่, Base URL Auto-detect, Session Startup
│   ├── db.php                 # การเชื่อมต่อฐานข้อมูล PDO Singleton/Instance
│   ├── auth.php               # การตรวจสอบสิทธิ์, สิทธิ์การใช้งาน (Admin/Editor), ป้องกัน CSRF
│   ├── helpers.php            # ฟังก์ชันช่วยเหลือ (e(), url(), q(), flash(), format_date())
│   ├── header.php             # ส่วนหัวเว็บและเมนูหน้าบ้าน
│   ├── footer.php             # ส่วนท้ายเว็บและ Bottom Navigation บนมือถือ
│   ├── admin-header.php       # ส่วนหัวและแถบเมนูนำทางหลังบ้าน
│   └── admin-footer.php       # ส่วนท้ายแผงควบคุมหลังบ้าน
│
└── uploads/                   # ที่จัดเก็บรูปภาพอัปโหลด
    └── seed/                  # ภาพตั้งต้นสำหรับการทดสอบและใช้งานจริง
```

---

## ส่วนที่ 2: มาตรฐานงานออกแบบ (Stylize & Design System Guide)

### 2.1 ปรัชญาการออกแบบ (Design Philosophy)
> **"Human-designed Digital Technology"**
> เทคโนโลยีที่คำนึงถึงมนุษย์ นำเสนอความแม่นยำทางวิศวกรรมผ่านโครงสร้าง (Structure), ระบบกริด (Grid), เส้นสาย (Thin Line Dividers), และการจัดลำดับชั้นข้อมูล (Data Hierarchy) แทนการใช้กราฟิกแฟนตาซีหรือเอฟเฟกต์ฉูดฉาด

**ข้อห้ามสำคัญทางสายตา (Visual Anti-Patterns):**
- ❌ ห้ามใช้เงาลอยฟุ้งขนาดใหญ่ (Soft Blur Drop Shadows) สไตล์ Glassmorphism
- ❌ ห้ามใช้สีนีออนเรืองแสง โทน Cyberpunk หรือการไล่สีสายรุ้ง
- ❌ ห้ามใช้วัตถุ 3D ลอยตัว หรือรูปภาพหุ่นยนต์/สมองวงจรที่สร้างจาก AI สำเร็จรูป
- ❌ ห้ามใช้รูป Avatar ทรงกลมสีเทาสำเร็จรูป (ให้ใช้ **Technical Monogram** ตัวอักษรย่อพื้นหลังสีอ่อนเสมอ)

---

### 2.2 สัดส่วนคู่สี (Color Palette & Golden Ratio)

| บทบาทสี | ชื่อสี | ค่าสี HEX | สัดส่วน | วัตถุประสงค์การใช้งาน |
| :--- | :--- | :--- | :---: | :--- |
| **Canvas Background** | Pure White & Soft Gray | `#FFFFFF` / `#F4F8FA` | **65%** | พื้นหลังหลักและพื้นผิวสลับความลึก |
| **Secondary Tint** | Soft Light Blue | `#E6F1F5` / `#B9DCEB` | **20%** | แถบเส้นคั่น ไฮไลต์กล่องข้อมูล กรอบภาพ และ Badge พื้นหลัง |
| **Deep Structural** | Deep Navy | `#17324D` / `#2E4A66` | **10%** | หัวข้อ ข้อความสำคัญ ส่วนหัวตาราง แถบเมนูด้านบน/ล่าง |
| **Functional Accent** | Warm Orange | `#E99A4A` / `#B5601F` | **5%** | ปุ่ม Call-to-Action, จุดสถานะ, เส้นเน้นสั้น (Rule), แท็กสำคัญ |

#### กฎความคมชัดและการเข้าถึง (WCAG AA Accessibility Rule for Orange):
1. **เมื่อใช้สีส้มเป็นพื้นหลังปุ่ม (`#E99A4A`)**: จะต้องใช้ตัวอักษรสี **Deep Navy (`#17324D`)** เท่านั้น (ให้ค่า Contrast ~5.7:1 ผ่านเกณฑ์ WCAG AA) ห้ามใช้ตัวอักษรสีขาวบนพื้นส้ม
2. **เมื่อใช้สีส้มเป็นข้อความหรือป้ายกำกับ (Text Label)**: ให้ใช้โทนส้มเข้ม **`#B5601F` (`--c-accent-ink`)** บนพื้นขาวหรือพื้นเทาอ่อน เพื่อให้อ่านออกได้อย่างชัดเจนและผ่านเกณฑ์ Contrast >= 4.5:1

---

### 2.3 ระบบตัวพิมพ์ (Typography & Hierarchies)

- **เนื้อหาหลักและภาษาไทย**: ใช้ `Anuphan` (`font-weight: 300, 400, 500, 600`)
- **ข้อมูลเทคนิค รหัส ตัวเลข วันที่ และสารบัญ**: ใช้ `IBM Plex Mono` (`font-weight: 400, 500, 600`)

#### สเกลขนาดตัวอักษร (Fluid Clamp System):
- `--fs-display`: `clamp(2.5rem, 1.4rem + 6.4vw, 6rem)` (ชื่อแผนกหลัก)
- `--fs-h1`: `clamp(2rem, 1.45rem + 2.6vw, 3.5rem)` (หัวเรื่องหน้า)
- `--fs-h2`: `clamp(1.5rem, 1.2rem + 1.4vw, 2.25rem)` (หัวเรื่องส่วน Section)
- `--fs-h3`: `clamp(1.125rem, 1.05rem + .35vw, 1.375rem)` (หัวข้อย่อย/การ์ด)
- `--fs-body`: `1rem` (16px — ขนาดพื้นฐาน อ่านง่ายบนมือถือ)
- `--fs-small`: `0.875rem` (14px — คำอธิบายประกอบ)
- `--fs-mono`: `0.75rem` (12px — Technical Eyebrow / Meta Tags)

---

### 2.4 กฎรูปทรงและเส้นสาย (Geometry & Layout Rules)

1. **รัศมีขอบมน (Border Radius)**:
   - ปฏิบัติตามมาตรฐานงานพิมพ์สไตล์บรรณาธิการ: รัศมีคม `0px` ถึงสูงสุดไม่เกิน `4px` (`--radius: 2px`)
   - ไม่ใช้ขอบมนกลมจัด (Pill shape) เกินความจำเป็น ยกเว้นปุ่มชิปตัวกรองหมวดหมู่
2. **การแบ่งส่วนและมิติพื้นผิว (Dividers & Depth)**:
   - ใช้เส้นคั่นบางเฉียบ `1px solid var(--c-line)` (`#DCE6EC`)
   - ใช้การสลับสีพื้นหลังส่วน Section ระหว่างสีขาว (`#FFFFFF`) และสีเทาฟ้าอ่อน (`#F4F8FA`) แทนการทิ้งเงาฟุ้ง
   - เส้นเน้นสั้น (Accent Rule): ขนาดกว้าง `32px` สูง `3px` สี `--c-accent`
3. **สารบัญลำดับเลข (Numbered Editorial Indices)**:
   - ทุกหน้าและทุกส่วนหลักจะมีรหัสตัวเลขกำกับ เช่น `01 / HOME`, `02 / ABOUT`, `03 / NEWS`, `04 / SCHEDULE`, `05 / LOGIN`, `07 / DIRECTORY`

---

### 2.5 ปฏิสัมพันธ์บนมือถือ (Mobile-First Experience)

1. **Persistent Bottom Navigation**:
   - บนหน้าจอพกพา (< 768px) จะแสดงแถบเมนูด้านล่างสูง `64px` มี 5 จุดหมายหลักที่เข้าถึงง่ายด้วยนิ้วโป้ง
   - ตัวบอดี้จะเผื่อระยะ `padding-bottom: calc(var(--bottom-nav-h) + env(safe-area-inset-bottom))` ป้องกันเนื้อหาถูกบัง
2. **ระยะสัมผัส (Touch Targets)**:
   - ลิงก์และปุ่มกดทุกจุดต้องมีความสูงหรือพื้นที่สัมผัสอย่างน้อย **44px x 44px**
3. **การแสดงผลตารางเรียน (Schedule Display)**:
   - **Mobile (< 768px)**: แปลงเป็นแถบเลือกวัน (Day Selector Mon-Fri) และแสดงคาบเรียนเป็นเส้นเวลาแนวตั้ง (Vertical Timeline) ป้องกันการเลื่อนจอแนวนอนที่หลุดกรอบ
   - **Desktop (>= 768px)**: แสดงผลเป็นตารางกริด 5 วันครบทั้งสัปดาห์

---

## ส่วนที่ 3: แนวทางปฏิบัติในการพัฒนาโค้ด (Coding & Engineering Standards)

### 3.1 ความปลอดภัยของระบบ (Security Standards)
1. **การป้องกัน XSS**: ทุกข้อความและตัวแปรที่นำออกแสดงผลสู่ HTML จะต้องผ่านการ Escape ด้วยฟังก์ชัน `e()`:
   ```php
   <?= e($variable) ?>
   ```
2. **การป้องกัน CSRF**: ทุกฟอร์มที่ส่งผ่านเมธอด `POST` จะต้องมี Hidden Input Token:
   ```php
   <?= csrf_field() ?>
   ```
   และที่ฝั่งประมวลผลปลายทางจะต้องเรียกฟังก์ชัน `validate_csrf()` เสมอ
3. **การป้องกัน SQL Injection**: ห้ามต่อสตริงในคำสั่ง SQL เป็นอันขาด ให้ใช้ Prepared Statements ผ่าน Helper `q($sql, $params)`
4. **ความปลอดภัยของเซสชันและการเข้าถึง**:
   - ตรวจสอบการเข้าสู่ระบบด้วย `require_login()` ในทุกไฟล์ส่วนของ Admin
   - ตรวจสอบสิทธิ์ระดับผู้ดูแลสูงสุดด้วย `require_admin()` เมื่อเข้าถึงโมดูลจัดการผู้ใช้ (`users.php`) หรือการตั้งค่าภาพรวม
   - ไม่อนุญาตให้ผู้ดูแลระบบลบบัญชีของตนเอง (`prevent self-deletion`)

---

### 3.2 กฎการเพิ่มและดูแลรักษาฟีเจอร์ (Strict Scope Rule)
- รักษาขอบเขตสถาปัตยกรรม 7 ส่วนหลักตามข้อกำหนด ไม่แตกไฟล์หน้าสาธารณะใหม่โดยไม่จำเป็น
- การจัดการข้อมูลทั้งหมดต้องแยกหมวดหมู่และจัดการได้จากหน้า `admin/` ที่เตรียมไว้
- เมื่อไม่มีภาพถ่ายอัปโหลด ให้คงรูปแบบ Fallback เป็น Monogram เสมอเพื่อรักษาความประณีตของ UI

---
*เอกสารนี้จัดทำขึ้นเพื่อใช้เป็นแม่แบบและมาตรฐานสูงสุดสำหรับนักพัฒนาและผู้ออกแบบระบบ Digital Business Technology (DBT)*
