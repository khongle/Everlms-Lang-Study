# C12 Workflow - WF-010 AI-assisted content generation

**Object:** course

## Business source
[B04 WF-010](../../B-Requirements/B04-business-workflows/WF-010-ai-assisted-content-generation.md)

## Modules
[lms_ai](../C05-modules/lms_ai.md), [lms_course](../C05-modules/lms_course.md), lms_chapter, lms_lesson, [lms_quiz](../C05-modules/lms_quiz.md), lms_question

## Related features (B03)
[F-018](../../B-Requirements/B03-features/F-018-ai-authoring-assistant.md)

## State model
vòng đời của generation:
| State | Meaning |
| ----- | ------- |
| submitted (prompted) | editor submits topic/prompt + provider |
| in_review (drafted) | provider returns JSON draft stored in private tempstore; no entities created yet |
| approved_or_completed (saved) | editor accepts -> draft course/chapter/lesson/quiz entities created |
| rejected_or_returned (discarded/failed) | provider error or editor discards; no course is persisted |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| submitted | in_review | provider trả về draft JSON |
| in_review | approved_or_completed | editor accept, entity course/lesson/quiz được tạo |
| in_review | rejected_or_returned | provider lỗi hoặc editor discard |

## Permission model
- Initiator: instructor/content_editor có quyền `create lms_course`.
- Guardrail: human-in-the-loop. Output của provider được preview trước khi lưu entity.
- Course builder dùng `AiProviderManager`; draft course cần một provider hỗ trợ JSON/text. Pollinations vẫn hiện ra như một provider nhưng fail có kiểm soát (graceful) với draft course vì trong triển khai này nó chỉ hỗ trợ image.

## Notifications
Kết quả/lỗi hiển thị inline trong AI builder; không có notification bất đồng bộ (async). Việc generate lesson body được đưa vào queue sau khi hệ thống phân cấp draft hợp lệ đã được lưu. Lỗi provider được xử lý có kiểm soát (không làm hỏng dữ liệu đã lưu).

## Round-2 QA verification
- Builder UI (`/manage/courses/ai-builder`, `create lms_course`) hoạt động tốt và có human-in-the-loop cho cả `instructor` lẫn `content_editor`: Topic / Audience / Tone / Provider → "Generate draft" (preview) trước khi entity được lưu. Provider đã cấu hình: OpenAI (mặc định, gpt-4o) + Gemini. Không chạy lại live generation ở round-2 để tránh tốn API credit của owner (course 138 ở round-1 đã chứng minh được đường lưu dữ liệu).
- 🚨 **Security (`WF-010-001`, critical, còn mở):** API key của provider (OpenAI + Gemini) đang được lưu **dạng plaintext trong `lms_ai.settings` và commit vào `config/sync/lms_ai.settings.yml`** — đây là biểu hiện cụ thể của rủi ro **R-001**. Cần xoay vòng (rotate) key và chuyển sang module `key` / env; không tự động fix (việc rotate chỉ owner mới làm được).

## Round-1 QA evidence
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/010-ai-assisted-content-generation/`
- Course thật cuối cùng: `138` (`Safe Wheelchair Transfer for Beginner Caregivers`), owner uid `56`, draft status `0`.
- Hệ thống phân cấp đã verify: course -> 3 chapter -> lesson text + lesson quiz cuối -> quiz `87`.
