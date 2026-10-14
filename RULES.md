# Digital Business Technology (DBT) — Engineering & Architecture Rules

เอกสารกฎระเบียบและมาตรฐานวิศวกรรมซอฟต์แวร์ (Engineering, Architecture & Clean Code Rules) สำหรับโครงการ **เว็บไซต์และระบบบริหารจัดการข้อมูล แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล**

> **กฎเหล็ก (Golden Rule)**: ทุกการแก้ไข, เพิ่มเติม, หรือรีแฟกเตอร์โค้ดในโปรเจกต์นี้ จะต้องปฏิบัติตามมาตรฐานในเอกสารนี้อย่างเคร่งครัดโดยไม่มีข้อยกเว้น เพื่อรักษาความสะอาด, ความปลอดภัย, ความเร็ว, และความสามารถในการบำรุงรักษาระยะยาว

---

## 1. การแบ่งส่วนสถาปัตยกรรมและโครงสร้างโปรเจกต์ (Project Separation & Structure)

เพื่อขจัดความแออัดของไฟล์ (Zero Clutter) และแบ่งแยกหน้าที่ของแต่ละส่วนอย่างเด็ดขาด โครงสร้างไดเรกทอรีจะต้องเป็นไปตามกฎดังต่อไปนี้:

```
web/
├── index.php                  # Essential Entry: ทางเข้าหลักหน้าแรก (01 / HOME)
├── error.php                  # Essential Handler: จัดการรหัสข้อผิดพลาด (403, 404, 419, 500)
├── .env                       # Environment: การกำหนดค่าสภาพแวดล้อม (ห้าม Commit ข้อมูลลับ)
├── .htaccess                  # Router: กฎ Rewrite เส้นทางคลีน URL สู่ pages/
├── favicon.ico                # Asset: ไอคอนประจำเว็บ
├── STYLE.md                   # Spec: มาตรฐานงานออกแบบ, UI States, โทนสี และฟอนต์
├── PLAN.md                    # Roadmap: แผนงานและสถานะการดำเนินงาน
├── RULES.md                   # Standard: เอกสารกฎวิศวกรรมนี้
│
├── pages/                     # Public Pages Cluster: หน้าสาธารณะทั้งหมด (ห้ามกระจายใน Root)
│   ├── about.php              # 02 / หน้าเกี่ยวกับแผนกวิชา
│   ├── news.php               # 03 / ข่าวสารและประกาศ
│   ├── schedule.php           # 04 / ตารางเรียนและกิจกรรม
│   ├── teachers.php           # 07 / ทำเนียบคณาจารย์และชมรมวิชาชีพ
│   ├── login.php              # 05 / เข้าสู่ระบบเจ้าหน้าที่
│   └── logout.php             # ทำลายเซสชันและออกจากระบบ
│
├── admin/                     # Administration Hub: แผงควบคุมและตัวจัดการหลังบ้าน
│   ├── index.php              # แดชบอร์ดภาพรวมและสรุปสถิติ
│   ├── hero.php               # จัดการแบนเนอร์และข้อมูลประชาสัมพันธ์หน้าแรก
│   ├── news.php               # จัดการข่าวสาร บทความ และหมวดหมู่
│   ├── classes.php            # จัดการคาบเรียนและตารางเรียน (Visual Timetable)
│   ├── activities.php         # จัดการปฏิทินกิจกรรมแผนก
│   ├── teachers.php           # จัดการทำเนียบอาจารย์ (Interactive Tree Slots)
│   ├── club.php               # จัดการสมาชิกชมรมวิชาชีพ (Interactive Tree Slots)
│   └── users.php              # จัดการบัญชีผู้ดูแลระบบ (RBAC)
│
├── assets/                    # Static Assets: ทรัพยากรระบบฝั่ง Client
│   ├── css/
│   │   ├── site.css           # สไตล์ Design System หน้าบ้านทั้งหมด
│   │   └── admin.css          # สไตล์เฉพาะของแผงควบคุมหลังบ้าน
│   ├── js/
│   │   ├── app.js             # สคริปต์โต้ตอบหน้าบ้านทั่วไป
│   │   ├── admin-scheduler.js # สคริปต์โมดูลผังตารางเรียน (Drag-Drop & Resize)
│   │   └── admin-tree-slots.js# สคริปต์โมดูลผังลำดับชั้นอาจารย์และชมรม (Tree Slots)
│   └── favicon/               # ชุด Favicons ครบขนาด
│
├── includes/                  # Core Modules & Bootstrap
│   ├── config.php             # กำหนดค่าคงที่ระบบ, Base URL, Session Setup
│   ├── db.php                 # การเชื่อมต่อฐานข้อมูล PDO Singleton
│   ├── helpers.php            # ฟังก์ชัน Utility ส่วนกลาง (e(), url(), q(), flash())
│   ├── security.php           # เครื่องมือความปลอดภัย (clean_text(), clean_html())
│   ├── header.php             # ส่วนหัวและแถบเมนูหน้าบ้าน
│   ├── footer.php             # ส่วนท้ายและ Bottom Navigation หน้าบ้าน
│   ├── admin-header.php       # ส่วนหัวและเมนูนำทางหลังบ้าน
│   ├── admin-footer.php       # ส่วนท้ายหลังบ้าน
│   └── services/              # Domain Service Layer (ตรรกะทางธุรกิจ 100%)
│       ├── AuthService.php    # การยืนยันตัวตน, สิทธิ์ผู้ใช้, Argon2id Hashing
│       ├── CacheService.php   # ระบบแคชข้อมูลสั้น (TTL 60s–120s)
│       ├── FacultyService.php # การจัดการอาจารย์และชมรม (Tree APIs)
│       ├── NewsService.php    # ข่าวสารและตัวกรองบทความ
│       ├── ScheduleService.php# การคำนวณตารางเรียนมาตรฐาน สอศ.
│       └── SettingService.php # การตั้งค่าระบบ ข้อความ และรูปภาพ Hero
│
├── database/                  # Database Schema & Migrations
│   └── schema.sql             # ตารางฐานข้อมูลและ Seed Data เริ่มต้น
│
└── uploads/                   # User Generated Content (ที่จัดเก็บรูปภาพอัปโหลด)
```

### กฎข้อห้ามเกี่ยวกับโครงสร้าง (Structural Constraints):
1. **Root Directory ต้องสะอาดเสมอ**: ห้ามเพิ่มไฟล์หน้าเพจใหม่ลงใน Root Directory โดยเด็ดขาด ไฟล์เพจสาธารณะต้องอยู่ใน `pages/` และไฟล์จัดการหลังบ้านต้องอยู่ใน `admin/`
2. **ห้ามปะปนโค้ดโปรแกรมใน `uploads/`**: ไดเรกทอรี `uploads/` มีไว้สำหรับไฟล์สื่อ (Images/Files) เท่านั้น ห้ามมีสคริปต์ `.php` ทำงานอยู่ภายใน

---

## 2. ข้อกำหนดการตั้งชื่อมาตรฐานใหม่ (Unified Naming Conventions)

ความสม่ำเสมอในการตั้งชื่อ (Consistency) เป็นหัวใจสำคัญในการลด Cognitive Load ขจัดความสับสนระหว่างภาษา และป้องกันข้อผิดพลาดจากการเดา ทุกไฟล์ คลาส ฟังก์ชัน ตัวแปร และองค์ประกอบในโปรเจกต์ DBT จะต้องปฏิบัติตามมาตรฐานเดียวกันอย่างเคร่งครัด:

### 2.1 ตารางสรุปภาพรวมมาตรฐานการตั้งชื่อ (Master Naming Reference)

| ขอบเขต (Domain) | ประเภทองค์ประกอบ | รูปแบบการตั้งชื่อ | ตัวอย่างที่ถูกต้อง | ตัวอย่างที่ผิด / ห้ามใช้ |
| :--- | :--- | :--- | :--- | :--- |
| **PHP Backend** | Service / Class / Interface | `PascalCase` (ต่อท้ายบทบาท) | `ScheduleService`, `AuthService`, `CacheService` | `schedule_service`, `scheduleService`, `CSchedule` |
| | Class Method | `camelCase` (กริยา + นาม) | `getTimetableGrid()`, `attemptLogin()`, `deleteSlot()` | `get_timetable_grid()`, `DeleteSlot()`, `slot_del()` |
| | Global Helper Function | `snake_case` (สั้นกระชับ) | `clean_text()`, `csrf_field()`, `thai_date()`, `url()`, `e()` | `cleanText()`, `CSRF_Field()`, `get_url()` |
| | Local Variable (App Logic) | `camelCase` (สื่อความหมาย) | `$currentUser`, `$selectedGroup`, `$daySlots`, `$slotCount` | `$current_user`, `$sel_grp`, `$d`, `$s`, `$data1` |
| | Constant & Environment | `SCREAMING_SNAKE_CASE` | `AUTH_PASSWORD_ALGO`, `CACHE_DEFAULT_TTL`, `SITE_NAME` | `auth_password_algo`, `siteName`, `Cache_Ttl` |
| **Database & SQL**| Table Name | `snake_case` (พหูพจน์) | `users`, `schedule_slots`, `activities`, `news` | `User`, `scheduleSlot`, `tbl_news`, `class_slot` |
| | Primary Key Column | `id` (เสมอ) | `id` | `slot_id`, `user_id`, `ID` |
| | Foreign Key Column | `snake_case` (`{table_singular}_id`) | `user_id`, `category_id`, `parent_slot_id` | `userId`, `ref_user`, `fk_user` |
| | Boolean Column | `is_{state}` หรือ `has_{state}` | `is_active`, `is_published`, `is_pinned`, `has_attachment` | `active`, `status`, `publish_flag`, `pinned` |
| | Timestamp Column | `{action}_at` (DATETIME) | `created_at`, `updated_at`, `published_at`, `deleted_at` | `create_date`, `updateTime`, `timestamp` |
| | Date Column | `{action}_date` (DATE) | `activity_date`, `start_date`, `end_date` | `date`, `eventDay`, `d_start` |
| | Index / Constraint | `idx_{table}_{column}` / `uniq_` | `idx_schedule_slots_class_group`, `uniq_users_username` | `index1`, `user_idx`, `GroupIndex` |
| **Files & Dirs** | Public Page (Route) | `kebab-case.php` (ใน `pages/`) | `about.php`, `news.php`, `schedule.php`, `teachers.php` | `about_us.php`, `ClassSchedule.php`, `viewNews.php` |
| | Admin Controller | `kebab-case.php` (ใน `admin/`) | `classes.php`, `hero.php`, `activities.php`, `users.php` | `manage_classes.php`, `AdminHero.php` |
| | Service File | `PascalCase.php` (ใน `services/`) | `ScheduleService.php`, `CacheService.php`, `AuthService.php` | `schedule_service.php`, `service.schedule.php` |
| | Partials / Includes | `kebab-case.php` (ใน `includes/`) | `header.php`, `footer.php`, `admin-header.php` | `Header.php`, `admin_header.php`, `inc_footer.php` |
| | Client JS Module | `kebab-case.js` (ใน `assets/js/`)| `app.js`, `admin-scheduler.js`, `admin-tree-slots.js` | `AdminScheduler.js`, `admin_slots.js`, `mainScript.js` |
| | CSS Stylesheet | `kebab-case.css` (ใน `assets/css/`)| `site.css`, `admin.css` | `SiteStyle.css`, `admin_theme.css` |
| | Media / Assets | `kebab-case.{ext}` | `dbt-logo.png`, `hero-cover.webp`, `default-avatar.svg` | `DBT_Logo.PNG`, `hero cover.jpg`, `IMG_001.webp` |
| **HTML & DOM** | Element ID | `kebab-case` (`{context}-{element}`)| `id="modal-slot-editor"`, `id="select-class-group"`, `id="clock-live"`| `id="slotModal"`, `id="group_select"`, `id="myDiv"` |
| | Data Attribute | `kebab-case` (`data-{scope}-{name}`)| `data-slot-id="12"`, `data-day-num="1"`, `data-csrf="..."` | `data-slotId`, `data-day_num`, `data-CSRF` |
| | Form Input Name | `snake_case` (ตรงกับคอลัมน์ DB) | `name="class_group"`, `name="start_period"`, `name="subject_code"` | `name="classGroup"`, `name="startPeriod"`, `name="subCode"` |
| **CSS & Design** | Component Block | `kebab-case` | `.admin-card`, `.timetable-grid`, `.slot-cell`, `.btn-action` | `.adminCard`, `.slot_cell`, `.btnAction` |
| | Modifier / Variant | `.{block}--{variant}` | `.admin-card--compact`, `.btn-action--danger`, `.slot-cell--break` | `.compact-card`, `.btn-red`, `.slot_break` |
| | UI State Class | `.is-{state}` หรือ `.has-{state}` | `.is-active`, `.is-current-class`, `.is-loading`, `.has-conflict` | `.active`, `.current`, `.loading`, `.errorState` |
| | Design Token Variable | `--{category}-{name}` | `--c-navy`, `--c-accent`, `--radius-sm`, `--fs-h1`, `--space-4` | `--colorNavy`, `--main_radius`, `--fontSize1` |
| **JavaScript** | JS Variable & Function | `camelCase` (สื่อความหมาย) | `currentClassGroup`, `fetchSlotData()`, `renderTimetableGrid()` | `current_group`, `$group`, `FetchData()`, `do_work()` |
| | DOM Element Variable | `{name}El` หรือ `{name}Node` | `modalEl`, `submitBtnEl`, `formEl`, `toastRootEl` | `modal`, `btn`, `form_element`, `$el` |
| | Event Handler Function | `handle{Action}{Target}` | `handleSaveSlotSubmit()`, `handleGroupFilterChange()` | `save_click()`, `on_change_group()`, `doSubmit()` |
| | JS Class / Controller | `PascalCase` | `AdminScheduler`, `TreeSlotsController` | `adminScheduler`, `tree_slots_manager` |
| | Custom DOM Event | `dbt:{component}:{action}` | `dbt:slot:saved`, `dbt:slot:deleted`, `dbt:cache:cleared` | `slot_updated`, `customEvent1` |
| **Network & API** | Public Clean URL | `kebab-case` | `/web/about`, `/web/schedule`, `/web/news`, `/web/teachers` | `/web/about_us`, `/web/classSchedule`, `/web/News` |
| | Query Parameter | `snake_case` | `?class_group=dbt-1&day_of_week=1&sort_by=published_at` | `?classGroup=dbt-1&dayOfWeek=1&sortBy=date` |
| | JSON API Response Key | `snake_case` | `{"success": true, "slot_id": 42, "error_msg": null}` | `{"Success": true, "slotId": 42, "errorMsg": null}` |
| | Flash Session Key | `flash_{type}` | `flash_success`, `flash_error`, `flash_warning`, `flash_info` | `success_msg`, `error`, `flashMsg` |
| | Cache Engine Key | `snake_case` (`{scope}_{id}_{var}`)| `schedule_grid_{group}`, `news_list_all`, `faculty_tree_all` | `cache_ScheduleGrid`, `NEWS_LIST`, `temp1` |

---

### 2.2 กฎความสอดคล้องระดับเชิงลึก (In-Depth Consistency Rules)

#### ก) กฎการเชื่อมโยงระหว่าง Form Input -> Database Column -> SQL Query
เพื่อให้ข้อมูลไหลลื่นโดยไม่ต้องแปลงชื่อไปมา:
1. ชื่อแอตทริบิวต์ `name="..."` ในฟอร์ม HTML จะต้องเป็น `snake_case` และ **ตรงกับชื่อคอลัมน์ในตารางฐานข้อมูลเสมอ**
2. เมื่อรับค่าใน PHP Controller สามารถดึงค่าเข้า Array และส่งต่อให้ Service ได้ทันที:
   ```html
   <!-- ✅ HTML Form: ใช้ snake_case ตรงกับ Database -->
   <input type="text" name="subject_code" class="form-input" id="input-subject-code">
   <input type="text" name="subject_name" class="form-input" id="input-subject-name">
   ```
   ```php
   // ✅ PHP Controller: สอดคล้อง 100%
   $slotData = [
       'subject_code' => clean_text($_POST['subject_code'] ?? ''),
       'subject_name' => clean_text($_POST['subject_name'] ?? ''),
   ];
   ScheduleService::saveSlot($slotData);
   ```

#### ข) กฎการตั้งชื่อตัวแปร DOM Element ใน JavaScript
ในสคริปต์ [assets/js/*.js](file:///c:/xampp/htdocs/web/assets/js/) ต้องระบุประเภทของตัวแปรที่เก็บ Element ไว้อย่างชัดเจนด้วย Suffix `El` เพื่อแยกความแตกต่างจากค่า Primitive อย่างเด็ดขาด:
```javascript
// ✅ วิธีที่ถูกต้อง: ชัดเจนระหว่าง Data กับ DOM Element
const classGroupSelectEl = document.getElementById('select-class-group');
const scheduleGridContainerEl = document.getElementById('grid-timetable-container');
const submitBtnEl = document.getElementById('btn-submit-slot');

const selectedGroup = classGroupSelectEl.value; // string value
const isSubmitting = false;                     // boolean state
```

#### ค) กฎการขึ้นต้นชื่อเมธอดใน Service Layer (Action Verb Prefixes)
เมธอดในคลาส Service ต้องขึ้นต้นด้วยคำกริยาที่สื่อเจตนาชัดเจน:
- **`get...()`**: ดึงข้อมูลชุดที่คาดหวังว่าจะพบเสมอ หรือคืนค่าค่าว่าง/Array ว่าง
- **`find...()`**: ค้นหาข้อมูลรายชิ้นด้วย Identifier (เช่น ID) หากไม่พบให้คืนค่า `null`
- **`create...()` / `save...()`**: บันทึกข้อมูลรายการใหม่
- **`update...()`**: ปรับปรุงข้อมูลรายการที่มีอยู่เดิม
- **`delete...()`**: ลบข้อมูลออกจากฐานข้อมูล
- **`has...()` / `is...()`**: เมธอดตรวจสอบสถานะที่คืนค่า `bool` เท่านั้น
- **`reorder...()`**: เมธอดจัดลำดับแถวข้อมูล (Sort Order)

---

## 3. กฎการเขียนโค้ดสะอาด (Clean Code Rules)

ยึดตามหลักการของ Robert C. Martin (Clean Code) และหลักการลูกเสือ (The Boy Scout Rule):

### 3.1 The Boy Scout Rule
> *"จงทิ้งแคมป์ให้สะอาดกว่าตอนที่คุณมาถึงเสมอ"*  
> ทุกครั้งที่เปิดไฟล์ใดขึ้นมาแก้ไข หากพบตัวแปรที่ตั้งชื่อคลุมเครือ, โค้ดที่ซ้ำซ้อน (Duplication), หรือคอมเมนต์ที่ล้าสมัย จงรีแฟกเตอร์จุดนั้นให้สะอาดยิ่งขึ้นทันที

### 3.2 DRY (Don't Repeat Yourself)
- ห้ามเขียนตรรกะหรือบล็อกมาร์กอัป HTML เดียวกันซ้ำซ้อนในหลายจุด
- ตรรกะทางธุรกิจที่ใช้ซ้ำต้องถูกดึงเข้าสู่ Service Layer ใน `includes/services/`
- ส่วนประกอบ UI ที่ใช้ซ้ำต้องเรียกใช้ผ่าน Helper หรือ Template Partial

### 3.3 ขนาดของฟังก์ชันและพารามิเตอร์ (Functions & Arguments)
- **ทำหน้าที่เพียงอย่างเดียว (Single Responsibility)**: ฟังก์ชันหนึ่งต้องทำงานให้สำเร็จเพียงเป้าหมายเดียว
- **จำนวนพารามิเตอร์สูงสุดไม่เกิน 3 ตัว**: หากต้องการรับค่ามากกว่า 3 ค่า ให้รวมเป็น Associative Array หรือ Data Transfer Object (DTO)
- **ความยาวของฟังก์ชัน**: ไม่ควรยาวเกิน 30–40 บรรทัด หากยาวเกินไปต้องแยกตรรกะย่อยออกเป็นฟังก์ชันสนับสนุน

### 3.4 ห้ามใช้ Magic Numbers และ Magic Strings
- ตัวเลขที่มีความหมายเฉพาะ (เช่น เวลา TTL 60/120 วินาที, รหัสวันในสัปดาห์ 1-5, สิทธิ์ผู้ใช้ `admin`/`editor`) ต้องถูกประกาศเป็นค่าคงที่ (Constants) ที่สื่อความหมายชัดเจน ห้ามใส่ตัวเลขหรือสตริงลอยๆ ลงในโค้ด

### 3.5 การตั้งชื่อที่เปิดเผยเจตนา (Intent-Revealing Names)
- ตั้งชื่อตัวแปรและฟังก์ชันให้บอกว่า *มันคืออะไร*, *ทำไมมันถึงอยู่ที่นี่*, และ *ใช้อย่างไร*
- ❌ **ห้าม**: `$d`, `$val`, `$temp`, `$arr2`, `$s` (ยกเว้นตัวแปรลูปสั้นๆ `$i`)
- ✅ **ใช้**: `$currentDayNum`, `$selectedClassGroup`, `$upcomingActivities`, `$targetSlotId`

### 3.6 กำจัดโค้ดที่ตายแล้ว (Zero Dead Code)
- ห้ามคอมเมนต์โค้ดเก่าทิ้งไว้ในไฟล์ ("กันเหนียว") หากไม่ใช้แล้วให้ลบทิ้งทันที Git มีประวัติย้อนหลังบันทึกไว้อยู่แล้ว
- ห้ามมีฟังก์ชันที่ไม่มีการเรียกใช้งาน (Unused Functions) หรือตัวแปรที่สร้างมาแต่ไม่ได้อ่านค่า

### 3.7 การคืนค่าล่วงหน้า (Guard Clauses & Early Returns)
- หลีกเลี่ยงการเขียนโค้ดซ้อน `if-else` หลายชั้น (Arrow Anti-Pattern หรือ Nested Hell)
- ตรวจสอบเงื่อนไขผิดพลาดหรือกรณีพิเศษแล้วคืนค่าออกไปทันที (Return Early / Exit Early):
  ```php
  // ✅ วิธีที่ถูกต้อง: Guard Clauses คืนค่าทันทีเมื่อข้อมูลไม่ถูกต้อง
  public static function updateActivity(int $id, array $data): bool {
      if ($id <= 0) {
          return false;
      }
      if (empty($data['title'])) {
          return false;
      }

      // ดำเนินการตรรกะหลักได้อย่างราบรื่น ไม่ต้องครอบ if หลายชั้น
      return self::persistActivity($id, $data);
  }
  ```

---

## 4. การแยกความรับผิดชอบอย่างเด็ดขาด (Separation of Concerns: SoC)

ระบบ DBT ห้ามสร้าง **"God Component"** (ไฟล์เดียวทำทุกอย่าง ทั้งคิวรีฐานข้อมูล, จัดการฟอร์ม, เรนเดอร์ HTML, และฝังสคริปต์ JS ยาวเหยียด):

```
┌────────────────────────────────────────────────────────────────────────┐
│                     SEPARATION OF CONCERNS (SoC)                       │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│ 1. PRESENTATION     │ 2. CONTROLLER      │ 3. DOMAIN SERVICES (DATA)   │
│ (View / Client)     │ (HTTP & Routing)   │ (Business Logic & Storage)  │
├─────────────────────┼────────────────────┼─────────────────────────────┤
│ • pages/*.php       │ • admin/*.php      │ • includes/services/*.php   │
│ • assets/css/*.css  │ • รับ Request POST  │ • คำนวณตารางเรียน           │
│ • assets/js/*.js    │ • ตรวจ CSRF Token  │ • คิวรีฐานข้อมูล SQL (q())   │
│ • ไม่มี SQL เด็ดขาด │ • เรียก Service    │ • ตรวจสอบสิทธิ์ (AuthService)│
│ • สร้าง DOM ปลอดภัย │ • Flash & Redirect │ • จัดการแคช (CacheService)  │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

### 4.1 กฎเหล็ก: ห้ามฝัง JavaScript ขนาดยาวในไฟล์ PHP (No Inline Script Spaghetti)
- **ห้ามฝังสคริปต์เกิน 5 บรรทัดในไฟล์ PHP**: สคริปต์ฝั่ง Client ที่จัดการตรรกะซับซ้อน (เช่น Drag & Drop, การจัดตาราง, การคำนวณ) ต้องถูกแยกเป็นไฟล์ `.js` ภายนอกใน `assets/js/` เสมอ
- ไฟล์ PHP มีหน้าที่เพียงส่งข้อมูลตั้งต้นผ่าน Data Attributes (เช่น `data-csrf="..."`, `data-group="..."`) ให้กับสคริปต์ภายนอกเท่านั้น

### 4.2 กฎเหล็ก: ห้ามฉีด HTML ผ่านสตริงดิบ (Zero Raw HTML String Injection)
- **ห้ามใช้ `innerHTML = '<div>' + unsafeString + '</div>'`**: การต่อสตริง HTML ใน JavaScript เสี่ยงต่อช่องโหว่ DOM XSS และยากต่อการบำรุงรักษา
- **สร้างส่วนประกอบ DOM อย่างปลอดภัย**: ต้องใช้ Native DOM Construction เสมอ:
  ```javascript
  // ✅ วิธีที่ถูกต้อง: สร้าง DOM Node ปลอดภัย
  const card = document.createElement('div');
  card.className = 'admin-slot-card';
  card.setAttribute('data-slot-id', slotId);

  const title = document.createElement('span');
  title.className = 'subject-name';
  title.textContent = subjectName; // ป้องกัน XSS อัตโนมัติ

  card.appendChild(title);
  container.appendChild(card);
  ```

### 4.3 กฎเหล็ก: ห้ามเขียนคำสั่ง SQL ใน View หรือ Page Template
- คำสั่ง SQL และคำสั่งเรียกใช้ `q()` ต้องอยู่ภายใน `includes/services/*.php` เท่านั้น
- หน้าเพจใน `pages/` และคอนโทรลเลอร์ใน `admin/` ต้องเรียกข้อมูลผ่าน Method ของ Service เท่านั้น เช่น:
  ```php
  // ✅ ถูกต้อง: เรียกผ่าน Service
  $grid = ScheduleService::getTimetableGrid($selectedGroup);

  // ❌ ผิด: เขียน SQL ปะปนในหน้า View
  $slots = q('SELECT * FROM schedule_slots WHERE ...')->fetchAll();
  ```

---

## 5. ระดับนามธรรมและความเรียบง่าย (SLAP & SLC)

### 5.1 SLAP (Single Level of Abstraction Principle)
- โค้ดในแต่ละฟังก์ชันต้องอยู่บนระดับนามธรรมเดียวกัน
- ฟังก์ชันระดับสูง (High-level Orchestrator) มีหน้าที่สั่งงานฟังก์ชันย่อยเป็นลำดับ ไม่ลงไปคลุกคลีกับรายละเอียดการแปลงสตริงระดับล่าง
- **ตัวอย่าง**:
  ```php
  // ✅ ถูกต้อง: แต่ละขั้นตอนอยู่ในระดับนามธรรมเดียวกัน
  public static function updateSlotSchedule(array $data): void {
      self::validateSlotData($data);
      self::checkTimeConflict($data);
      self::persistSlot($data);
      CacheService::forget('schedule_grid_' . $data['class_group']);
  }
  ```

### 5.2 SLC (Simple, Lovable, Complete) & Ponytail Tenet
- **ไม่สร้างนามธรรมเกินจำเป็น (No Speculative Over-engineering / YAGNI)**: ห้ามสร้าง Interface ที่มีการ Implement เพียงคลาสเดียว หรือสร้าง Factory ที่สร้างวัตถุได้ชนิดเดียว
- **ใช้ฟีเจอร์พื้นฐานของแพลตฟอร์มก่อนเสมอ (Native Platform First)**: ใช้เครื่องมือมาตรฐานของ PHP และ Web API ดั้งเดิม (Vanilla JS, CSS Variables, HTML5 Dialog/Drag-Drop) ก่อนคิดจะดึงไลบรารีภายนอกมาใช้
- **สมบูรณ์ ประณีต และใช้งานได้จริง (Complete & Lovable)**: สิ่งที่ส่งมอบต้องทำงานได้ครบถ้วน สวยงามตาม [STYLE.md](file:///c:/xampp/htdocs/web/STYLE.md) ไม่สร้างผลงานแบบครึ่งๆ กลางๆ

---

## 6. กฎประสิทธิภาพและความเร็ว (Performance & Scalability Rules)

### 6.1 นโยบายระบบแคชสั้น (Cache Rules: 60s–120s TTL)
1. **อายุแคช 1–2 นาที**: ข้อมูลสาธารณะที่ถูกเรียกบ่อยจะถูกแคชผ่าน `CacheService` ด้วยอายุ 60 ถึง 120 วินาที เพื่อตอบสนองผู้ใช้ได้ในระดับไม่กี่มิลลิวินาที
2. **Instant Cache Invalidation (Cache Busting)**: เมื่อมีการกดบันทึก แก้ไข หรือลบข้อมูลใน `admin/` เมธอดใน Service จะต้องสั่งล้างคีย์แคชที่เกี่ยวข้องทันที ห้ามปล่อยให้แอดมินเห็นข้อมูลเก่า
3. **HTTP Header Caching**:
   - หน้าสาธารณะ: ส่ง Header `Cache-Control: public, max-age=60, s-maxage=120, stale-while-revalidate=30`
   - หน้าหลังบ้าน: ส่ง Header `Cache-Control: no-store, no-cache, must-revalidate, private`

### 6.2 ประสิทธิภาพการคิวรีฐานข้อมูล (Database Query Rules)
- **Prepared Statements 100%**: ทุกคิวรีต้องส่งตัวแปรผ่าน Parameter Binding ของ PDO เสมอ
- **ดัชนีคอลัมน์ (Indexes)**: คอลัมน์ที่ใช้เป็นเงื่อนไข `WHERE`, `JOIN` หรือ `ORDER BY` บ่อยครั้ง (เช่น `class_group`, `day_of_week`, `published_at`, `sort_order`) ต้องมีการทำ Index ในฐานข้อมูล
- **ป้องกันปัญหา N+1 Query**: ดึงข้อมูลที่เกี่ยวข้องมาให้ครบในคิวรีเดียว (JOIN หรือ IN query) แทนการวนลูปส่งคิวรีทีละแถว

### 6.3 ประสิทธิภาพฝั่ง Client และ DOM Rendering
- **Batch DOM Operations**: เมื่อต้องการเพิ่มการ์ดหรือแถวตารางจำนวนมาก ให้ประกอบชิ้นส่วนลงใน `DocumentFragment` ก่อน แล้วจึง `appendChild` เข้าสู่ DOM จริงเพียงครั้งเดียว เพื่อป้องกัน Browser Reflow / Layout Thrashing
- **Debounced Event Listeners**: การโต้ตอบที่เกิดถี่ เช่น การพิมพ์ค้นหา, การลากปรับขนาด (Resize) หรือการเลื่อนจอ ต้องผ่านการ Debounce / Throttle เพื่อไม่ให้โหลดเครื่องผู้ใช้
- **Image Optimization**: รูปภาพต้องระบุ `loading="lazy"` เสมอ และมีสัดส่วนแน่นอน (Aspect Ratio) เพื่อป้องกัน Cumulative Layout Shift (CLS)

---

## 7. กฎความปลอดภัยและการเข้าถึง (Security & Accessibility Non-Negotiables)

ความปลอดภัยและการเข้าถึงเป็นข้อกำหนดที่ต้องผ่าน 100% เสมอ:

1. **การเข้ารหัสรหัสผ่าน (Password Hashing: Argon2id)**:
   - ใช้ `PASSWORD_ARGON2ID` เป็นมาตรฐานสูงสุดในระบบ
   - มีระบบตรวจสอบ `password_needs_rehash()` ตอนล็อกอิน เพื่ออัปเกรดรหัสผ่านเดิมเป็น Argon2id โดยอัตโนมัติ
   - ห้ามแสดงข้อความเปิดเผยรหัสผ่านทดสอบในหน้าจอใดๆ
2. **การป้องกัน XSS (Output Escaping & Whitelist Sanitization)**:
   - ทุกตัวแปรที่นำออกแสดงผลใน HTML ต้องผ่าน `e()`: `<?= e($val) ?>`
   - ข้อความเนื้อหาที่มีแท็ก HTML ต้องผ่าน `clean_html()` ซึ่งใช้ระบบ Whitelist ของ DOMDocument ห้ามปล่อยสตริงดิบออกสู่เบราว์เซอร์
3. **การป้องกัน CSRF (Cross-Site Request Forgery)**:
   - ทุกฟอร์ม POST และการส่ง AJAX POST/DELETE ต้องมี CSRF Token กำกับและตรวจสอบด้วย `verify_csrf()` เสมอ
4. **การเข้าถึงตามมาตรฐาน WCAG AA (Accessibility)**:
   - คอนทราสต์ของสีข้อความและพื้นหลังต้องผ่านเกณฑ์ >= 4.5:1
   - ตัวควบคุมปุ่มกดต้องมีเส้นวงแหวนโฟกัส `:focus-visible` ชัดเจน
   - ปุ่มสัมผัสบนมือถือต้องมีขนาดไม่ต่ำกว่า `44px x 44px`

---

## 8. ขั้นตอนและระเบียบปฏิบัติในการรีแฟกเตอร์ระบบ (Refactoring Protocol & Migration Rules)

การรีแฟกเตอร์ระบบจะต้องดำเนินการอย่างเป็นระบบ ปราศจากความเสี่ยง และไม่ส่งผลกระทบต่อข้อมูลจริงในระบบ โดยมีระเบียบปฏิบัติ 6 ขั้นตอนดังนี้:

```
┌────────────────────────────────────────────────────────────────────────┐
│                  REFACTORING PROTOCOL (6-STEP PIPELINE)                │
├────────────────────────────────────────────────────────────────────────┤
│ 1. ANALYZE & MAP       → ระบุจุดที่เป็น God Component / หนี้ทางเทคนิค    │
│ 2. EXTRACT SERVICES    → ย้าย SQL & Business Logic สู่ includes/services │
│ 3. DECOUPLE CLIENT JS  → ย้าย Inline JS สู่ assets/js/ (Safe DOM APIs) │
│ 4. APPLY STYLE GUIDE   → ปรับแต่ง UI ตาม STYLE.md (Minimal, Brutalism)  │
│ 5. ATTACH CACHE & PERF → เชื่อมต่อ CacheService (60-120s) & Busting   │
│ 6. VERIFY & BENCHMARK  → รัน php -l, เช็ค Console, ตรวจสอบ Responsive │
└────────────────────────────────────────────────────────────────────────┘
```

### 8.1 การขจัด God Component (Dismantling God Components)
ตัวอย่างรูปธรรม: ไฟล์ [admin/classes.php](file:///c:/xampp/htdocs/web/admin/classes.php) (1,528 บรรทัด)
1. **แยกส่วน Controller**: ให้ไฟล์ `admin/classes.php` รับผิดชอบเฉพาะการตรวจสอบสิทธิ์แอดมิน, รับพารามิเตอร์ URL, ตรวจสอบ CSRF เมื่อมี POST, และส่งต่อข้อมูลไปยัง Service
2. **แยกส่วน Service**: ดึงฟังก์ชันการดึงข้อมูลตาราง, การคำนวณคาบเรียนที่ทับซ้อน, และการบันทึกลงฐานข้อมูลไปไว้ใน `ScheduleService.php`
3. **แยกส่วน JavaScript**: ดึงโค้ด JavaScript กว่า 700 บรรทัดไปไว้ที่ [assets/js/admin-scheduler.js](file:///c:/xampp/htdocs/web/assets/js/admin-scheduler.js)
4. **เปลี่ยนการสร้าง DOM**: แปลงโค้ดที่เคยใช้ `innerHTML = '...'` เป็นฟังก์ชันปลอดภัยที่ใช้ `document.createElement()`, `appendChild()`, และ `textContent`
5. **แปลงสไตล์**: ปรับเปลี่ยน CSS ให้อิงตาม Token ตัวแปรใน `admin.css` และ [STYLE.md](file:///c:/xampp/htdocs/web/STYLE.md)

### 8.2 การย้ายไฟล์หน้าเว็บเข้า `pages/` (Strangler & Clean Routing Protocol)
เพื่อขจัดความแออัดของไฟล์ใน Root Directory ให้ดำเนินตามขั้นตอนนี้:
1. สร้างไดเรกทอรี `pages/`
2. ย้ายไฟล์หน้าเว็บสาธารณะ: `about.php`, `news.php`, `schedule.php`, `teachers.php`, `login.php`, `logout.php` เข้าสู่ `pages/`
3. ปรับแต่งไฟล์ `.htaccess` เพื่อรักษาความต่อเนื่องของเส้นทาง URL เดิม (Clean URLs) โดยที่ผู้ใช้งานภายนอกไม่รู้สึกถึงความเปลี่ยนแปลง
4. ตรวจสอบการอ้างอิง `include` / `require_once` ในไฟล์ที่ถูกย้าย เพื่อให้ชี้ไปยัง `../includes/config.php` อย่างแม่นยำ

### 8.3 การอัปเกรดรหัสผ่านสู่ Argon2id แบบไร้รอยต่อ (Zero-Downtime Security Migration)
1. กำหนดค่าคงที่ `AUTH_PASSWORD_ALGO` ใน `AuthService.php`
2. ในกระบวนการ `attemptLogin()` เมื่อผู้ใช้กรอกรหัสผ่านถูกต้อง ให้เรียกใช้ฟังก์ชันมาตรฐาน `password_needs_rehash($hash, AUTH_PASSWORD_ALGO)`
3. หากระบบตรวจพบว่ารหัสผ่านยังเป็นรูปแบบเดิม (เช่น Bcrypt) ให้ทำการอัปเดตแฮชใหม่ด้วย Argon2id ลงในฐานข้อมูลทันที โดยผู้ใช้ไม่ต้องเปลี่ยนรหัสผ่านเอง
4. ลบข้อความตัวอย่างรหัสผ่านทดสอบในหน้าจอ [login.php](file:///c:/xampp/htdocs/web/login.php) ออกทั้งหมด เพื่อความปลอดภัยขั้นสูงสุด

### 8.4 การตรวจสอบความถูกต้องทุกขั้นตอน (Quality Verification Protocol)
หลังจากการรีแฟกเตอร์แต่ละไฟล์หรือโมดูล จะต้องผ่านการตรวจสอบดังนี้:
1. **PHP Syntax Check**: รันคำสั่ง `php -l <path-to-file>` เพื่อตรวจสอบว่าไม่มี Syntax Error
2. **Browser Console Check**: เปิด Console ใน Developer Tools เพื่อยืนยันว่าไม่มี Uncaught TypeError หรือ SyntaxError ใดๆ
3. **Responsive Audit**: ตรวจสอบการแสดงผลบนขนาดหน้าจอทั้ง 3 ระดับ:
   - Mobile View: 375px / 390px / 414px (ต้องแสดงผลเป็นแนวตั้งเดี่ยว, เมนูด้านล่าง, ฟอนต์และปุ่มกดขนาดเหมาะสม)
   - Tablet View: 768px / 820px / 900px (ต้องแสดงผล 2 คอลัมน์, เมนูนำทางบนไม่ขึ้นบรรทัดใหม่, ตารางเรียนรองรับ Touch-scroll)
   - Desktop View: 1024px / 1280px / 1440px (แสดงผลเต็มรูปแบบตาม Editorial Grid)
4. **Print Layout Verification**: ตรวจสอบหน้า [schedule.php](file:///c:/xampp/htdocs/web/schedule.php) ด้วยโหมดจำลองการพิมพ์ (Print Simulation) ต้องจัดพิมพ์ลงกระดาษแนวนอน A4 พอดี 100% กว้างเต็มหน้า และไม่ถูกตัดขาดครึ่งตาราง

---

## 9. กฎการตรวจสอบคุณภาพก่อนเสร็จสิ้นงาน (Definition of Done: DoD)

โค้ดจะถือว่าเสร็จสมบูรณ์ (Done) ได้ก็ต่อเมื่อผ่านการตรวจสอบครบทุกข้อ:
- [ ] โค้ดถูกจัดเก็บในโครงสร้างไดเรกทอรีที่ถูกต้อง (Root มีเฉพาะไฟล์จำเป็น, หน้าเว็บอยู่ใน `pages/`)
- [ ] การตั้งชื่อตัวแปร, ฟังก์ชัน, คลาส, และไฟล์ตรงตามข้อกำหนด Naming Conventions
- [ ] ตรรกะทางธุรกิจถูกแยกอยู่ใน Service Layer และไม่มีคำสั่ง SQL ใน View
- [ ] โค้ด JavaScript ถูกแยกเป็นไฟล์โมดูลภายนอก และสร้าง DOM ด้วย `document.createElement` (ไม่มี `innerHTML` injection)
- [ ] มีการเชื่อมต่อระบบแคช `CacheService` พร้อมกลไกล้างแคชทันทีเมื่อมีการอัปเดต
- [ ] หน้าเว็บแสดงผลได้สมบูรณ์บนทั้ง Mobile (< 768px), Tablet (768–1023px), และ Desktop (>= 1024px)
- [ ] หน้าตารางเรียน [schedule.php](file:///c:/xampp/htdocs/web/schedule.php) สั่งพิมพ์เอกสารแนวนอน A4 ออกมาสวยงาม ไม่ขาดครึ่งหน้า
- [ ] ไม่เหลือ Dead Code, คอมเมนต์รกรุงรัง, หรือ Console Error ในเบราว์เซอร์

---
*เอกสารนี้จัดทำขึ้นเพื่อใช้เป็นกฎเหล็กอ้างอิงสูงสุดในการพัฒนาระบบ Digital Business Technology (DBT)*
