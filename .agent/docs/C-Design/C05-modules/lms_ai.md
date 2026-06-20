# C05 Module - lms_ai

**Name:** AI authoring · **Phase:** 10 · **Epic:** [E10](../../D-Development/D02-epics/E10-ai.md) · **Status:** done

## Responsibility
The `@AiProvider` **plugin host** (`AiProviderManager` + `AiProviderInterface`) with OpenAI / Gemini / Pollinations providers, plus `CourseContentGenerator` to turn prompts into course/lesson/quiz drafts. ~4 routes.

## Owned / related entities
- None (generates content into other modules' entities).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md) | AI authoring assistant |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-010](../../B-Requirements/B04-business-workflows/WF-010-ai-assisted-content-generation.md) | AI-assisted content generation |

## Integrations / APIs (B06 / C06)
- [INT-005 OpenAI](../C06-apis/INT-005-openai.md), [INT-006 Gemini](../C06-apis/INT-006-google-gemini.md), [INT-007 Pollinations](../C06-apis/INT-007-pollinations.md)

## Backing pages
| Label | URL |
| ----- | --- |
| AI generate (in authoring) | `/ai/generate` |

## Cross references
- Used by [lms_blog](lms_blog.md), [lms_zoom](lms_zoom.md); generates into [lms_course](lms_course.md)/[lms_quiz](lms_quiz.md).

## Dependencies
- lms_base, **key** (declared dep; ⚠️ no key entities configured — AI keys live in `lms_ai.settings.yml`/`lms_ai.engine.settings.yml`, R-001).

## Implementation notes
- `src/Plugin/AiProvider/{OpenAiProvider,GeminiProvider,PollinationsProvider}.php` + `AiProviderManager`; `CourseContentGenerator` builds structured drafts. Human-in-the-loop: output is a draft to review before save.
