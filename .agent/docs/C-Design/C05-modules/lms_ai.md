# C05 Module - lms_ai

**Name:** AI authoring · **Phase:** 10 · **Epic:** [E10](../../D-Development/D02-epics/E10-ai.md) · **Status:** done

## Responsibility
**Plugin host** `@AiProvider` (`AiProviderManager` + `AiProviderInterface`) với các provider OpenAI / Gemini / Pollinations, cùng `CourseContentGenerator` để biến prompt thành bản nháp course/lesson/quiz. ~4 route.

## Owned / related entities
- Không có (sinh nội dung vào entity của các module khác).

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
- Được dùng bởi [lms_blog](lms_blog.md), [lms_zoom](lms_zoom.md); sinh nội dung vào [lms_course](lms_course.md)/[lms_quiz](lms_quiz.md).

## Dependencies
- lms_base, **key** (dependency đã khai báo; ⚠️ chưa cấu hình key entity nào — key AI nằm trong `lms_ai.settings.yml`/`lms_ai.engine.settings.yml`, R-001).

## Implementation notes
- `src/Plugin/AiProvider/{OpenAiProvider,GeminiProvider,PollinationsProvider}.php` + `AiProviderManager`; `CourseContentGenerator` tạo bản nháp có cấu trúc. Human-in-the-loop: kết quả là bản nháp cần review trước khi lưu.
