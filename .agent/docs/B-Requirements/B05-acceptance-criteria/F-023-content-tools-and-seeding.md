# F-023 Acceptance Criteria - Content tools & seeding

## Core criteria
1. **Given** a fresh install, **when** an admin runs `lms:import` (from `.agent/generated/*.json`), **then** the documented catalog (courses/quizzes/users/etc.) is created.
2. **Given** seeded content, **when** `lms:clean` runs, **then** generated content is removed without breaking the install.
3. **Given** `lms:generate-demo`, **when** run, **then** procedural demo courses/chapters/lessons are created.
4. **Given** the `SeedInputForm` UI, **when** an admin submits seed input, **then** it is processed by `ContentToolsManager`.

## Evidence expected
- Module: [lms_content_tools](../../C-Design/C05-modules/lms_content_tools.md); inputs SRC-10/11.
- Test: import→count assertion vs live DB (40 courses/600 lessons); clean idempotency test (E03).
