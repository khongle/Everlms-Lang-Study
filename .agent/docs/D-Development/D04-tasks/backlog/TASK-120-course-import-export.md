# Task: Course Import/Export (JSON/YAML)

**ID:** TASK-120
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow admins and instructors to export a course (with chapters, lessons, and quiz structure) to a JSON or YAML file, and re-import that file on the same or a different EverLMS instance. Useful for course library exchange and rapid re-use. Video and binary files are excluded; only structure and text content are exported.

## Context

- Module: `lms_course` (admin tools)
- Export scope: Course entity fields, Chapter entities, Lesson entities (text content + metadata), Quiz entities with Question entities (no media binaries)
- Import: creates new entities; does not overwrite existing by default

## Acceptance Criteria

- [ ] Export action available on course admin list and course edit page → downloads `course-{slug}-{date}.json`
- [ ] Exported JSON includes: course metadata, ordered chapters, ordered lessons per chapter, quiz definitions with all questions and answer options
- [ ] Video/image/file field values are excluded; a placeholder note is inserted in their place
- [ ] Import form at `/admin/lms/course/import` accepts JSON/YAML file upload
- [ ] Import validates schema version before processing; reports field-level errors
- [ ] Import runs as batch operation for courses with >20 lessons
- [ ] Duplicate detection: if a course with same `uuid` exists, offer overwrite or create-new options
- [ ] Imported course created in draft state; admin must publish manually
- [ ] Schema version field (`_schema_version`) included in export for forward/backward compat checks

## Technical Notes

- New service: `web/modules/custom/lms_course/src/CoursePorterService.php` (export + import methods)
- New form: `web/modules/custom/lms_course/src/Form/CourseImportForm.php`
- New route: `lms_course.import` → `/admin/lms/course/import`
- Export format: JSON preferred (YAML optional via library); use `json_encode` with `JSON_PRETTY_PRINT`
- Entity serialization: use Drupal's `Serializer` service with `NormalizerInterface`
- On import: use `EntityTypeManager::getStorage()->create()` per entity type in dependency order (course → chapters → lessons → quizzes)
- Schema version: start at `1.0`

## Dependencies

- TASK-117 (draft state for imported courses)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
