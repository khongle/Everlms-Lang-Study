---
usecase_id: UC-WF-010-001
workflow: WF-010-ai-assisted-content-generation
status: as-built
primary_actor: Instructor
modules: [lms_ai, lms_course, lms_quiz]
processes: [ai-assisted-content-generation]
---

# UC-WF-010-001 - Generate a course draft with AI

## Intent
An editor drafts course content via AI, reviews, and saves.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-010 |
| Business flow | prompt → draft → review → save |
| Modules | lms_ai, lms_course, lms_quiz |
| Related features | F-018 |
| B04 source | WF-010 |

## Actors
Instructor/content_editor (primary); AI provider.

## Preconditions
Authoring role; an AI provider configured (P046).

## Trigger
Editor submits a topic/prompt in the AI builder.

## Main success scenario
1. Open AI builder (P044); enter prompt + select provider.
2. `CourseContentGenerator` returns a structured draft.
3. Editor reviews/edits.
4. Save → course/lesson/quiz entities created.

## Alternate and exception flows
- Provider error/timeout → graceful message; retry/switch provider; no corrupt save.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| AI course builder | Manage | /manage/courses/ai-builder |

## Acceptance and test contract
- Provider routing works; failure path graceful; human-in-the-loop before save (B05 F-018).
