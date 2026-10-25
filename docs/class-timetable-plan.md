# Class timetable images and manual management — implementation plan

## Requested outcome

Remove the homepage “9 รายวิชา” statistic and the class model's “วิชาชีพ” classification. Give staff a choice between uploading a complete timetable image and managing timetable slots manually. The homepage and public schedule should reflect the selected class group's published timetable.

Implemented on 2026-10-25. The local database migration has been applied. The original analysis below records the starting state and intended design; current behavior is documented in README.md.

Confirmed public display requirement: staff explicitly choose image or manual mode for each group and save. Both the homepage and public schedule respect that saved choice. Switching retains the stored image and manual slots. Missing content in the chosen mode produces an empty/unavailable state; the site does not automatically switch to the other mode.

Admin UX: retain the original scheduler layout and use compact Manual/Image buttons in the group toolbar. Only the active editor is visible; the manual grid/list and related actions hide in Image mode, and image upload/preview fields hide and disable in Manual mode. Switching editors is immediate and retains unsaved field/file values; saving publishes the choice. Grid/list navigation preserves the current editing mode without publishing it.

Validation: PHP lint and JavaScript syntax checks passed; transaction-based service regression, fresh schema/seed installation, and local HTTP authentication/CSRF, upload/replace/remove, mode-switching, invalid/oversized upload and public-rendering checks passed. Computer Use stopped because it could not reliably determine Chrome's current URL, so visual/browser interaction checks remain unverified.

Test incident: the first fresh-install check executed legacy SQL containing `USE dbt_web`, so the seed statements ran against the main local database. This reapplied seeded course names/default teachers/rooms/credits and news-category names. The incidental `test-group` configuration was removed. Binary/general database logs are disabled, and no prior full-data backup was found in this workspace; restoration of any previous custom values requires a pre-session backup. The test now strips database-selection statements, rejects unexpected ones before execution, and compares source course/category rows before and after its isolated run.

## Assumptions to review

- “Remove 9 รายวิชา” means remove the course-count statistic from the homepage, retaining the catalog and course records.
- Confirmed: “model” means the database table. Retire the professional/regular course classification by removing `classes.course_type` and its index, plus dependent form choices, filters and badges. This removes classification metadata, not course records.
- “Image for class table” means an image of the complete weekly timetable for a class group, rather than individual course thumbnails.
- Authenticated staff manage the image or manual schedule in `admin/classes.php`; public visitors view it.
- Each group has one active presentation mode: manual or image. Switching mode preserves the other mode's content.
- Proposed homepage behavior: add a compact timetable preview with a class-group selector and a link to the full schedule. If only a link is desired, omit the preview during implementation.

## Current project and evidence

This is a server-rendered PHP/MySQL department website. It uses Bootstrap, project CSS tokens, and vanilla JavaScript. Public pages sit in `pages/`, services in `includes/services/`, and clean URLs are routed by `.htaccess`.

| Area | Current behavior | Relevant files |
|---|---|---|
| Homepage | Iterates the statistics returned by `SettingService::getHomeStats()`; links to the schedule, but renders no timetable | `index.php`, `includes/services/SettingService.php` |
| Course statistic | Counts professional courses dynamically; the displayed 9 is data-dependent | `includes/services/SettingService.php` |
| Administration | Manual grid/list scheduling, catalog editing, group selection, periods and modal editing | `admin/classes.php`, `includes/renderers/classes-*.php`, `includes/renderers/scheduler-table.php` |
| Schedule persistence | `classes` holds the course catalog; `class_schedule` holds group-specific slots | `includes/services/ScheduleService.php`, `database/schema/04_classes.sql` |
| Interactive editing | Authenticated POST/CSRF API supports dragging, resizing, and saving slots | `api/admin/classes.php`, `assets/js/admin-scheduler.js`, `assets/js/modules/scheduler-*.js` |
| Public timetable | Group selector, desktop grid, mobile day views, current/next slot information, print/export | `pages/schedule.php`, `assets/js/page-schedule.js` |
| Existing upload pattern | JPG/PNG/WebP, maximum 5 MB, MIME and image-header checks, random filenames, upload deletion helper | `includes/helpers.php`, `admin/hero.php`, `assets/js/admin-image-upload.js` |
| Caching | Cached services and public HTTP cache headers; settings writes clear `home_stats` | `includes/services/CacheService.php`, `includes/services/SettingService.php` |

No existing storage field for a timetable image or presentation mode was found. Group discovery currently comes only from `class_schedule`, so a group with an uploaded image and no manual slots would otherwise disappear.

The professional classification is currently stored as `classes.course_type` (`professional` / `regular`). It is read and written by `ScheduleService`, the admin controller/API, the catalog and class modals, scheduler renderers, and the public timetable/detail modal. Removing only its dropdown would leave dependent filters and badges in place.

## Recommended design

Add one small `class_timetables` table keyed by `class_group`, with `display_mode` (`manual` or `image`, default manual), nullable `image_path`, image alternative text, and timestamps. Add an incremental migration and update both schema representations for fresh installations. Existing groups without a configuration row continue using manual mode.

This makes timetable presentation part of `ScheduleService`. Group discovery merges saved timetable configurations with existing slot groups, allowing image-only groups. A JSON blob in global settings would require less schema work, but would couple independent group updates and complicate group discovery and validation.

Reuse the existing upload validation and preview controls. Validate mode, group and file before changing published state. Save the new file, persist its reference, then remove the old file only after the database write succeeds. On failure remove only the newly uploaded file. Removing the active image returns that group to manual mode; it never deletes manual slots.

Image mode represents a static picture. Slot details and current/next-class highlights apply only to the manual schedule. Offer the image's full-size link and download, and verify image print layout; do not run the manual grid export path against absent DOM elements.

## Proposed staff flow

```text
ตารางเรียน → เลือก/เพิ่มกลุ่มเรียน
วิธีแสดงตาราง: [จัดตารางเอง] [อัปโหลดรูปตาราง]

Manual: existing grid/list and editing controls
Image:  current image / empty upload area
        [เลือกรูป] → preview → [บันทึก]
        [เปลี่ยนรูป] [ลบรูป]
```

For each group, show the saved mode and distinguish an unsaved preview from published content. Reject switching to image mode without a valid stored or newly uploaded image. Provide field-level errors and a success message. Require confirmation to remove an image. All controls must work by keyboard, with visible labels and focus. On narrow screens stack the controls and show the full uncropped image with a full-size link. Missing files fall back to the manual schedule with a clear staff notice.

Public flow: homepage preview → group selection → full timetable at `/schedule?group=...`. Both public locations resolve the same saved presentation. Activities retain their existing separate view.

## Ordered implementation

1. **Remove the course statistic.** Remove its item and now-unused count query from `SettingService::getHomeStats()`. Invalidate the exact `home_stats` key, adjust the two-stat layout, and remove/update the matching course count in the admin hero preview so it matches the homepage.
2. **Add persistence and service operations.** Add the table/migration; implement group configuration reads/saves, image replacement/removal, mode validation, and group discovery including image-only groups. Keep SQL in services and use existing authentication/CSRF conventions for writes.
3. **Build the admin choice.** Add the presentation selector and image upload/preview/replace/remove form to `admin/classes.php`, using the shared upload script. Keep the catalog and manual slot editor available. Use normal multipart POST for images; the existing slot JSON API need not accept file uploads.
4. **Publish the selected presentation.** Add a shared public timetable presentation renderer for `pages/schedule.php` and the proposed compact preview in `index.php`. Render a readable, uncropped image in image mode and preserve the existing manual full schedule otherwise. Initialize manual-only JavaScript only when its DOM exists.
5. **Verify the complete flow.** Check migration/backward compatibility; upload, replace, remove, mode changes, image-only groups, public group selection, cache refresh, failed uploads/database writes, access control/CSRF, mobile layout, and print/download behavior. Run PHP lint on changed PHP files and a browser pass on homepage/admin/public schedule.

### Additional scope: retire the class classification

Implement this alongside step 1, before the timetable image feature:

1. Remove the course-type selector and “วิชาชีพ” / “สามัญ” labels from class creation/editing and course selection modals. Remove the corresponding catalog filter/column and schedule/detail badges, including public markup in `pages/schedule.php` and JavaScript data attributes/bindings.
2. Remove course-type parameters, validation, query predicates and selected aliases from `admin/classes.php`, `api/admin/classes.php`, and `ScheduleService`. Retire `getProfessionalCourseCount()` after removing all callers, including `admin/hero.php`. Keep subject codes, names, teachers, rooms, credits, group selection and schedule slots.
3. Stop using `course_type` in application code first, while leaving the existing column compatible with that release. Then use a separate migration to drop `idx_class_type` and `course_type`; update both fresh-install schemas, seeds and migration bootstrap paths so new and upgraded installations reach the same schema. Preserve a backup of classification values if a reversible data rollback is required; restoring the column alone cannot recover its old values.
4. Search for remaining `course_type`, `professional`, classification labels and count-method references; review each match so unrelated uses of “วิชาชีพ” (such as the student club name) remain. Verify class create/edit/list, course selection, slot editing and public class details against both the transitional and final schemas.

## Acceptance criteria

- Homepage no longer displays the course-count statistic; course data remains intact.
- Class forms, catalog filters and timetable/detail views no longer expose the professional/regular classification; class operations work after `course_type` is retired. Unrelated department/club wording is retained.
- Staff can publish a timetable image for a specific group, replace/remove it, or select the manual schedule.
- Switching modes preserves manual slots and any retained image.
- Groups created for image timetables are selectable even without manual slots.
- Public pages show the saved mode for the selected group, with a usable empty/missing-image fallback.
- Unsupported, oversized, corrupt or failed uploads leave the existing published timetable intact.
- Replacing/removing one group's image does not affect another group's files or timetable.
- Manual editing, catalog management, activities and relevant print/export behavior still work.

## Risks and verification notes

- Public responses advertise caching of 60 seconds (shared cache 120 seconds). Service invalidation alone cannot promise immediate refresh of already cached browser/proxy responses; verify and choose suitable headers for timetable presentation.
- `ScheduleService` invalidates `home_stats_` by prefix, while the actual statistics key is `home_stats`. Use the exact key for this change.
- A timetable image cannot supply structured class details or screen-reader-equivalent course data by itself. Include a descriptive alternative text field and preserve the manual schedule when available.
- Graphify's installed extractor reported missing SQL parsing support and PHP templates with no symbols. The graph includes a supplemental, deterministic file/include map; schema behavior above was checked directly in source. The graph is navigation evidence, not proof that every runtime dependency was extracted.

## Scope boundary

No course deletion, OCR/image-to-slots conversion, arbitrary document uploads, redesign, deployment, or unrelated refactoring is proposed.
