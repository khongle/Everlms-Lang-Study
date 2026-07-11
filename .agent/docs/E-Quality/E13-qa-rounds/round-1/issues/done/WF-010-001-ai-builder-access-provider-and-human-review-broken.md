---
id: WF-010-001
workflow: WF-010
title: AI builder blocks instructor access, hardcodes provider, and skips review-before-save
severity: high
category: workflow
status: done
roles: instructor, content_editor
source: code and document review
found: 2026-06-30
---

# WF-010-001 - AI builder access/provider/review flow broken

## Problem
The AI course builder route and form do not match C12/C13 or B05 F-018:

- `/manage/courses/ai-builder` requires `administer lms_course`, blocking the
  documented instructor/content_editor actors.
- The form does not expose the documented provider selector.
- `CourseBuilderService` hardcodes `openai`, ignoring configured/default
  provider routing through `AiProviderManager`.
- Submitting the form immediately creates entities instead of returning a
  reviewable draft that the editor explicitly saves.

## Evidence
- `web/modules/custom/lms_ai/lms_ai.routing.yml`
- `web/modules/custom/lms_ai/src/Form/CourseBuilderForm.php`
- `web/modules/custom/lms_ai/src/Service/CourseBuilderService.php`
- C12 WF-010 and C13 UC-WF-010-001 require
  `prompt -> draft -> review -> save`.

## Impact
The documented primary actors cannot complete WF-010, provider routing cannot
be verified, and failed/rejected drafts risk being persisted before human
review.

## Expected
Instructors/content editors with course creation access can open the builder,
select a supported provider, generate a reviewable draft without creating
entities, then explicitly save the reviewed draft.

## Fix Plan
- Change route access to course creation permission.
- Add provider selection backed by `AiProviderManager`.
- Split generation from persistence in the course builder service.
- Store the generated draft in private tempstore until the editor saves.
- Retest with real `/user/login` form as an instructor.

## Resolution
- `/manage/courses/ai-builder` now requires `create lms_course`, matching
  instructor/content_editor authoring permissions.
- `CourseBuilderForm` exposes provider selection from `AiProviderManager`.
- `CourseBuilderService` now separates `generateDraft()` from
  `createCourseFromDraft()`.
- Generated drafts are held in private tempstore until the editor explicitly
  clicks `Save draft course`.

## Verification
- Retested with real `/user/login` form as `sabine.mueller`.
- Final WF-010 screenshots: `001` through `011`.
- Final saved course: `138`, draft status `0`.
