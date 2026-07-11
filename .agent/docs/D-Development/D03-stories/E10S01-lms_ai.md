---
story_id: E10S01
story_type: module-foundation
primary_source: C05-modules/lms_ai.md
owning_modules: [lms_ai]
adequacy: PASS
---

# E10S01 - lms_ai build spine

Host `@AiProvider` + CourseContentGenerator (OpenAI/Gemini/Pollinations). **Status:** done.

## Tasks
| Task | Status |
| ---- | ------ |
| Provider host + 3 providers | resolved |
| Course content generator + UI | resolved |
| Keys → Key/env | todo (R-001) |

## Links
[C05](../../C-Design/C05-modules/lms_ai.md) · [WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md) · [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## Source Trace
- **Module:** `lms_ai` — [C05](../../C-Design/C05-modules/lms_ai.md) · [D07](../D07-code-map/lms_ai.md)
- **Entity:** n/a — host `@AiProvider` plugin, ghi kết quả generate trực tiếp vào `lms_course`/`lms_lesson`/`lms_article`
- **Feature:** [F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md) ai-authoring-assistant
- **Workflow:** [WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md) ai-assisted-content-generation

## WBS Tasks
| Task | Trạng thái |
| ---- | ---------- |
| `E10S01T01` Provider host + 3 provider (OpenAI/Gemini/Pollinations) | resolved |
| `E10S01T02` Course content generator + UI | resolved |
| `E10S01T03` Keys → Key/env | todo (R-001) |

## Adequacy Checklist
- [x] Entity: n/a
- [x] Route: content-generator UI route đã resolved
- [x] Access/permission: chỉ instructor/admin dùng generator, đã resolved
- [ ] Test: API key vẫn ở config YAML (R-001); chưa có automated test — R-006/X01
