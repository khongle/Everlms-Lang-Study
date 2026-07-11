# C12 Workflow - WF-010 AI-assisted content generation

## Business source
[B04 WF-010](../../B-Requirements/B04-business-workflows/WF-010-ai-assisted-content-generation.md)

## Modules
[lms_ai](../C05-modules/lms_ai.md), [lms_course](../C05-modules/lms_course.md), lms_chapter, lms_lesson, [lms_quiz](../C05-modules/lms_quiz.md), lms_question

## Related features (B03)
[F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
generation lifecycle:
| State | Meaning |
| ----- | ------- |
| submitted (prompted) | editor submits topic/prompt + provider |
| in_review (drafted) | provider returns JSON draft stored in private tempstore; no entities created yet |
| approved_or_completed (saved) | editor accepts -> draft course/chapter/lesson/quiz entities created |
| rejected_or_returned (discarded/failed) | provider error or editor discards; no course is persisted |

## Permission model
- Initiator: instructor/content_editor with `create lms_course`.
- Guardrail: human-in-the-loop. Provider output is previewed before entity save.
- Course builder uses `AiProviderManager`; course drafts require a provider with JSON/text support. Pollinations is visible as a provider but fails gracefully for course drafts because it is image-only in this implementation.

## Notifications
Inline result/error in the AI builder; no async notification. Lesson body generation is queued after a valid draft hierarchy is saved. Provider failures degrade gracefully (no corrupt save).

## Round-2 QA verification
- Builder UI (`/manage/courses/ai-builder`, `create lms_course`) is functional and human-in-the-loop for both `instructor` and `content_editor`: Topic / Audience / Tone / Provider → "Generate draft" (preview) before entities are saved. Providers configured: OpenAI (default, gpt-4o) + Gemini. Live generation not re-run in round-2 to avoid consuming the owner's API credits (round-1 course 138 already proves the save path).
- 🚨 **Security (`WF-010-001`, critical, open):** provider API keys (OpenAI + Gemini) are stored **in plaintext in `lms_ai.settings` and committed to `config/sync/lms_ai.settings.yml`** — the concrete manifestation of risk **R-001**. Keys must be rotated and moved to the `key` module / env; not auto-fixed (rotation is owner-only).

## Round-1 QA evidence
- Screenshots: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/010-ai-assisted-content-generation/`
- Final real course: `138` (`Safe Wheelchair Transfer for Beginner Caregivers`), owner uid `56`, draft status `0`.
- Verified hierarchy: course -> 3 chapters -> text lessons + final quiz lesson -> quiz `87`.
