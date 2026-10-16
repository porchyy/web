# สารบัญเอกสารระบบ (DBT System Documentation Hub)

ยินดีต้อนรับสู่ศูนย์รวมเอกสารข้อกำหนดและสถาปัตยกรรมระบบ **เว็บไซต์และระบบบริหารจัดการข้อมูล สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล (Digital Business Technology - DBT)**

เอกสารทั้งหมดในไดเรกทอรีนี้ถูกปรับปรุงและรวบรวมให้เป็นศูนย์กลางข้อมูลทางเทคนิคของโครงการอย่างสมบูรณ์ ทั้งมาตรฐานงานออกแบบ UI/UX ตาม **[style.md](file:///c:/xampp/htdocs/web/docs/style.md)**, มาตรฐานวิศวกรรมซอฟต์แวร์ตาม **[rules.md](file:///c:/xampp/htdocs/web/docs/rules.md)**, ข้อกำหนดระบบฉบับสมบูรณ์ใน **[spec.md](file:///c:/xampp/htdocs/web/docs/spec.md)**, และบันทึกการตัดสินใจเชิงสถาปัตยกรรมใน **[adr/](file:///c:/xampp/htdocs/web/docs/adr/)**

---

## 1. เอกสารมาตรฐานหลักของระบบ (Core System Standards)

### 1.1 [rules.md](file:///c:/xampp/htdocs/web/docs/rules.md) — กฎวิศวกรรมและมาตรฐานซอฟต์แวร์ (Engineering & Architecture Rules)

- **โครงสร้างโปรเจกต์ (Zero Clutter)**: การแยกหน้าที่ชัดเจนระหว่าง `pages/`, `admin/`, `assets/`, `includes/`, และ `docs/`
- **มาตรฐานการตั้งชื่อส่วนกลาง (Unified Naming Conventions)**: ตาราง Master Reference ครอบคลุม PHP, SQL, HTML, CSS, JavaScript, และ Network/API
- **กฎการเขียนโค้ดสะอาด (Clean Code)**: The Boy Scout Rule, DRY, Function SRP, ห้าม Magic Numbers, กำจัด Dead Code
- **การแยกหน้าที่ (Separation of Concerns: SoC)**: ห้ามเขียน SQL ใน View, ห้าม Inline JS เกิน 5 บรรทัด, ห้ามต่อสตริง HTML ดิบ (`innerHTML`)
- **ประสิทธิภาพและความเร็ว**: นโยบายแคช 60s–120s TTL พร้อม Instant Invalidation, Batch DOM ด้วย `DocumentFragment`
- **ความมั่นคงปลอดภัยขั้นสูง**: การเข้ารหัส Argon2id, Whitelist HTML Sanitization ด้วย `DOMDocument`, และ CSRF Verification

### 1.2 [style.md](file:///c:/xampp/htdocs/web/docs/style.md) — มาตรฐานงานออกแบบและระบบภาพ (Design System & Visual Standards)

- **ปรัชญาและทิศทางการออกแบบ**: Refined Editorial Institutional Design ผสาน Human-designed Digital Technology
- **สัดส่วนสี (Color Ratios)**: ขาว/เทาอ่อน (`#FFFFFF`, `#F4F8FA`) ~65%, ฟ้าอ่อน (`#E6F1F5`, `#B9DCEB`) ~20%, กรมท่า Deep Navy (`#17324D`) ~10%, ส้ม Accent (`#E99A4A` / `#B5601F`) ~5%
- **ระบบตัวอักษร (Typography)**: `Kanit` (Display & Headings), `Prompt` / `Anuphan` (Body & Interface), และ `IBM Plex Mono` (Technical Metadata)
- **เรขาคณิตและเส้นสาย**: Border Radius 0–4px คมชัด, เส้นคั่นบาง 1px (`#DCE6EC`), ปราศจากเงาฟุ้งแบบพลาสติก, ไม่มีเอฟเฟกต์ Cyberpunk หรือ 3D ลอยตัว
- **ส่วนประกอบหน้าจอ (Components)**: Bottom Navigation Bar 5 แท็บสำหรับ Mobile, ตารางเรียนแบบ Editorial Brutalism, Monogram อวตาร, การ์ดหลักสูตร และฟอร์มคอนโทรล

### 1.3 [spec.md](file:///c:/xampp/htdocs/web/docs/spec.md) — ข้อกำหนดระบบฉบับสมบูรณ์ (System Specification)

- **ปัญหาและแนวทางแก้ไข (Problem & Solution)**: การแก้ปัญหาเว็บการศึกษาบนหน้าจอมือถือ และการจัดการข้อมูลหลังบ้าน
- **User Stories (1–38)**: ข้อกำหนดฟังก์ชันงานครอบคลุมผู้ใช้งานทุกกลุ่ม (นักเรียน, ผู้ปกครอง, อาจารย์, เจ้าหน้าที่, และผู้ดูแลระบบ)
- **การตัดสินใจนำไปปฏิบัติจริง (Implementation Decisions)**: สถาปัตยกรรม PHP 8, PDO Singleton, Domain Service Layer, Modular CSS Tokens, Vanilla JS Micro-Modules, และการบริหารจัดการหลังบ้านครบ 8 โมดูล
- **เกณฑ์การทดสอบและการควบคุมคุณภาพ (Testing Decisions)**: HTTP Seams, Security Testing, Responsive Viewports (390px, 768px, 1024px), และ Print Simulation

### 1.4 [component-architecture-mindmap.md](file:///c:/xampp/htdocs/web/docs/component-architecture-mindmap.md) — แผนผังสถาปัตยกรรมส่วนประกอบ (Component Architecture Mindmap)

- **แผนผังความสัมพันธ์ (Mermaid Mindmap)**: การเชื่อมต่อระหว่าง Views ↔ Design Tokens ↔ Modular CSS ↔ Vanilla JS Micro-Modules ↔ Decoupled API Micro-endpoints ↔ Domain Services
- **ตารางทะเบียนส่วนประกอบ (Component Registry)**: ระบุพิกัดไฟล์ (File Location), หน้าที่เรียกใช้ (Consumers), ไฟล์ CSS/JS ควบคุม, และ API/Service ที่เกี่ยวข้องครบทุกตัว

---

## 2. บันทึกการตัดสินใจเชิงสถาปัตยกรรม (Architecture Decision Records - ADR)

บันทึกการตัดสินใจเชิงเทคนิคและสถาปัตยกรรมสำคัญที่ผ่านการยอมรับ (Accepted) และนำไปปฏิบัติจริงในโค้ดปัจจุบัน:

| รหัส                                                                                               | หัวข้อการตัดสินใจ                                                        | สถานะ    | สาระสำคัญ                                                                                                                                                                                |
| :------------------------------------------------------------------------------------------------- | :----------------------------------------------------------------------- | :------- | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **[0001](file:///c:/xampp/htdocs/web/docs/adr/0001-editorial-technology-design-system.md)**        | สถาปัตยกรรม Editorial Technology บน PHP & Bootstrap 5                    | Accepted | การใช้ Bootstrap 5.3 เฉพาะโครงสร้างพื้นฐาน และ Override ด้วย Modular Tokens (สัดส่วนสี 65/20/10/5%, ขอบมน 0–4px, เส้นขอบบาง 1px, ฟอนต์ Kanit/Prompt/IBM Plex Mono, Mobile Bottom Nav)    |
| **[0002](file:///c:/xampp/htdocs/web/docs/adr/0002-dual-domain-faculty-and-club.md)**              | การแบ่งสัดส่วนข้อมูลอาจารย์และชมรมวิชาชีพในหน้า 07                       | Accepted | การจัดกลุ่มข้อมูลอาจารย์และชมรมวิชาชีพไว้ใน `pages/teachers.php` โดยแบ่ง 2 ส่วนทางสายตา พร้อมระบบลากวางสลับตำแหน่งหัวหน้า (Position Swapping) ในหลังบ้าน                                 |
| **[0003](file:///c:/xampp/htdocs/web/docs/adr/0003-domain-service-layer-and-caching.md)**          | สถาปัตยกรรม Domain Service Layer และระบบแคชประสิทธิภาพสูง (60s–120s TTL) | Accepted | การแยกตรรกะทางธุรกิจสู่ `includes/services/` (Zero SQL ใน Views/Controllers) พร้อมระบบแคชข้อมูลสั้น 1–2 นาที และกลไกล้างแคชทันทีเมื่อมีการบันทึก (Instant Cache Busting)                 |
| **[0004](file:///c:/xampp/htdocs/web/docs/adr/0004-public-pages-clustering-and-clean-routing.md)** | การจัดกลุ่มหน้าเพจสาธารณะ (pages/) และระบบ Clean URL Routing             | Accepted | การจัดกลุ่มหน้าสาธารณะไว้ใน `pages/` (Zero Clutter Root Directory) พร้อมระบบ Clean URL ผ่าน `.htaccess` และตัวจัดการข้อผิดพลาดส่วนกลาง `error.php` (403, 404, 419, 500)                  |
| **[0005](file:///c:/xampp/htdocs/web/docs/adr/0005-argon2id-password-hashing-and-security.md)**    | การยกระดับความปลอดภัยด้วย Argon2id และ Whitelist HTML Sanitization       | Accepted | การเข้ารหัสรหัสผ่านด้วย Argon2id พร้อมการอัปเกรดแฮชอัตโนมัติขณะล็อกอิน, การล้างข้อมูล Rich Text ด้วย DOMDocument Whitelist (`clean_html()`), และการป้องกัน CSRF                          |
| **[0006](file:///c:/xampp/htdocs/web/docs/adr/0006-modular-css-and-vanilla-js-micro-modules.md)**  | การแยกส่วนโมดูลาร์ CSS Design Tokens และ Vanilla JS Micro-Modules        | Accepted | การแตกไฟล์ CSS สู่โครงสร้างโมดูลาร์ผ่าน `@import` และการสร้างไมโครโมดูล JavaScript ไร้ Dependency ภายนอก (`modal.js`, `admin-scheduler.js`, `admin-tree-slots.js`, `admin-quill.js` ฯลฯ) |
| **[0007](file:///c:/xampp/htdocs/web/docs/adr/0007-database-backed-system-settings.md)**           | การย้ายการตั้งค่าระบบสู่ฐานข้อมูลและโมดูลบริหารจัดการหลังบ้าน            | Accepted | การสงวน `.env` ไว้เฉพาะข้อมูลลับระบบ และย้ายข้อมูลการติดต่อ อัตลักษณ์ และสถิติสู่ตาราง `settings` ใน MySQL จัดการผ่าน `admin/settings.php` และ `SettingService`                          |

---

## 3. แผนที่โครงสร้างเอกสารของโครงการ (Project Documentation Structure)

```
web/
├── docs/                                  # ศูนย์รวมเอกสารระบบทั้งหมด (Centralized Documentation Hub)
│   ├── README.md                          # ดัชนีสารบัญเอกสารนี้
│   ├── rules.md                           # กฎเหล็กวิศวกรรมซอฟต์แวร์ (Engineering, Architecture & Clean Code)
│   ├── style.md                           # คัมภีร์งานออกแบบ (Design System, Tokens & Visual Standards)
│   ├── spec.md                            # ข้อกำหนดระบบฉบับสมบูรณ์ (Functional & Technical Specs)
│   ├── component-architecture-mindmap.md  # แผนผังสถาปัตยกรรมส่วนประกอบ (Component Living Map)
│   └── adr/                               # บันทึกการตัดสินใจเชิงสถาปัตยกรรม (ADR 0001–0007)
│       ├── 0001-editorial-technology-design-system.md
│       ├── 0002-dual-domain-faculty-and-club.md
│       ├── 0003-domain-service-layer-and-caching.md
│       ├── 0004-public-pages-clustering-and-clean-routing.md
│       ├── 0005-argon2id-password-hashing-and-security.md
│       ├── 0006-modular-css-and-vanilla-js-micro-modules.md
│       └── 0007-database-backed-system-settings.md
│
├── PLAN.md                                # แผนงานการปรับปรุงและรีแฟกเตอร์ระบบ (Phased Roadmap)
├── CONTEXT.md                             # คำศัพท์และบริบททางธุรกิจ (Domain Glossary)
└── README.md                              # ข้อมูลภาพรวมโครงการและขั้นตอนการติดตั้งเริ่มต้น
```
