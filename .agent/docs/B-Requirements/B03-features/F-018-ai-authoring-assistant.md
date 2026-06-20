# F-018 - AI authoring assistant

**Status:** built
**Primary module:** [lms_ai](../../C-Design/C05-modules/lms_ai.md)
**Epic:** [E10 AI](../../D-Development/D02-epics/E10-ai.md)
**Related workflows:** [WF-010 AI-assisted content generation](../B04-business-workflows/WF-010-ai-assisted-content-generation.md)
**Acceptance:** [B05 F-018](../B05-acceptance-criteria/F-018-ai-authoring-assistant.md)
**Source:** `web/modules/custom/lms_ai/`

## Requirement
Instructors/editors use AI to draft course content, lessons, and quiz questions. A pluggable `@AiProvider` host (`AiProviderManager`) supports **OpenAI**, **Google Gemini**, and **Pollinations**; `CourseContentGenerator` turns a prompt/topic into structured course/lesson/quiz drafts the editor can review and save.

## Business value
AI authoring slashes course-production time and lowers the barrier for subject-matter experts to publish, directly increasing catalog growth.

## Scope notes
- Provider abstraction lets admins choose/swap LLM backends; keys configured per provider (⚠️ in config YAML, R-001).
- Output is a **draft** — human review/edit before publish (editorial-usability preserved).
- Generation is a server-side call; failures must degrade gracefully (INT contract).

## Cross references
- Design: [C05 lms_ai](../../C-Design/C05-modules/lms_ai.md), [C06 INT-005 OpenAI](../../C-Design/C06-apis/INT-005-openai.md), [C06 INT-006 Gemini](../../C-Design/C06-apis/INT-006-google-gemini.md), [C06 INT-007 Pollinations](../../C-Design/C06-apis/INT-007-pollinations.md), [C12 WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md)
