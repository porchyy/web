# 05: Activity Schedule Real-Time Countdown & Multi-Day Support

**What to build:**
Enable multi-day date ranges for departmental events and display dynamic countdown badges (e.g. "วันนี้", "พรุ่งนี้", "อีก 3 วัน", "เสร็จสิ้นแล้ว") instead of static badges.

**Blocked by:** None (can start immediately)

**Status:** closed

- [x] Add optional `event_date_end` and `action_url` columns to `activities` table in `database/schema.sql` and migration
- [x] Update `admin/activities.php` form to support `event_date_end` and `action_url`
- [x] Add activity countdown helper in `includes/helpers.php` (e.g. `activity_countdown_badge(string $date, ?string $endDate)`)
- [x] Update `schedule.php` and `index.php` upcoming activity cards to render real-time countdown badges
