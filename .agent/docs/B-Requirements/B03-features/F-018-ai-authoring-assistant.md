# F-018 - AI authoring assistant

**Status:** built
**Primary module:** [lms_ai](../../C-Design/C05-modules/lms_ai.md)
**Epic:** [E10 AI](../../D-Development/D02-epics/E10-ai.md)
**Related workflows:** [WF-010 AI-assisted content generation](../B04-business-workflows/WF-010-ai-assisted-content-generation.md)
**Acceptance:** [B05 F-018](../B05-acceptance-criteria/F-018-ai-authoring-assistant.md)
**Source:** `web/modules/custom/lms_ai/`

## Requirement
Instructor/editor dùng AI để soạn nháp (draft) nội dung course, lesson và quiz question. Một plugin host dạng pluggable `@AiProvider` (`AiProviderManager`) hỗ trợ **OpenAI**, **Google Gemini** và **Pollinations**; `CourseContentGenerator` biến một prompt/topic thành bản draft course/lesson/quiz có cấu trúc để editor review và save.

## Business value
AI authoring cắt giảm mạnh thời gian sản xuất course và hạ thấp rào cản để chuyên gia lĩnh vực (subject-matter expert) có thể publish, trực tiếp thúc đẩy tăng trưởng catalog.

## Scope notes
- Lớp abstraction cho provider cho phép admin chọn/đổi LLM backend; key được cấu hình theo từng provider (⚠️ nằm trong config YAML, R-001).
- Output là một **draft** — cần con người review/edit trước khi publish (giữ nguyên editorial-usability).
- Việc generation là một server-side call; khi lỗi phải degrade gracefully (theo INT contract).

## Cross references
- Design: [C05 lms_ai](../../C-Design/C05-modules/lms_ai.md), [C06 INT-005 OpenAI](../../C-Design/C06-apis/INT-005-openai.md), [C06 INT-006 Gemini](../../C-Design/C06-apis/INT-006-google-gemini.md), [C06 INT-007 Pollinations](../../C-Design/C06-apis/INT-007-pollinations.md), [C12 WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md)
