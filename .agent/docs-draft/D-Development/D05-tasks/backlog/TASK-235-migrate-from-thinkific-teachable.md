# Task: Migrate from Thinkific / Teachable

**ID:** TASK-235
**Unit:** — (new module)
**Group:** G-MIGRATION
**Priority:** P3
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build an import tool for courses exported from Thinkific or Teachable. Both platforms provide a CSV export + media bundle (ZIP of video/image files). The tool maps exported fields to EverLMS entities and handles the differences between the two platforms' export formats. New module `lms_migrate_external`.

## Context
- Teachable and Thinkific are common platforms that course creators may be migrating away from.
- TASK-234 handles Moodle (XML-based); this task handles CSV-based external platform exports.
- New module `lms_migrate_external`; shares no code with TASK-234 but follows same wizard pattern.

## Acceptance Criteria
- [ ] New module `lms_migrate_external` scaffolded
- [ ] Admin UI wizard at `/admin/lms/migrate/external` — 4 steps: (1) Select platform (Thinkific | Teachable), (2) Upload CSV + optional media ZIP, (3) Field mapping review, (4) Run import with progress
- [ ] Thinkific CSV format support: columns `course_name`, `chapter_name`, `lesson_name`, `lesson_type`, `content_url`, `video_url`, `is_free_preview` parsed correctly
- [ ] Teachable CSV format support: columns `course_title`, `section_name`, `lecture_name`, `lecture_type`, `file_path`, `is_published` parsed correctly
- [ ] Both formats: creates `lms_course` node, `lms_lesson` nodes (type mapped: text→text, video→video, quiz→quiz), and uploads media files from ZIP to managed file entities
- [ ] Field mapping UI (step 3) allows admin to remap any source column to any EverLMS field before import
- [ ] Drush command `drush lms:migrate-external --platform=thinkific --csv=<path> [--media-zip=<path>]`
- [ ] Import log with per-row status; download log as CSV post-import
- [ ] Idempotent on re-run: match on course title + lesson title to avoid duplicates

## Technical Notes
- New module: `lms_migrate_external` at `web/modules/custom/lms_migrate_external/`
- Source plugins: `ThinkificCsvSource` and `TeachableCsvSource` (extend `SourcePluginBase`); each implements `fields()` and `getIds()` for Migrate API
- CSV parsing: PHP `SplFileObject` with `CSV_MODE`; handle UTF-8 BOM (common in Excel-exported CSVs)
- Media ZIP: PHP `ZipArchive`; extract to `private://lms_migrate_external_tmp/{import_id}/`; match file names from CSV `content_url`/`file_path` to extracted files
- Field mapping: stored as config entity `ExternalMigrationMapping` (platform + column → lms field) per import session
- Platform abstraction: `ExternalPlatformInterface` implemented by `ThinkificPlatform` and `TeachablePlatform`; DI registered as tagged services

## Dependencies
- TASK-234 (Moodle migrate — establishes wizard pattern and module structure convention)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: CSV parsing fixtures for both platforms; functional: end-to-end import, idempotency)
- [ ] No new debt introduced
