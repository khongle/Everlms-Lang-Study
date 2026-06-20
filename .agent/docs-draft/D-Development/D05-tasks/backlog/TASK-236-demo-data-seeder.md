# Task: Demo Data Seeder

**ID:** TASK-236
**Unit:** U-010 (base)
**Group:** G-DX
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Create a Drush command `drush lms:demo-seed` that populates a fresh EverLMS install with realistic demo data: 5 sample courses (one per difficulty level), 50 sample lessons distributed across those courses, 5 quizzes covering every question type, 10 users in every role, and sample orders + certificates. Enables rapid demo environments and local dev bootstrapping.

## Context
- `lms_base` is the appropriate home for cross-module demo seeding logic.
- No demo data tooling currently exists; new installs require hours of manual content creation.
- TASK-237 (user import templates) shares the user creation pattern.

## Acceptance Criteria
- [ ] `drush lms:demo-seed` Drush command exists, documented with `--help`
- [ ] Command options: `--reset` (wipe existing demo data and re-seed), `--courses=N` (default 5), `--dry-run` (print plan without writing)
- [ ] Creates 5 courses: Beginner, Elementary, Intermediate, Advanced, Expert — each with a category, description, and course image (placeholder SVG)
- [ ] Creates 50 lessons distributed across 5 courses (10 per course): mix of video, text, quiz, assignment types
- [ ] Creates 5 quizzes: one per question type (multiple choice, true/false, fill-in-blank, matching, essay)
- [ ] Creates 10 users: 1 platform admin, 2 instructors, 5 students, 1 org admin, 1 guest; each with a deterministic password `Demo1234!` documented in seeder output
- [ ] Creates sample orders: 3 completed orders per student (linked to courses)
- [ ] Creates sample certificates: one per completed order
- [ ] All demo entities tagged with `field_is_demo = true` (boolean) for easy identification and cleanup
- [ ] `--reset` flag deletes all entities where `field_is_demo = true` before re-seeding

## Technical Notes
- Module: `lms_base`
- Drush command: `lms_base/src/Commands/DemoSeedCommands.php` annotated with `@command lms:demo-seed`
- Service: `DemoSeederService` injected into the Drush command; each entity type has a factory method: `seedCourse()`, `seedLesson()`, `seedUser()`, etc.
- Placeholder images: SVG data URIs stored as constants in `DemoSeederService::DEMO_IMAGE_{LEVEL}` — no external HTTP calls
- Order + certificate seeding: call existing `lms_order` and `lms_certificate` service APIs to avoid bypassing business logic
- Demo flag: `field_is_demo` added to all relevant entity types via `lms_base.install` `hook_update_N` or `hook_field_schema`
- Idempotency: check for existing demo data before creating; skip if `--reset` not passed

## Dependencies
- None blocking (seeds all modules from base)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: each seed factory method; functional: full seed run on clean install, --reset wipes correctly)
- [ ] No new debt introduced
