---
workflow: WF-006
title: Course Creation (Manual and AI-Assisted) — Use-Case Catalog
status: as-built
---

# C13 — WF-006: Course Creation — Use-Case Catalog

Use cases covering both manual course creation via the workspace admin UI and the AI-assisted course builder. Both paths produce entities in the same curriculum hierarchy: `lms_course` → `lms_chapter` (via `field_chapters`) → `lms_lesson` (via `field_lessons`). AI paths use the `lms_ai` plugin system (OpenAI or Gemini) with background queue workers for content generation.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF006-001-manual-course-create.md](UC-WF006-001-manual-course-create.md) | UC-WF006-001 | happy | Admin manually creates a course, chapter, and lesson via the workspace UI |
| [UC-WF006-002-ai-course-builder-partial-failure.md](UC-WF006-002-ai-course-builder-partial-failure.md) | UC-WF006-002 | error | AI course builder partially creates entities then fails — no rollback leaves orphaned drafts |
| [UC-WF006-003-permission-non-admin-blocked.md](UC-WF006-003-permission-non-admin-blocked.md) | UC-WF006-003 | permission | Authenticated user without `administer lms_course` is blocked from course creation routes |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-006-course-creation.md](../../../B-Requirements/B04-business-workflows/WF-006-course-creation.md)
- Acceptance criteria (AI): [../../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md](../../../B-Requirements/B05-acceptance-criteria/AC-14-ai-features.md)
- Acceptance criteria (course management): [../../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md](../../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md)
- Primary modules: `lms_course`, `lms_chapter`, `lms_lesson`, `lms_ai`, `lms_zoom`
- Entities mutated: `lms_course`, `lms_chapter`, `lms_lesson`, `lms_quiz` (optional), `lms_certificate_template` (optional reference)

## Known gaps

| Gap | Detail |
|-----|--------|
| No rollback on partial AI failure | `CourseBuilderService::generateCourseStructure()` creates entities before all steps complete; on exception, orphaned drafts remain in DB (no saga/compensation) |
| AI key in config | OpenAI and Gemini API keys stored in plaintext config YAML — `«secret in config — see security risk»` |
| `AiEngineUserForm` saves JSON to module-level files | `JSON/ai_curriculum.json` and `JSON/ai_users.json` stored inside the `lms_ai` module directory — not suitable for multi-server or containerized deployments |
| VTT stub in `ZoomAiService::processSummaries()` | Post-meeting transcript download uses a hardcoded simulated VTT string instead of real HTTP download from Zoom recordings API |
| `google_slide_format.txt` possibly missing | `GoogleSlideBuilderService` references this prompt file but it was not confirmed in the module file listing |
