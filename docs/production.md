# Production deployment

The release is prepared for Apache 2.4 with PHP 8.2+ and MySQL/MariaDB. Use a currently supported, fully patched PHP release. Required PHP extensions: PDO MySQL, fileinfo, mbstring, DOM and sessions. There are no Composer/npm runtime dependencies; Bootstrap, Quill, Google Fonts and html2canvas load from the existing CDNs.

## Before deployment

1. Back up the complete database and `uploads/` outside the web root. Verify that the backup can be restored. Do not run demo seeds against an existing database.
2. Review the local data incident: an earlier fresh-install test accidentally selected the main local database and reapplied seeded course names/default teachers/rooms/credits and category names. The harness is corrected; prior custom values cannot be recovered without a pre-session backup. Confirm the content or restore those values before copying this local database to production. No remote production database has been deployed to or modified.
3. Build with `powershell -File tools/package-release.ps1`. The ZIP excludes `.env`, user uploads, seed/demo data, tests, screenshots, graph outputs, source-control metadata, caches and backups. Keep existing uploads when updating a site. Fresh sites must supply their own content/images.
4. Extract to staging. Copy `.env.example` to `.env` and fill in the real HTTPS `BASE_URL`, a dedicated database account and strong password. Keep `APP_ENV=production` and `DB_AUTO_BOOTSTRAP=0`. Server-provided environment values take precedence over `.env`.
5. For a new installation, create the database using an operator account, configure its name in `.env`, and run `php tools/install-schema.php` with a schema account. This uses the modular schema files without selecting another database or creating demo data. Then provision an administrator with `tools/create-admin.php` using `ADMIN_USERNAME` and `ADMIN_PASSWORD` environment variables. It refuses existing usernames. For existing accounts, change default credentials using the authenticated user-management page before enabling production mode. Production login rejects accounts that still use the seeded development password.
6. For an existing database, enter a maintenance window, deploy matching code and run `php database/migrations/2026_10_class_timetable_images.php` with a migration account that can ALTER/CREATE tables. This retires `classes.course_type` after writing a metadata backup to protected `scratch/migration-backups/`. It preserves course and slot records and is safe to rerun. It is not a substitute for a full database backup.
7. Use a runtime database account limited to SELECT/INSERT/UPDATE/DELETE on the application database. Do not grant CREATE DATABASE or ALTER to the web process. Only `uploads/` and `scratch/` need filesystem write access; create `scratch/` before checks. Keep application/configuration files read-only to the web process.
8. Enable `mod_rewrite`, `mod_headers` (for static upload headers), and the AllowOverride permissions needed by `.htaccess`. Verify private directories return 403. Keep `uploads/.htaccess` in place; uploaded scripts and directory listing are forbidden. Equivalent rules are required if using another web server.
9. Enforce HTTPS at the web server/load balancer. Terminate TLS with a valid certificate and set HSTS only on the HTTPS virtual host. Production sessions use Secure, HttpOnly, SameSite=Lax cookies. Do not use production mode over plain HTTP. Set `BASE_URL` explicitly behind a proxy; do not trust arbitrary forwarded headers.
10. Apply `deploy/php-production.ini` to PHP configuration. `.user.ini` is used by CGI/FPM and is not a substitute for mod_php/server-level settings. Set an operator-controlled error log outside the document root and monitor it. Configure Apache `ServerTokens Prod` and `ServerSignature Off` at server level. Restart/reload the relevant service after changes.
11. Configure a shared login rate limit at the reverse proxy/WAF. The application's session-based login lockout does not stop attempts made with new cookies. Review external CDN availability and your site's content-security policy in staging before launch.

## Verification and release

Run `php tools/check-production.php` on staging and the production host. It exits nonzero for development settings, missing schema, root/empty database credentials, placeholder URLs, missing writable directories/extensions, or seeded passwords. It does not change any records or passwords.

Run the tests against a disposable local/staging database, never a production database. `tests/timetable-http.cjs` only targets localhost and requires `DBT_TEST_USERNAME` / `DBT_TEST_PASSWORD`; provide them as temporary shell environment variables and clear them after testing. The release ZIP does not contain tests or demo credentials.

Confirm: login/logout, CSRF errors, image upload/replace/remove, manual/image switching, group selection, catalog edits, activities, homepage, image containment on mobile/desktop, and print/download. Confirm inactive editor fields remain hidden/disabled. Check the browser console and server error log. Visual browser verification has not been completed by the agent because Computer Use could not determine Chrome's current URL; complete this check in staging.

After launch, monitor HTTP 5xx, PHP errors, upload failures, free disk space and database backups. Verify private paths and executable upload paths still return 403 through the public hostname.

## Rollback

Keep the prior application release, full database backup and uploads snapshot together. If rollback is needed, stop writes, restore the matching code/database/uploads, clear the application cache under `scratch/cache/`, and verify before reopening. Restoring only the dropped classification column cannot recover its old values; the metadata JSON is for targeted recovery, not a full rollback. Never delete user uploads or migration backups as part of source cleanup.

Security configuration follows the [PHP error-reporting guidance](https://www.php.net/manual/en/errorfunc.configuration.php), [PHP session configuration](https://www.php.net/manual/en/session.configuration.php), and [Apache FilesMatch documentation](https://httpd.apache.org/docs/2.4/mod/core.html#filesmatch).
