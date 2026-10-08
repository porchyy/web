# 01: Pre-factor & Shared Clean Helpers

**What to build:**
Centralize duplicated record deletion and asset cleanup logic across administrative modules, provide a reusable avatar/monogram component helper, and improve naming clarity of core utilities.

**Blocked by:** None (can start immediately)

**Status:** closed

- [x] Add `delete_record_with_asset(string $table, int $id, string $column = 'image_path')` to `includes/helpers.php`
- [x] Refactor `admin/news.php`, `admin/teachers.php`, and `admin/club.php` to use `delete_record_with_asset()`
- [x] Add `render_avatar(string $name, ?string $imagePath, int $size = 48, string $class = '')` helper to `includes/helpers.php` and adopt in `teachers.php`
- [x] Add `format_time_hm(?string $time)` as a clear alias or replacement for `hm()`
