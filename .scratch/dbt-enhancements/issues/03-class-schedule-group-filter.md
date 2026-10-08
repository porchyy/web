# 03: Class Schedule Group Filter & Print Layout

**What to build:**
Allow students and instructors to select and switch between class groups (e.g. ปวช.1/1, ปวช.2/1, ปวส.1/1) on both mobile timeline and desktop grid views, and provide a clean printable stylesheet.

**Blocked by:** None (can start immediately)

**Status:** closed

- [x] Query distinct available class groups from `class_schedule` in `schedule.php`
- [x] Provide a clean group selector UI (chip/select dropdown) maintaining the selected group across day navigation
- [x] Filter schedule queries by the active class group
- [x] Add `@media print` CSS rules in `assets/css/site.css` and a print button on `schedule.php`
