# 02: Role-based Access Control & Security Guards

**What to build:**
Enforce role authorization so only administrators can access and modify user accounts, and provide a clear dashboard security banner warning when default credentials (`admin1234`) are still in use.

**Blocked by:** None (can start immediately)

**Status:** closed

- [x] Add `require_role(string $role)` helper in `includes/auth.php`
- [x] Enforce `require_role('admin')` in `admin/users.php`
- [x] Hide user management navigation links from non-admin users in `includes/admin-header.php`
- [x] Display an in-app banner alert in `admin/index.php` and `admin/users.php` if the default password hash matches `admin1234`
