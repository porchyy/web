# Master Implementation Plan: DBT Web System Modernization & Refactoring

> **Document Status**: PENDING USER APPROVAL / AWAITING EXECUTION COMMAND (`proceed` / `execute`)  
> **Target System**: Digital Business Technology (DBT) Portal & Admin CMS (`c:\xampp\htdocs\web`)  
> **Guiding Standard**: [STYLE.md](file:///c:/xampp/htdocs/web/STYLE.md) (*"Only show what user needs — Less clutter = more productive"*, Clean, Minimalism, Refined Editorial Brutalism)  
> **Engineering Rulebook**: [RULES.md](file:///c:/xampp/htdocs/web/RULES.md) (Unified Naming Conventions, Clean Code, SoC, SLAP, SLC, Performance, Security & Refactoring Protocol)  
> **Planning Consensus**: Approved via interactive `/grill-me` session.

---

## 1. Problem & Agreed Solution Matrix

| # | Problem Domain | Current Issue | Agreed Solution |
|---|---|---|---|
| **1** | **CSS Modularization** | `site.css` (45KB) and `admin.css` (35KB) are monolithic God components. | Break down into `tokens.css`, `base.css`, `components/*.css`, `pages/*.css`, and `admin/*.css`. Use native CSS `@import` entry points. |
| **2** | **JS Modularization & Naming** | Monolithic and fragmented scripts with unstandardized naming. | Standardize to modular scripts: `modal.js`, `site-app.js`, `site-schedule.js`, `admin-scheduler.js`, `admin-quill.js`, `admin-tree-slots.js`, `admin-image-upload.js`. |
| **3** | **System Config & Settings** | Duplicate configs in `.env` and DB, no admin UI. | Reserve `.env` strictly for environment/DB credentials. Migrate presentation configs to MySQL `settings` table. Build `admin/settings.php`. |
| **4** | **Quill Rich Text Editor** | Manual convert button; destructive DOM wipes on input break headings and formatting. | Remove manual convert button. Auto-parse markdown on paste/keystroke without `dangerouslyPasteHTML` DOM wipes. Auto-split block headings when applied to sub-phrases. |
| **5** | **Hero Admin (`admin/hero.php`)** | Cluttered preview box; title split across two rigid inputs (`hero_title_1`, `hero_title_2`). | Remove preview box. Merge title into a single input supporting `<br>`. Add real-time Full Preview Modal with Desktop/Mobile toggle. |
| **6** | **News Admin (`admin/news.php`)** | Missing quick category creation; release date input redundant; image input stacked; lacks preview. | Add "Add Category" modal without leaving page. Auto-set publish date (remove input). Place image preview and dropzone side-by-side. Add full preview modal. |
| **7** | **Scheduler UX (`admin/classes.php`)** | Mobile/tablet touch conflicts; flat list view; clutter (`04 / TIMETABLE SCHEDULER · สอศ.`). | Touch-optimized Day Selector (Tabs Mon–Fri) on mobile/tablet. Dedicated drag handle (`⠿`) so body scrolls smoothly. Split list menu by day of the week. Remove obsolete header. Enlarge typography. |
| **8** | **Native Alert / Confirm Dialogs** | Native `alert()` and `confirm()` disrupt UI in `admin/activities.php` and throughout admin. | Replace with accessible, styled editorial modals (`modal.js`). Manage activity CRUD inside modals. |
| **9** | **Tree Slots Swap (`admin/teachers.php`, `admin/club.php`)** | Dropping onto Head Teacher / Club Head rejects the drop. | Implement position swap: dropping a member onto the head card promotes that member to head and demotes previous head to regular roster, persisting order in DB. |
| **10** | **Users Form Alignment (`admin/users.php`)** | Create/Edit card stuck on the left. | Add `mx-auto` to center the card. |
| **11** | **Visual Affordance (`pages/about.php`)** | Static cards and interactive items have identical appearance. | Differentiate static info cards (neutral borders, distinct typography) from interactive curriculum cards (hover highlight, micro-arrows, schedule links). |
| **12** | **Mobile Responsiveness (`pages/login.php`)** | Zero mobile horizontal padding, edge-touching elements. | Centered layout, comfortable mobile/tablet gutters, responsive typography. |
| **13** | **Schedule Table Style (`pages/schedule.php`)** | Clashes with `STYLE.md` (rounded corners, 4px thick borders, glowing shadows). | Restore Refined Editorial Brutalism: sharp 1px borders, subtle backgrounds, crisp typography. |

---

## 2. Phased Execution Roadmap

### Phase 1: Foundations (CSS Breakdown & Modular JS)
- [ ] **Track 1.1: CSS Modularization & Token Architecture**
  - Create `assets/css/tokens.css` (variables, colors, radii, typography).
  - Create `assets/css/base.css` (reset, layout, base elements).
  - Create `assets/css/components/`:
    - `buttons.css`
    - `forms.css`
    - `modals.css`
    - `tables.css`
    - `cards.css`
    - `badges.css`
  - Create `assets/css/pages/`:
    - `page-schedule.css`
    - `page-about.css`
    - `page-login.css`
    - `page-news.css`
  - Create `assets/css/admin/`:
    - `admin-layout.css`
    - `admin-scheduler.css`
    - `admin-tree.css`
  - Update `site.css` and `admin.css` to act as clean `@import` orchestrators.
- [ ] **Track 1.2: Modal Component & Accessible Dialogs**
  - Create `assets/js/modal.js` providing `Modal.confirm()` and `Modal.open()`.
  - Replace native `alert()` and `confirm()` in `assets/js/app.js` and admin scripts.

### Phase 2: Core Services & System Settings
- [ ] **Track 2.1: Database Migration & SettingService Expansion**
  - Ensure all site/contact keys exist in `settings` table (`site_name`, `site_name_en`, `site_short`, `institute_name`, `contact_phone`, `contact_email`, `contact_facebook_name`, `contact_facebook_url`, `contact_address`, `contact_hours`, `hero_title`).
  - Update `includes/config.php` and `includes/services/SettingService.php` to read DB-first with `.env` fallback.
  - Clean up `.env` to reserve strictly for DB connection & app environment.
- [ ] **Track 2.2: New Admin Settings Page (`admin/settings.php`)**
  - Create `admin/settings.php` with tabbed configuration (General, Contacts, Social Media).
  - Add navigation link in `includes/admin-header.php`.

### Phase 3: Quill Editor Overhaul
- [ ] **Track 3.1: Quill Auto-Markdown & Partial Heading Fix**
  - Remove manual `[ 🪄 แปลง Markdown / HTML ]` toolbar button.
  - Remove destructive `quill.clipboard.dangerouslyPasteHTML` on typing.
  - Implement smart block-heading splitting when formatting sub-phrases.
  - Implement paste and keystroke markdown matcher without DOM disruption.

### Phase 4: Content Management & Full Preview
- [ ] **Track 4.1: Hero Management Overhaul (`admin/hero.php`)**
  - Remove inline "ตัวอย่างข้อความพรีวิว".
  - Merge `hero_title_1` and `hero_title_2` into a single `hero_title` input/editor supporting `<br>`.
  - Update homepage `index.php` to display `hero_title` with `<br>`.
  - Implement Real-Time Full Preview Modal with Desktop/Mobile viewport switcher.
- [ ] **Track 4.2: News Management Overhaul (`admin/news.php`)**
  - Add AJAX-powered "เพิ่มหมวดหมู่ใหม่" modal next to category select.
  - Remove `published_at` release date input from create/edit forms.
  - Restructure image upload into side-by-side layout (preview left, dropzone right).
  - Implement Real-Time Full Preview Modal for news articles.

### Phase 5: High-Impact Admin Engagements
- [ ] **Track 5.1: Scheduler Mobile & List Overhaul (`admin/classes.php`)**
  - Remove `04 / TIMETABLE SCHEDULER · สอศ.` from header.
  - Build touch-friendly Day Selector (Mon–Fri) on mobile/tablet (< 1024px).
  - Implement smooth page scrolling on card bodies with dedicated drag handle (`⠿`) for reordering.
  - Overhaul List View: split classes by Day of the Week with clear day headers.
  - Enlarge typography and improve readability across all viewport sizes.
- [ ] **Track 5.2: Activities Modal Overhaul (`admin/activities.php`)**
  - Convert Add/Edit Activity flows into editorial modal dialogs.
  - Replace delete confirm dialog with styled modal confirmation.
- [ ] **Track 5.3: Tree Slots Head Replacement (`admin/teachers.php`, `admin/club.php`)**
  - Support dropping a member onto the Head Teacher / Club Head card.
  - Swap positions: promoted member becomes head, previous head moves to member roster.
  - Save updated roles and sort orders to MySQL via AJAX.
- [ ] **Track 5.4: Admin Users Centering (`admin/users.php`)**
  - Center the Create/Edit card using `mx-auto`.

### Phase 6: Public Views & Responsive Polish
- [ ] **Track 6.1: Public Schedule Page (`pages/schedule.php`)**
  - Remove rounded 8px corners, 4px thick borders, and blur shadows.
  - Restore sharp 1px borders and subtle editorial highlights per `STYLE.md`.
- [ ] **Track 6.2: About Page Affordance Hierarchy (`pages/about.php`)**
  - Style static content cards with neutral, non-clickable appearance.
  - Style curriculum cards with prominent hover states, badges, and action buttons.
- [ ] **Track 6.3: Login Page Responsiveness (`pages/login.php`)**
  - Add gutters, center form container on mobile/tablet, fix edge bleeds.
- [ ] **Track 6.4: System-wide Mobile Responsiveness Audit**
  - Audit all public and admin pages on 360px, 390px, 768px, and 1024px.
  - Ensure zero horizontal overflow and minimum 44px touch targets.

---

## 3. Completion Verification Gate
- [ ] Modular CSS loads via `@import` without console errors.
- [ ] Quill allows partial text selection headings without converting whole document or breaking cursor.
- [ ] Full preview modals for Hero and News render correctly in desktop and mobile modes.
- [ ] Admin scheduler scrolls naturally on mobile/tablet and reorders via `⠿` handle.
- [ ] Head teacher swap persists in database.
- [ ] Public schedule table strictly complies with `STYLE.md`.
