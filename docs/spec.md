# Specification: เว็บไซต์และระบบจัดการข้อมูล สาขาเทคโนโลยีธุรกิจดิจิทัล (Digital Business Technology)

## Problem Statement

นักเรียน นักศึกษา อาจารย์ ผู้ปกครอง และผู้สนใจภายนอก ต้องการเข้าถึงข้อมูลที่สำคัญของสาขาวิชาเทคโนโลยีธุรกิจดิจิทัล (เช่น ข้อมูลหลักสูตร ข่าวสาร ประกาศ ตารางเรียน ตารางกิจกรรม และข้อมูลคณาจารย์) ผ่านอุปกรณ์สมาร์ตโฟนเป็นหลัก แต่เว็บไซต์ของหน่วยงานทางการศึกษาส่วนใหญ่มักออกแบบมาจากมุมมองของหน้าจอ Desktop แล้วย่อขนาดลงมา ส่งผลให้ตัวหนังสือเล็กเกินไป ตารางเรียนกว้างล้นจอจนต้องเลื่อนซ้ายขวา และโครงสร้างเมนูเข้าถึงยากด้วยมือเดียว

นอกจากนี้ เว็บไซต์ด้านเทคโนโลยีในปัจจุบันมักตกอยู่ในกับดักการใช้ภาพลักษณ์แบบ AI-generated หรือเทมเพลตสำเร็จรูป (เช่น โทนสี Cyberpunk, แสงนีออน, วัตถุสามมิติลอย, ภาพกราฟิกหุ่นยนต์/สมองดิจิทัล) ซึ่งดูแปลกแยก ไม่มีความเป็นมนุษย์ และไม่สะท้อนเอกลักษณ์ของการศึกษาและการทำงานในโลกธุรกิจจริง ขณะเดียวกัน ผู้ดูแลระบบของสาขาก็ต้องการระบบจัดการหลังบ้านที่ใช้งานง่าย สะดวกบนหน้าจอมือถือ ไม่ติดขัดกับป๊อปอัปแจ้งเตือนดั้งเดิมของเบราว์เซอร์ และปรับปรุงข้อมูลประจำวันได้อย่างรวดเร็ว

## Solution

ออกแบบและพัฒนาระบบเว็บไซต์และระบบบริหารจัดการข้อมูลของสาขาวิชาเทคโนโลยีธุรกิจดิจิทัลด้วยแนวคิด **Mobile-First 100% (เริ่มต้นที่หน้าจอ 390px)** ภายใต้ปรัชญา **“Human-designed Digital Technology” (Refined Editorial Institutional Design)** ที่ผสาน Modern Technology, Contemporary Education, Editorial Graphic Design และ Clean Interface เข้าด้วยกันอย่างลงตัว

ตัวระบบสร้างภาพลักษณ์ความเป็นเทคโนโลยีผ่าน **โครงสร้างของงาน** (ระบบกริด, เส้นคั่นบาง 1px, การจัดวางกลุ่มข้อมูลแบบมีลำดับขั้น, ตัวเลขและ Technical Label สไตล์ Monospace) โดยไม่พึ่งพา Effect ฉูดฉาด ใช้สัดส่วนสีอ่อนเป็นหลัก (ขาว 65%, ฟ้าอ่อน 20%, กรมท่า Deep Navy 10%, ส้ม Accent 5%) ขับเคลื่อนด้วยสถาปัตยกรรมระดับองค์กร:

- **Backend**: PHP 8 ร่วมกับ PDO MySQL Singleton แบบ Prepared Statements 100% พร้อม Domain Service Layer ในการควบคุม Business Logic
- **Security & Caching**: การเข้ารหัสรหัสผ่านขั้นสูงสุดด้วย Argon2id, ระบบ Whitelist HTML Sanitization ด้วย DOMDocument, และระบบ Short-lived Caching (60s–120s TTL) พร้อม Instant Invalidation
- **Frontend Architecture**: Bootstrap 5.3 (Reboot & Grid) ผสาน Modular CSS Design Tokens Architecture (`tokens.css`, `base.css`, `components/`, `pages/`, `admin/`) และ Vanilla JS Micro-Modules ล้วน ปราศจากการพึ่งพา External Library ส่วนเกิน
- **Administration**: ครอบคลุมการจัดการครบทั้ง 8 โมดูล (Hero, News, Classes, Activities, Teachers, Club, Users, Settings) พร้อมระบบพรีวิวแบบเรียลไทม์ และระบบลากวางสลับตำแหน่ง (Position Swap)

---

## User Stories

1. **As a mobile student**, I want to access the website on my smartphone (390px) without horizontal scrolling, so that I can read all department information comfortably with one hand.
2. **As a visitor**, I want to see an editorial hero section with a clean department title, realistic classroom photograph, and concise intro, so that I immediately understand what this department represents without distraction.
3. **As a student**, I want to use a persistent bottom navigation bar with 5 primary sections on mobile, so that I can switch between main pages quickly with my thumb.
4. **As a desktop user**, I want the navigation to expand into a numbered top navigation bar with clean spacing, so that the site looks balanced on larger screens.
5. **As a student**, I want to view a concise data summary strip on the homepage, so that I know the current number of teachers, available news articles, and upcoming events at a glance.
6. **As a student**, I want a numbered index list on the homepage linking directly to each section, so that I immediately know where to go next.
7. **As a prospective student**, I want to view the "About" page to learn about department facilities, curriculum offerings (ปวช./ปวส.), and internship programs with clear visual affordance separating static info from interactive cards, so that I can make informed decisions about enrolling.
8. **As a visitor**, I want to see a leadership feature for the Head of Department with their vision and direct contact link, so that I know who leads the academic department.
9. **As a parent**, I want to access direct phone, email, Facebook, and room location touch targets with one tap (tel: and mailto:), so that I can reach out to the department effortlessly.
10. **As a student**, I want to browse news articles categorized by announcement, activity, achievement, and general news using a horizontal scroll chip bar on mobile, so that I can filter updates that matter to me.
11. **As a student**, I want to view a featured news article prominently highlighted, so that I do not miss critical departmental announcements.
12. **As a reader**, I want to tap any news card row to view the full article with large documentary photography, published date, and complete body text, so that I can read in-depth details.
13. **As a student**, I want to toggle between the class timetable and activity calendar on a single schedule page, so that I do not have to navigate between disjointed pages.
14. **As a student**, I want to select my class group (e.g. ปวช.1/1 or ปวส.1/1) and day of the week (Mon-Fri) via day tabs on mobile, so that I can view my daily schedule as a vertical timeline without horizontal overflowing tables.
15. **As a desktop student**, I want the schedule to automatically render as a full weekly 5-day grid table following Refined Editorial Brutalism (sharp 1px borders, subtle backgrounds), so that I can see the entire week's subjects, rooms, and instructors at once.
16. **As a student**, I want to view an upcoming activity schedule grouped by month with prominent day numbers and countdown badges, so that I can plan my participation in extracurricular events.
17. **As a student**, I want to view the faculty directory and student club directory on a dedicated page (`pages/teachers.php`) separated into two clear sections, so that I can distinguish between instructors and student representatives.
18. **As a visitor**, I want any instructor or club member without a photo to display a stylish technical monogram instead of a generic gray avatar, so that the design consistency remains polished.
19. **As a teacher or administrator**, I want to access a dedicated login screen with visible labels, comfortable mobile gutters, and a password visibility toggle, so that I can sign into the admin console securely.
20. **As an administrator**, I want CSRF protection and secure session management on authentication, so that unauthorized attackers cannot compromise the administration panel.
21. **As an administrator**, I want an administration hub showing overview cards for all 8 management modules, so that I can quickly select which data to update.
22. **As an administrator**, I want to manage system user accounts (create, edit, update password, delete) with Argon2id hashing and RBAC (`admin` vs `editor`), so that I can grant or revoke access to department editors.
23. **As a logged-in administrator**, I want the system to block self-account deletion, so that I do not accidentally lock myself out of the system.
24. **As an editor**, I want to create, edit, upload photos for (with side-by-side preview and dropzone), and delete news articles, so that public announcements are always up to date.
25. **As an administrator**, I want to upload a new hero image, edit its editorial caption, and adjust the title with `<br>` support and real-time Full Preview Modal (Desktop/Mobile toggle), so that the homepage visual identity stays fresh.
26. **As an editor**, I want to add, edit, and delete class periods for any class group and day of the week with touch-optimized Day Selector tabs and dedicated drag handles (`⠿`), so that timetable changes are immediately visible to students without interfering with mobile scrolling.
27. **As an editor**, I want to schedule departmental activities with start/end dates, times, and locations inside styled editorial modals, so that the department calendar remains accurate.
28. **As an administrator**, I want to manage faculty profiles and promote a teacher to Head of Department via drag-and-drop position swapping, so that the public directory reflects current staff seamlessly.
29. **As an administrator**, I want to manage student club members and promote a student to Club Leader via drag-and-drop position swapping, so that student leadership information is properly acknowledged.
30. **As a mobile administrator**, I want all modal interactions, alerts, and confirmations to use accessible editorial styled modals (`modal.js`) rather than native browser popups, so that workflows are non-blocking and visually cohesive.
31. **As an administrator**, I want all destructive delete actions to prompt for confirmation via styled modals, so that I do not accidentally delete important departmental records.
32. **As a visitor using assistive technology**, I want clear contrast ratios meeting WCAG AA standards (>= 4.5:1), visible focus rings, and proper ARIA labels, so that the website is accessible to everyone.
33. **As an administrator**, I want to manage department contact details, social links, and system settings from a dedicated admin interface (`admin/settings.php`), so that I do not need to edit environment configuration files manually.
34. **As an editor**, I want to quickly add a new news category via an in-page modal dialog directly inside `admin/news.php`, so that I do not lose my article drafting flow.
35. **As an editor**, I want a rich text editor (Quill) that automatically parses Markdown on paste or typing and cleanly formats sub-phrases without resetting the document, so that formatting articles is fast and dependable.
36. **As a visitor encountering an invalid link or expired form**, I want to see a branded, helpful editorial error page (`error.php`) with clear HTTP status codes (403, 404, 419, 500) and return navigation, so that I am never stranded.
37. **As a public user**, I want pages to load instantly (< 100ms) through high-speed short-term caching (60s–120s TTL) while ensuring content updates published by administrators reflect immediately via instant cache busting.
38. **As an administrator**, I want the system to seamlessly rehash older passwords to Argon2id upon valid login, so that our security posture continuously improves without requiring user password resets.

---

## Implementation Decisions

### 1. Tech Stack, Architecture & Service Layer

- **Backend**: PHP 8.1+ running natively on standard local environments (XAMPP / Laragon / Linux Apache) with zero required build tools or Node.js in production.
- **Database**: MySQL / MariaDB with `utf8mb4` encoding and `utf8mb4_unicode_ci` collation. Database connectivity is encapsulated in a PDO Singleton in `includes/db.php`.
- **Domain Service Layer**: 100% of business logic and database queries are encapsulated in `includes/services/` (Zero SQL in views or controllers):
  - `AuthService`: Session management, Argon2id hashing, RBAC enforcement.
  - `CacheService`: File and in-memory short-lived caching with instant busting.
  - `FacultyService`: Faculty roster, club members, tree slot hierarchy, and position swapping.
  - `NewsService`: News articles, categorized queries, featured articles, and slug matching.
  - `ScheduleService`: Vocational timetable period calculation, conflict detection, and grid mapping.
  - `SettingService`: Key-value site settings, contact info, and dynamic homepage statistics.
- **Client Scripting**: Vanilla JavaScript micro-modules in `assets/js/` (`modal.js`, `app.js`, `admin-scheduler.js`, `admin-tree-slots.js`, `admin-quill.js`, `admin-hero.js`, `admin-image-upload.js`). Zero heavy frontend frameworks.

### 2. Design Tokens & Visual Aesthetics (Preserved Standards)

- **Design Philosophy**: Refined Editorial Institutional Design & Human-designed Digital Technology (per [docs/style.md](file:///c:/xampp/htdocs/web/docs/style.md)).
- **Color Palette & Ratios**:
  - Light Backgrounds (`#FFFFFF`, `#F4F8FA`): ~65%
  - Light Blue (`#E6F1F5`, `#B9DCEB`): ~20%
  - Deep Navy (`#17324D`): ~10%
  - Warm Orange Accent (`#E99A4A` / `#B5601F`): ~5% (paired with Deep Navy for high contrast >= 5.7:1).
- **Typography Pairing**:
  - `Kanit`: Display & Headings (Hero, page titles, section headers).
  - `Prompt` / `Anuphan`: Body, UI controls, navigation, and article paragraphs.
  - `IBM Plex Mono`: Technical labels, course codes, timetable periods, dates, and section indices (`01 / HOME`).
- **Shapes & Dividers**:
  - Border Radius: strictly 0–4px (precision print aesthetic; no bubbly rounded corners).
  - Thin 1px Dividers (`#DCE6EC`) and subtle background alternation (`#F4F8FA`) for depth.
  - Zero blurred box-shadows, zero floating 3D elements, zero neon cyberpunk effects.
- **Photography & Avatars**:
  - Realistic documentary photography of real classrooms and computer laboratories.
  - Technical monogram initials as the default fallback for staff/students without photos.

### 3. Project Structure & Clean URL Routing

- **Zero Clutter Root Directory**:
  - `index.php`: Essential Entry point (01 / HOME).
  - `error.php`: Centralized error handler for 403, 404, 419, 500 status codes.
  - `.env`: Environment secrets only (DB credentials, APP_ENV).
  - `.htaccess`: Apache URL rewriting rules for clean routing.
  - `PLAN.md`, `README.md`, `CONTEXT.md`: High-level roadmap and orientation documentation.
  - `docs/`: Centralized documentation hub containing [rules.md](file:///c:/xampp/htdocs/web/docs/rules.md), [style.md](file:///c:/xampp/htdocs/web/docs/style.md), [spec.md](file:///c:/xampp/htdocs/web/docs/spec.md), and [adr/](file:///c:/xampp/htdocs/web/docs/adr/).
- **Public Pages Cluster (`pages/`)**:
  - `pages/about.php`: 02 / About department, 5 dimensions of learning, leadership, and contact.
  - `pages/news.php`: 03 / News feed, category filters, and full article view (`?id=`).
  - `pages/schedule.php`: 04 / Class timetable (day tabs on mobile, weekly grid on desktop) and activity calendar.
  - `pages/teachers.php`: 07 / Faculty directory and student club directory.
  - `pages/login.php`: 05 / Authentication login screen.
  - `pages/logout.php`: Session destruction and redirect.
- **Admin Hub (`admin/`)**:
  - `admin/index.php`: Overview dashboard and summary metrics.
  - `admin/hero.php`: Hero title (`<br>` support), caption, image, and real-time full preview modal.
  - `admin/news.php`: Article CRUD, in-page quick category modal, side-by-side photo dropzone, real-time preview modal.
  - `admin/classes.php`: Visual timetable scheduler with touch Day Selector tabs, dedicated drag handle (`⠿`), and day-split list view.
  - `admin/activities.php`: Department calendar management with styled editorial modals.
  - `admin/teachers.php`: Faculty roster with tree slots and Head Teacher position swap.
  - `admin/club.php`: Student club roster with tree slots and Club Leader position swap.
  - `admin/users.php`: System user administration with RBAC (`admin`/`editor`) and centered layout.
  - `admin/settings.php`: System settings, contact phone/email/Facebook, address, and office hours.
- **Clean URL Architecture**:
  - `.htaccess` maps `/web/about` -> `pages/about.php`, `/web/news` -> `pages/news.php`, etc.
  - Auto-detected `BASE_URL` in `includes/config.php` supporting root domains and subfolder environments.

### 4. Modular CSS Architecture

- Modular breakdown orchestrated via native CSS `@import`:
  - `assets/css/tokens.css`: Core design tokens (colors, typography, radii, spaces).
  - `assets/css/base.css`: CSS reboot, typography baseline, layout foundations.
  - `assets/css/components/`: `badges.css`, `buttons.css`, `cards.css`, `forms.css`, `modals.css`, `navigation.css`, `tables.css`.
  - `assets/css/pages/`: `page-home.css`, `page-about.css`, `page-schedule.css`, `page-teachers.css`, `page-login.css`, `page-news.css`.
  - `assets/css/admin/`: `admin-layout.css`, `admin-scheduler.css`, `admin-tree.css`, `admin-components.css`.
  - `assets/css/site.css` and `assets/css/admin.css`: Clean orchestrators with 100% token consistency.

### 5. Security & Session Hardening

- **Argon2id Password Hashing**: System standard `PASSWORD_ARGON2ID` with transparent `password_needs_rehash()` check during authentication.
- **Whitelist HTML Sanitization**: Rich text from news editors is processed via `clean_html()` using native PHP `DOMDocument` against an approved tag/attribute whitelist, neutralizing Stored XSS. URL schemes are restricted to `http`, `https`, `mailto`, and `tel` via `safe_url()`.
- **Output Escaping**: All dynamic variables rendered in views pass through `e()` (`htmlspecialchars`).
- **CSRF Defense**: Session-bound CSRF tokens validated on all POST and AJAX mutating requests with HTTP 419 error responses on failure.
- **File Upload Safeguards**: MIME type verification, 5MB file size limit, randomized naming, and direct script execution prevention in `uploads/`.

### 6. Caching & Performance Architecture

- **Short-Lived Caching (60s–120s TTL)**: Public read-heavy queries (schedule grids, news listings, faculty rosters, site settings) are cached by `CacheService`.
- **Instant Cache Busting**: Any CUD operation in the admin panel triggers immediate invalidation of relevant cache keys (`CacheService::forget()`).
- **HTTP Caching Headers**: Public responses include `Cache-Control: public, max-age=60, s-maxage=120, stale-while-revalidate=30`. Admin responses enforce `Cache-Control: no-store, private`.
- **Client DOM Efficiency**: Batch DOM mutations using `DocumentFragment` and debounced event listeners to prevent layout thrashing.

### 7. Configuration Management

- Strict decoupling: `.env` is strictly reserved for sensitive environment credentials (`DB_*`, `APP_*`).
- Presentation, contact info, and site titles are stored in the MySQL `settings` table and editable in `admin/settings.php` via `SettingService`.

---

## Testing Decisions

1. **Testing Philosophy**:
   - Tests verify observable external behavior at architectural seams, ensuring stability across updates.
   - All tests enforce status 200 HTTP codes, valid semantic HTML5, zero horizontal page overflow, minimum 44px mobile touch targets, and persistence in MySQL.

2. **Testing Seams**:
   - **HTTP Integration Seam**:
     - Public endpoints (`/web/`, `/web/about`, `/web/news`, `/web/schedule`, `/web/teachers`, `/web/login`, `/web/error.php`) return HTTP 200 / valid semantic markup.
     - Protected admin endpoints (`/web/admin/index.php`, `hero.php`, `news.php`, `classes.php`, `activities.php`, `teachers.php`, `club.php`, `users.php`, `settings.php`) enforce session authentication.
   - **Security Seam**:
     - Submitting POST forms without a valid CSRF token reliably triggers HTTP 419.
     - Testing Argon2id password hashing and automatic rehash behavior.
     - Testing `clean_html()` against malicious XSS payloads (`<script>`, `onerror`, `javascript:`).
   - **Performance Seam**:
     - Verifying cache hit latency (< 50ms) and ensuring cache invalidation fires upon admin save actions.

3. **Automated & Manual Verification Criteria**:
   - **PHP Syntax Integrity**: `php -l` passes across all files in `pages/`, `admin/`, `includes/`.
   - **Console Health**: Zero JavaScript errors in browser Developer Tools across all pages.
   - **Multi-device Responsiveness**:
     - Mobile (360px, 390px, 430px): 100% single column, bottom nav, zero horizontal scroll.
     - Tablet (768px, 820px): Clean 2-column distribution, touch-friendly day selectors.
     - Desktop (1024px, 1280px, 1440px): Full editorial grid and weekly schedule table.
   - **Print Verification**: Schedule page prints cleanly on A4 landscape without truncation.

---

## Out of Scope

- E-commerce transactions, online payments, or student tuition processing.
- Student grading portal, student attendance check-in, or student account creation.
- Conversational AI chatbots, floating widget scrapers, or third-party automated tracking.
- Multi-language localization beyond Thai with English technical labels.
- Native mobile application wrappers (PWA/Capacitor).

---

## Architectural Decision Records (ADRs)

All core architectural decisions are formally documented in the `docs/adr/` directory:

- [0001: สถาปัตยกรรม Editorial Technology บน PHP & Bootstrap 5](file:///c:/xampp/htdocs/web/docs/adr/0001-editorial-technology-design-system.md)
- [0002: การแบ่งสัดส่วนข้อมูลอาจารย์และชมรมวิชาชีพในหน้า 07](file:///c:/xampp/htdocs/web/docs/adr/0002-dual-domain-faculty-and-club.md)
- [0003: สถาปัตยกรรม Domain Service Layer และระบบแคชประสิทธิภาพสูง (60s–120s TTL)](file:///c:/xampp/htdocs/web/docs/adr/0003-domain-service-layer-and-caching.md)
- [0004: การจัดกลุ่มหน้าเพจสาธารณะ (pages/) และระบบ Clean URL Routing](file:///c:/xampp/htdocs/web/docs/adr/0004-public-pages-clustering-and-clean-routing.md)
- [0005: การยกระดับความปลอดภัยด้วย Argon2id และ Whitelist HTML Sanitization](file:///c:/xampp/htdocs/web/docs/adr/0005-argon2id-password-hashing-and-security.md)
- [0006: การแยกส่วนโมดูลาร์ CSS Design Tokens และ Vanilla JS Micro-Modules](file:///c:/xampp/htdocs/web/docs/adr/0006-modular-css-and-vanilla-js-micro-modules.md)
- [0007: การย้ายการตั้งค่าระบบสู่ฐานข้อมูลและโมดูลบริหารจัดการหลังบ้าน](file:///c:/xampp/htdocs/web/docs/adr/0007-database-backed-system-settings.md)
