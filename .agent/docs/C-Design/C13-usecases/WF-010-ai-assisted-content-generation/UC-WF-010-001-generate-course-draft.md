---
usecase_id: UC-WF-010-001
workflow: WF-010-ai-assisted-content-generation
status: as-built
primary_actor: Instructor
modules: [lms_ai, lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question]
processes: [ai-assisted-content-generation]
---

# UC-WF-010-001 - Generate a course draft with AI

## Intent
An editor drafts course content via AI, reviews the generated outline, and saves it as unpublished course entities for final review/publishing.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-010 |
| Business flow | prompt + provider → draft preview → review → save draft entities |
| Modules | lms_ai, lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question |
| Related features | F-018 |
| B04 source | WF-010 |

## Actors
Instructor/content_editor (primary); AI provider.

## Preconditions
Authoring role with `create lms_course`; an AI provider configured (P046). Course drafts require JSON/text generation support.

## Trigger
Editor submits a topic/prompt in the AI builder.

## Main success scenario
1. Open AI builder (P044) through real form login; enter topic, audience, tone, and provider.
2. `CourseBuilderService::generateDraft()` routes to the selected `@AiProvider` and returns normalized JSON.
3. The form stores the draft in private tempstore and shows a sanitized review preview.
4. Editor clicks `Save draft course`.
5. `CourseBuilderService::createCourseFromDraft()` creates an unpublished course owned by the editor, chapters, text lessons, optional final quiz/questions, and a quiz lesson.
6. Lesson body generation queue items are created after the hierarchy is valid.
7. Editor is redirected to the course editor for review/publish.

## Alternate and exception flows
- Provider error/timeout or unsupported provider (for example Pollinations for JSON course drafts) -> error message; retry/switch provider; no matching course is saved.
- Discard -> private draft removed; no entities created.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| AI course builder | Manage | /manage/courses/ai-builder |

## Acceptance and test contract
- Provider routing works; failure path graceful; human-in-the-loop before save (B05 F-018).
- Saved draft is a valid `lms_course.field_chapters -> lms_chapter.field_lessons -> lms_lesson` hierarchy with optional `lms_quiz`/`lms_question`.
- Round-1 QA: screenshots 001-011 under WF-010 screen folder; final course `138`.
