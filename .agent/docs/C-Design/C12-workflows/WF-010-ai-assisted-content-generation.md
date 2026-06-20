# C12 Workflow - WF-010 AI-assisted content generation

## Business source
[B04 WF-010](../../B-Requirements/B04-business-workflows/WF-010-ai-assisted-content-generation.md)

## Modules
[lms_ai](../C05-modules/lms_ai.md), [lms_course](../C05-modules/lms_course.md), [lms_quiz](../C05-modules/lms_quiz.md)

## Related features (B03)
[F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
generation lifecycle:
| State | Meaning |
| ----- | ------- |
| submitted (prompted) | editor submits topic/prompt |
| in_review (drafted) | provider returns draft for review |
| approved_or_completed (saved) | editor accepts → entities created |
| rejected_or_returned (discarded/failed) | provider error or editor discards |

## Permission model
- Initiator: instructor/content_editor.
- Guardrail: human-in-the-loop — draft must be reviewed before save.

## Notifications
Inline result/error in the AI builder; no async notification. Provider failures degrade gracefully (no corrupt save).
