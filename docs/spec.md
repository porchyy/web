# Specification: เว็บไซต์และระบบจัดการข้อมูล แผนกเทคโนโลยีธุรกิจดิจิทัล (Digital Business Technology)

## Problem Statement

นักเรียน นักศึกษา อาจารย์ ผู้ปกครอง และผู้สนใจภายนอก ต้องการเข้าถึงข้อมูลที่สำคัญของแผนกวิชาเทคโนโลยีธุรกิจดิจิทัล (เช่น ข้อมูลหลักสูตร ข่าวสาร ประกาศ ตารางเรียน ตารางกิจกรรม และข้อมูลคณาจารย์) ผ่านอุปกรณ์สมาร์ตโฟนเป็นหลัก แต่เว็บไซต์ของหน่วยงานทางการศึกษาส่วนใหญ่มักออกแบบมาจากมุมมองของหน้าจอ Desktop แล้วย่อขนาดลงมา ส่งผลให้ตัวหนังสือเล็กเกินไป ตารางเรียนกว้างล้นจอจนต้องเลื่อนซ้ายขวา และโครงสร้างเมนูเข้าถึงยากด้วยมือเดียว 

นอกจากนี้ เว็บไซต์ด้านเทคโนโลยีในปัจจุบันมักตกอยู่ในกับดักการใช้ภาพลักษณ์แบบ AI-generated หรือเทมเพลตสำเร็จรูป (เช่น โทนสี Cyberpunk, แสงนีออน, วัตถุสามมิติลอย, ภาพกราฟิกหุ่นยนต์/สมองดิจิทัล) ซึ่งดูแปลกแยก ไม่มีความเป็นมนุษย์ และไม่สะท้อนเอกลักษณ์ของการศึกษาและการทำงานในโลกธุรกิจจริง ขณะเดียวกัน ผู้ดูแลระบบของแผนกก็ต้องการระบบจัดการหลังบ้านที่ใช้งานง่าย สะดวกบนหน้าจอมือถือ และปรับปรุงข้อมูลประจำวันได้อย่างรวดเร็ว

## Solution

ออกแบบและพัฒนาระบบเว็บไซต์และระบบบริหารจัดการข้อมูลของแผนกวิชาเทคโนโลยีธุรกิจดิจิทัลด้วยแนวคิด **Mobile-First 100% (เริ่มต้นที่หน้าจอ 390px)** ภายใต้แนวคิด **“Human-designed Digital Technology”** ที่ผสาน Modern Technology, Contemporary Education, Editorial Graphic Design และ Clean Interface เข้าด้วยกัน

ตัวระบบสร้างภาพลักษณ์ความเป็นเทคโนโลยีผ่าน **โครงสร้างของงาน** (ระบบกริด, เส้นคั่นบาง 1px, การจัดวางกลุ่มข้อมูลแบบมีลำดับขั้น, ตัวเลขและ Technical Label สไตล์ Monospace) โดยไม่พึ่งพา Effect ฉูดฉาด ใช้สัดส่วนสีอ่อนเป็นหลัก (ขาว 65%, ฟ้าอ่อน 20%, กรมท่า Deep Navy 10%, ส้ม Accent 5%) ขับเคลื่อนด้วย PHP 8, Bootstrap 5.3 (ปรับแต่ง CSS Tokens ทั้งหมด) และฐานข้อมูล MySQL พร้อมระบบหลังบ้าน Admin ที่ครอบคลุมการจัดการครบทั้ง 7 โมดูลโดยไม่เพิ่มภาระหรือฟีเจอร์ที่ไม่จำเป็น

## User Stories

1. As a mobile student, I want to access the website on my smartphone (390px) without horizontal scrolling, so that I can read all department information comfortably with one hand.
2. As a visitor, I want to see an editorial hero section with a clean department title, realistic classroom photograph, and concise intro, so that I immediately understand what this department represents without distraction.
3. As a student, I want to use a persistent bottom navigation bar with 5 primary sections on mobile, so that I can switch between main pages quickly with my thumb.
4. As a desktop user, I want the navigation to expand into a numbered top navigation bar with clean spacing, so that the site looks balanced on larger screens.
5. As a student, I want to view a concise data summary strip on the homepage, so that I know the current number of teachers, available news articles, and upcoming events at a glance.
6. As a student, I want a numbered index list on the homepage linking directly to each section, so that I immediately know where to go next.
7. As a prospective student, I want to view the "About" page to learn about department facilities, curriculum offerings (ปวช./ปวส.), and internship programs, so that I can make informed decisions about enrolling.
8. As a visitor, I want to see a leadership feature for the Head of Department with their vision and direct contact link, so that I know who leads the academic department.
9. As a parent, I want to access direct phone, email, Facebook, and room location touch targets with one tap (tel: and mailto:), so that I can reach out to the department effortlessly.
10. As a student, I want to browse news articles categorized by announcement, activity, achievement, and general news using a horizontal scroll chip bar on mobile, so that I can filter updates that matter to me.
11. As a student, I want to view a featured news article prominently highlighted, so that I do not miss critical departmental announcements.
12. As a reader, I want to tap any news card row to view the full article with large documentary photography, published date, and complete body text, so that I can read in-depth details.
13. As a student, I want to toggle between the class timetable and activity calendar on a single schedule page, so that I do not have to navigate between disjointed pages.
14. As a student, I want to select my class group (e.g. ปวช.1/1 or ปวส.1/1) and day of the week (Mon-Fri) via day tabs on mobile, so that I can view my daily schedule as a vertical timeline without horizontal overflowing tables.
15. As a desktop student, I want the schedule to automatically render as a full weekly 5-day grid table, so that I can see the entire week's subjects, rooms, and instructors at once.
16. As a student, I want to view an upcoming activity schedule grouped by month with prominent day numbers and countdown badges, so that I can plan my participation in extracurricular events.
17. As a student, I want to view the faculty directory and student club directory on a dedicated page separated into two clear sections, so that I can distinguish between instructors and student representatives.
18. As a visitor, I want any instructor or club member without a photo to display a stylish technical monogram instead of a generic gray avatar, so that the design consistency remains polished.
19. As a teacher or administrator, I want to access a dedicated login screen with visible labels and a password visibility toggle, so that I can sign into the admin console securely.
20. As an administrator, I want CSRF protection and secure session management on authentication, so that unauthorized attackers cannot compromise the administration panel.
21. As an administrator, I want an administration hub showing overview cards for all 7 management modules, so that I can quickly select which data to update.
22. As an administrator, I want to manage system user accounts (create, edit, update password, delete), so that I can grant or revoke access to department editors.
23. As a logged-in administrator, I want the system to block self-account deletion, so that I do not accidentally lock myself out of the system.
24. As an editor, I want to create, edit, upload photos for, and delete news articles, so that public announcements are always up to date.
25. As an administrator, I want to upload a new hero image, edit its editorial caption, and adjust the introductory text with real-time preview, so that the homepage visual identity stays fresh.
26. As an editor, I want to add, edit, and delete class periods for any class group and day of the week, so that timetable changes are immediately visible to students.
27. As an editor, I want to schedule departmental activities with start/end dates, times, and locations, so that the department calendar remains accurate.
28. As an administrator, I want to manage faculty profiles (name, position, role, expertise, email, photo, sort order), so that the public directory reflects current staff.
29. As an administrator, I want to manage student club members and designated club leaders, so that student leadership information is properly acknowledged.
30. As a mobile administrator, I want all add/edit operations in the admin panel to open as clean dedicated form views instead of cramped modal popups, so that virtual keyboards do not obstruct form fields or action buttons.
31. As an administrator, I want all destructive delete actions to prompt for confirmation, so that I do not accidentally delete important departmental records.
32. As a visitor using assistive technology, I want clear contrast ratios meeting WCAG AA standards (>= 4.5:1), visible focus rings, and proper ARIA labels, so that the website is accessible to everyone.

## Implementation Decisions

1. **Tech Stack & Environment**:
   - Backend: PHP 8 with PDO MySQL using strict prepared statements.
   - Frontend: Bootstrap 5.3 CDN for baseline grid, flex utilities, and reboot, paired with a custom CSS token architecture in `assets/css/site.css` that completely overrides Bootstrap's generic look.
   - Database: MySQL/MariaDB with UTF-8 mb4 encoding, hosted on XAMPP/local server.

2. **Design Tokens & Visual Aesthetics**:
   - Palette Ratio: Light/White `#FFFFFF` & `#F4F8FA` (~65%), Light Blue `#E6F1F5` & `#B9DCEB` (~20%), Deep Navy `#17324D` (~10%), Orange Accent `#E99A4A` (~5%).
   - Orange Contrast Rule: Orange `#E99A4A` as a background button is always paired with Deep Navy `#17324D` text to achieve a contrast ratio of ~5.7:1 (WCAG AA compliant). Small text labels use darker accent `#B5601F`.
   - Typography: `Anuphan` for Thai body text and headings, paired with `IBM Plex Mono` for technical numbers, section indices (`01 / ABOUT`), dates, and course codes.
   - Shapes & Dividers: Border radius strictly 0–4px; subtle 1px dividers (`#DCE6EC`); no blurred dropped shadows; subtle background alternation for surface depth.
   - Photography: Realistic documentary imagery depicting real students, classrooms, and computing labs; strict avoidance of 3D floating spheres, neon, cyberpunk, and AI robot/brain illustrations. Monogram initials as technical fallback.

3. **Routing & Directory Structure**:
   - Standard 7-page structure with no extra added pages:
     - `index.php` (Home)
     - `about.php` (About Department)
     - `news.php` (News Feed & Full Article View via `?id=`)
     - `schedule.php` (Class Schedule & Activity Calendar via `?tab=`)
     - `login.php` & `logout.php` (Authentication)
     - `admin/` (Administration Hub & 7 specific management controllers)
     - `teachers.php` (Faculty & Student Club Directory)
   - Auto-detected `BASE_URL` in `includes/config.php` supporting both root domains and `/web` subdirectories in XAMPP.

4. **Security & Session Management**:
   - Passwords hashed with Bcrypt (`password_hash` with `PASSWORD_DEFAULT`).
   - CSRF tokens generated per session and validated on all POST operations.
   - Role-based authorization (`admin` for complete control; `editor` for content updates).
   - Input sanitization via `htmlspecialchars` (`e()` helper) and safe file upload validation (MIME type verification, 5MB limit, random file renaming).

## Testing Decisions

1. **Testing Philosophy**:
   - Tests must evaluate observable external behavior at the highest possible architectural seam, not internal private helpers.
   - Tests verify that HTTP responses return status 200, output valid semantic HTML, include custom design tokens and fonts, enforce mobile touch target criteria (>= 44px), enforce CSRF validation on forms, and persist data in MySQL.

2. **Testing Seams**:
   - **Single Primary Seam: HTTP Request / Response Integration Seam**:
     - Testing public endpoints (`/web/index.php`, `/web/about.php`, `/web/news.php`, `/web/schedule.php`, `/web/teachers.php`, `/web/login.php`) via automated HTTP requests.
     - Testing authenticated admin endpoints (`/web/admin/index.php`, `users.php`, `news.php`, `hero.php`, `classes.php`, `activities.php`, `teachers.php`, `club.php`) with session cookies.
     - Verifying database integrity by checking table records before and after CRUD requests.

3. **Automated Verification Criteria**:
   - All public and admin endpoints return HTTP 200 (or HTTP 302 redirect for protected admin access without login).
   - CSS assets load with HTTP 200 and define custom tokens (`--c-navy`, `--c-accent`, `--font-sans`, `--font-mono`).
   - Mobile viewports (360px, 390px, 430px) render clean bottom navigation without horizontal layout overflow.
   - Form submissions without a valid CSRF token fail with HTTP 419.

## Out of Scope

- E-commerce, payment gateways, or student tuition payment processing.
- Student grading system, attendance tracking, or student portal logins (public viewers only read schedules; only admin/editors log in).
- Online chat bots, AI conversational widgets, or third-party automated social scrapers.
- Multi-language localization beyond Thai (with English technical labels).
- Push notifications or native mobile application wrapping.

## Further Notes

- The system is built to run standalone on standard educational server environments (such as XAMPP or Laragon) with zero build tools or Node.js runtime required in production.
- Default administrative credentials for initial system setup: `admin` / `admin1234` (with an explicit in-app prompt to update the password).
- All architectural decisions are formally recorded in `docs/adr/0001-editorial-technology-design-system.md` and `docs/adr/0002-dual-domain-faculty-and-club.md`.
