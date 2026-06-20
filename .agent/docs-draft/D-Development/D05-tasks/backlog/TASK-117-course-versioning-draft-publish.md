# Task: Course Versioning — Draft/Published States and Version History

**ID:** TASK-117
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Introduce draft/published versioning for courses so editors can iterate on course content without disrupting learners who are mid-progress. A new version can be created as draft while learners continue on the stable published version. On publish, existing progress records are migrated or flagged for review.

## Context

- Module: `lms_course`
- Drupal's core `ContentEntityType` supports revisions natively; `lms_course` entity needs `revision` = TRUE in annotation.
- Draft state: visible only to admin/instructor. Published state: visible to learners.
- Version bump: admin explicitly publishes draft, triggering optional progress migration.

## Acceptance Criteria

- [ ] `lms_course` entity has `revision = TRUE` with `revision_table` in entity annotation
- [ ] Entity definition adds `published`/`draft` workflow using Drupal core Content Moderation or a custom `status` field approach
- [ ] Editors can save course edits as "Draft" without affecting the published revision learners see
- [ ] Version history tab lists all revisions with timestamp, author, and status
- [ ] Admin can compare diff between two revisions (chapter/lesson titles listed)
- [ ] On publish: system checks if any structural changes (lessons added/removed) and warns admin about progress impact
- [ ] Progress migration option: admin can choose to reset progress for affected learners or preserve as-is
- [ ] Learners mid-progress see published revision; new enrollees get latest published
- [ ] Draft preview accessible to admin/instructor via `/course/{id}/preview`

## Technical Notes

- Entity annotation changes: `web/modules/custom/lms_course/src/Entity/Course.php`
- Add `revision_log_message` field for revision notes
- Progress migration: `web/modules/custom/lms_progress/src/ProgressMigrationService.php` (new)
- Use `hook_entity_revision_create` or entity events to trigger migration logic
- Content Moderation module dependency (Drupal core) is acceptable; alternatively use a lightweight `version_status` field
- This is a large task — split implementation into: (a) revision schema, (b) UI, (c) migration logic

## Dependencies

- None (standalone entity enhancement)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
