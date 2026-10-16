# แผนผังและสถาปัตยกรรมส่วนประกอบระบบ (DBT Component Architecture Mindmap)

> **มาตรฐานการจัดเก็บเอกสาร:** RULES.md Section 1 (Zero Clutter Architecture) & ADR 0001–0007  
> **วัตถุประสงค์:** แสดงความสัมพันธ์ของส่วนประกอบทั้งหมดในระบบ (Component Living Map) ว่าแต่ละส่วนประกอบอยู่ที่ไหน (File Location), มีเพจใดเรียกใช้ (Consumers), ควบคุมสไตล์ด้วย CSS ใด, จัดการพฤติกรรมด้วย JS ตัวใด, และติดต่อกับ API หรือ Domain Service ชั้นใด

---

## 1. แผนภาพความสัมพันธ์สถาปัตยกรรม (System Architecture Mindmap)

```mermaid
mindmap
  root((DBT Web System))
    ["🌐 Public Views (pages/)"]
      หน้าหลัก ["index.php"]
        Hero Section
        Latest News Cards
        Fast Schedule Widget
      เกี่ยวกับสาขา ["pages/about.php"]
        Vision & Mission
        Curriculum Cards
      ข่าวสารและประกาศ ["pages/news.php"]
        Featured News
        Category Chips Filter
        Article Detail View
      ตารางเรียนสาธารณะ ["pages/schedule.php"]
        Week Table Grid (スอศ. Standard)
        Today & Period Active Highlight
        A4 Landscape Print View
      คณาจารย์และชมรม ["pages/teachers.php"]
        Faculty Directory Cards
        Club Executive Structure
      เข้าสู่ระบบ ["pages/login.php"]
        Argon2id Authentication Form
    ["⚙️ Admin Views (admin/)"]
      แดชบอร์ด ["admin/index.php"]
        System Overview Metrics
        Quick Navigation Links
      จัดการตารางเรียน ["admin/classes.php"]
        Visual DND Scheduler Grid
        Bootstrap Group Select Dropdown
        Slot Editor Modal (CRUD)
        Periods Time Config Modal
      จัดการข่าวสาร ["admin/news.php"]
        News List Table
        Quill Markdown Editor
        Live Viewport Preview Modal
      จัดการแบนเนอร์ ["admin/hero.php"]
        Quill Hero Title Editor
        Live Hero Preview Modal
      จัดการคณาจารย์ ["admin/teachers.php"]
        Faculty Position Swapper
      จัดการกิจกรรม ["admin/activities.php"]
        Academic Calendar Timeline
      จัดการผู้ใช้งาน ["admin/users.php"]
        User Account Grid
      ตั้งค่าระบบ ["admin/settings.php"]
        Database-backed Settings
    ["🔌 Decoupled API (api/)"]
      API ตารางเรียน ["api/admin/classes.php"]
        ajax_update_slot (Drag & Drop)
        ajax_update_special_period
        ajax_get_slot
      API ข่าวสาร ["api/admin/news-category.php"]
        ajax_add_category (Quick Add)
      API คณาจารย์ ["api/admin/teachers.php"]
        reorder (Faculty Drag & Drop)
        swap_head (Department Head)
      API ชมรมวิชาชีพ ["api/admin/club.php"]
        reorder (Officers Drag & Drop)
        swap_head (Club President)
    ["🧩 Shared Renderers (includes/renderers/)"]
      ตัวเรนเดอร์ผังตาราง ["scheduler-table.php"]
        renderSchedulerThead()
        renderSchedulerTbody()
    ["🧠 Domain Services (includes/services/)"]
      AuthService
        Session & Argon2id Verification
      ScheduleService
        Timetable Grid & Period Calculations
      NewsService
        Articles & Category Management
      TeacherService
        Faculty Directory & Sorting
      ActivityService
        Calendar & Events Management
      SettingService
        Key-Value System Settings
    ["🎨 Modular CSS (assets/css/)"]
      Design Tokens ["assets/css/tokens.css"]
        Colors (65/20/10/5 Ratio)
        Typography (Kanit, Prompt, IBM Plex Mono)
        Spacing & Elevation Tokens
      Components ["assets/css/components/"]
        modals.css (Preview Viewports & Dialogs)
        buttons.css
        cards.css
        navigation.css
      Page Styles ["assets/css/pages/"]
        page-schedule.css (Screen Highlights & Print CSS)
        page-news.css (Article Typography & Tight Spacing)
        page-home.css
      Admin Styles ["assets/css/admin/"]
        admin-scheduler.css (Empty Slots & Card Cursors)
        admin-components.css (Quill Line Spacing Tokens)
        admin-table.css
    ["⚡ Vanilla JS Micro-Modules (assets/js/)"]
      admin-scheduler.js
        HTML5 Drag & Drop Engine
        Edge-Resize Snapping
        Slot Click Delegation
      admin-tree-slots.js
        HTML5 Hierarchy Sorting
        Dropzone Head Swapping
      admin-image-upload.js
        Client FileReader Preview
        Dropzone Drag & Drop
      admin-quill.js
        Smart Heading Block Splitting
        Markdown Keystroke Shortcuts
      modal.js
        Accessible Dialog Controller
      main.js
        Theme & Mobile Nav Controller
```

---

## 2. ตารางทะเบียนส่วนประกอบระบบ (Component Registry & Living Map)

| ชื่อส่วนประกอบ (Component)     | ตำแหน่งไฟล์โค้ด (File Location)                                                                                                                  | หน้าที่เรียกใช้งาน (Consumers)                                                                                                                     | สไตล์ชีตควบคุม (CSS)                                                                                           | ไมโครโมดูลควบคุม (JS)                                                                            | API / Domain Service ที่เชื่อมต่อ                                                                |
| :----------------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------- |
| **Public Timetable Grid**      | [`pages/schedule.php:208-292`](file:///c:/xampp/htdocs/web/pages/schedule.php#L208-L292)                                                         | `pages/schedule.php`                                                                                                                               | [`assets/css/pages/page-schedule.css`](file:///c:/xampp/htdocs/web/assets/css/pages/page-schedule.css)         | Native CSS / Print Engine                                                                        | `ScheduleService::getTimetableGrid()`                                                            |
| **Today & Hover Highlight**    | [`assets/css/pages/page-schedule.css:203-275`](file:///c:/xampp/htdocs/web/assets/css/pages/page-schedule.css#L203-L275)                         | `pages/schedule.php`                                                                                                                               | `page-schedule.css` (`.week-table tbody tr:hover td`, `tr.is-today`)                                           | Native CSS Transitions                                                                           | `ScheduleService`                                                                                |
| **A4 Print Layout**            | [`assets/css/pages/page-schedule.css:420-530`](file:///c:/xampp/htdocs/web/assets/css/pages/page-schedule.css#L420-L530)                         | `pages/schedule.php` (Print Trigger)                                                                                                               | `page-schedule.css` (`@media print`)                                                                           | Browser Print Dialog                                                                             | Pure Print CSS                                                                                   |
| **Visual Scheduler Grid**      | [`includes/renderers/scheduler-table.php`](file:///c:/xampp/htdocs/web/includes/renderers/scheduler-table.php)                                   | [`admin/classes.php`](file:///c:/xampp/htdocs/web/admin/classes.php), [`api/admin/classes.php`](file:///c:/xampp/htdocs/web/api/admin/classes.php) | [`assets/css/admin/admin-scheduler.css`](file:///c:/xampp/htdocs/web/assets/css/admin/admin-scheduler.css)     | [`assets/js/admin-scheduler.js`](file:///c:/xampp/htdocs/web/assets/js/admin-scheduler.js)       | `api/admin/classes.php`, `ScheduleService`                                                       |
| **Empty Slot Add Target**      | [`includes/renderers/scheduler-table.php:143-157`](file:///c:/xampp/htdocs/web/includes/renderers/scheduler-table.php#L143-L157)                 | `admin/classes.php`                                                                                                                                | `admin-scheduler.css` (`.admin-slot-empty`, `cursor: pointer`)                                                 | `admin-scheduler.js` (Opens Slot Modal)                                                          | `api/admin/classes.php`                                                                          |
| **Scheduler Group Dropdown**   | [`admin/classes.php:574-587`](file:///c:/xampp/htdocs/web/admin/classes.php#L574-L587)                                                           | `admin/classes.php`                                                                                                                                | Bootstrap 5.3 (`.form-select.form-select-sm`)                                                                  | Native Inline Navigation (`location.href`)                                                       | `ScheduleService::getGroups()`                                                                   |
| **News Live Preview Modal**    | [`admin/news.php:292-343`](file:///c:/xampp/htdocs/web/admin/news.php#L292-L343)                                                                 | `admin/news.php`                                                                                                                                   | [`assets/css/components/modals.css`](file:///c:/xampp/htdocs/web/assets/css/components/modals.css)             | `admin/news.php` (`show.bs.modal` dynamic query)                                                 | Dynamic DOM Sync (Quill & FileReader)                                                            |
| **Quill Rich Editor**          | [`admin/news.php:258`](file:///c:/xampp/htdocs/web/admin/news.php#L258), [`admin/hero.php:235`](file:///c:/xampp/htdocs/web/admin/hero.php#L235) | `admin/news.php`, `admin/hero.php`                                                                                                                 | [`assets/css/admin/admin-components.css`](file:///c:/xampp/htdocs/web/assets/css/admin/admin-components.css)   | [`assets/js/admin-quill.js`](file:///c:/xampp/htdocs/web/assets/js/admin-quill.js)               | Form Post / MySQL `news` / `settings`                                                            |
| **Interactive Image Dropzone** | [`includes/helpers.php`](file:///c:/xampp/htdocs/web/includes/helpers.php)                                                                       | `admin/news.php`, `admin/hero.php`, `admin/teachers.php`                                                                                           | [`assets/css/components/image-upload.css`](file:///c:/xampp/htdocs/web/assets/css/components/image-upload.css) | [`assets/js/admin-image-upload.js`](file:///c:/xampp/htdocs/web/assets/js/admin-image-upload.js) | Multipart Upload (`uploads/`)                                                                    |
| **Quick Category Modal**       | [`admin/news.php:346-408`](file:///c:/xampp/htdocs/web/admin/news.php#L346-L408)                                                                 | `admin/news.php`                                                                                                                                   | `modals.css`                                                                                                   | [`assets/js/modal.js`](file:///c:/xampp/htdocs/web/assets/js/modal.js)                           | [`api/admin/news-category.php`](file:///c:/xampp/htdocs/web/api/admin/news-category.php)         |
| **Faculty Slots & Swapper**    | [`admin/teachers.php:340-410`](file:///c:/xampp/htdocs/web/admin/teachers.php#L340-L410)                                                         | `admin/teachers.php`                                                                                                                               | [`assets/css/admin/admin-components.css`](file:///c:/xampp/htdocs/web/assets/css/admin/admin-components.css)   | [`assets/js/admin-tree-slots.js`](file:///c:/xampp/htdocs/web/assets/js/admin-tree-slots.js)     | [`api/admin/teachers.php`](file:///c:/xampp/htdocs/web/api/admin/teachers.php), `FacultyService` |
| **Club Officers & Swapper**    | [`admin/club.php:330-400`](file:///c:/xampp/htdocs/web/admin/club.php#L330-L400)                                                                 | `admin/club.php`                                                                                                                                   | `admin-components.css`                                                                                         | `admin-tree-slots.js`                                                                            | [`api/admin/club.php`](file:///c:/xampp/htdocs/web/api/admin/club.php), `FacultyService`         |

---

## 3. กฎความสัมพันธ์ระหว่างเลเยอร์ (Architecture Boundary Rules)

1. **Zero SQL in View / Presentation Pages**: หน้าแสดงผล (`pages/` และ `admin/`) ห้ามเขียนคำสั่ง SQL หรือเรียก `q()` โดยตรง ต้องส่งต่อให้ `includes/services/` จัดการเสมอ
2. **Dedicated JSON Micro-Endpoints (`api/`)**: ห้ามฝังโค้ดตอบกลับ JSON (`header('Content-Type: application/json')` และ `exit;`) ไว้ในหน้า Controller ที่แสดงผล HTML ให้แยกเป็นไฟล์ใน `api/` โดยเฉพาะ
3. **Decoupled Renderers (`includes/renderers/`)**: โค้ด HTML Template ส่วนกลางที่ใช้ร่วมกันระหว่างการเรนเดอร์หน้าเว็บปกติกับคำขอ AJAX ให้แยกไว้ใน `includes/renderers/` เพื่อความ DRY และลดขนาดไฟล์ Controller
4. **Token-Driven CSS (`assets/css/tokens.css`)**: ทุกองค์ประกอบต้องอ้างอิง CSS Variables จากโทเค็นส่วนกลาง (`var(--c-navy)`, `var(--c-accent)`, `var(--radius)`) ห้าม Hardcode สีหรือขนาดโดยไม่จำเป็น
