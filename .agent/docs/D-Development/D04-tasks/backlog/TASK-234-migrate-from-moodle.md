# Task: Migrate from Moodle (mbz format)

**ID:** TASK-234
**Unit:** — (new module)
**Group:** G-MIGRATION
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a Moodle course migration tool that accepts a Moodle `.mbz` backup archive (a ZIP containing XML manifests and media) and imports its contents into EverLMS as courses, lessons, quizzes, and optionally user accounts. A UI wizard guides the admin through: upload → parse & preview → field mapping → confirm import.

## Context
- Many Vietnamese schools use Moodle; Moodle mbz export is the canonical migration path.
- New module `lms_migrate_moodle`; no existing migration tooling in the project.
- Drupal 11 Migrate API (migrate, migrate_drupal) is the underlying engine; custom source plugins parse mbz XML.

## Acceptance Criteria
- [ ] New module `lms_migrate_moodle` scaffolded with correct naming convention
- [ ] Drush command `drush lms:migrate-moodle <path_to.mbz> [--dry-run]` runs the full import from CLI
- [ ] Admin UI wizard at `/admin/lms/migrate/moodle` — 4 steps: (1) Upload .mbz, (2) Preview parsed content tree, (3) Field mapping confirmation, (4) Run import with real-time progress bar
- [ ] Parses mbz structure: `moodle_backup.xml` (course meta), `sections/` (chapters), `activities/page|resource|quiz/` (lessons/quizzes), `files/` (media)
- [ ] Creates: `lms_course` node, `lms_lesson` nodes (text and video types), `lms_quiz` entities, managed file entities for media
- [ ] Optional user import: if `--import-users` flag set, creates `lms_user` accounts from Moodle user XML (email = username, random password, must-change-password flag)
- [ ] Idempotent: re-running import for the same course (identified by `moodle_course_id` stored as external ref) updates rather than duplicates
- [ ] Import log at `/admin/lms/migrate/moodle/log` showing per-item status (imported, skipped, failed)
- [ ] `--dry-run` mode prints preview without writing to DB

## Technical Notes
- New module: `lms_migrate_moodle` at `web/modules/custom/lms_migrate_moodle/`
- Drupal Migrate API: define `MoodleCourseSource`, `MoodleLessonSource`, `MoodleQuizSource` (plugin classes extending `SourcePluginBase`)
- mbz parsing: PHP `ZipArchive` to extract, `SimpleXMLElement` to parse XML; extract to temp dir in `private://lms_migrate_moodle/`
- Field mapping: Moodle `course.fullname` → `lms_course.title`; `course.summary` → `field_course_description`; activities → lessons (type mapping configurable)
- External ref storage: `field_moodle_source_id` (string) on course and lesson nodes for idempotency
- Progress bar: Drupal Batch API with AJAX progress endpoint
- Media: copy files from mbz `files/` dir to `public://lms_migrate_moodle/{course_id}/`; create `file` entity + `media` entity

## Dependencies
- None blocking (standalone new module)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: mbz parser on sample fixture; functional: end-to-end import of sample mbz, idempotency re-run)
- [ ] No new debt introduced
