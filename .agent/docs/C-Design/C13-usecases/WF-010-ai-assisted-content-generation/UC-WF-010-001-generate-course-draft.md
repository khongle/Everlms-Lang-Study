---
usecase_id: UC-WF-010-001
workflow: WF-010-ai-assisted-content-generation
status: as-built
primary_actor: Instructor
modules: [lms_ai, lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question]
processes: [ai-assisted-content-generation]
---

# UC-WF-010-001 - Generate a course draft with AI

## Intent
Một editor soạn draft nội dung course qua AI, review outline được sinh ra, và lưu thành các entity course chưa publish để review/publish cuối cùng.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-010 |
| Business flow | prompt + provider → draft preview → review → save draft entities |
| Modules | lms_ai, lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question |
| Related features | F-018 |
| B04 source | WF-010 |

## Actors
Instructor/content_editor (primary); AI provider.

## Preconditions
Role authoring có `create lms_course`; đã cấu hình một AI provider (P046). Draft course cần hỗ trợ sinh JSON/text.

## Trigger
Editor submit một topic/prompt trong AI builder.

## Main success scenario
1. Mở AI builder (P044) qua login form thật; nhập topic, audience, tone, và provider.
2. `CourseBuilderService::generateDraft()` route tới `@AiProvider` đã chọn và trả về JSON đã chuẩn hóa.
3. Form lưu draft vào private tempstore và hiện một preview review đã sanitize.
4. Editor click `Save draft course`.
5. `CourseBuilderService::createCourseFromDraft()` tạo một course chưa publish thuộc sở hữu editor, các chapter, lesson text, quiz/question cuối tùy chọn, và một lesson quiz.
6. Các item queue sinh lesson body được tạo sau khi hệ thống phân cấp hợp lệ.
7. Editor được redirect tới course editor để review/publish.

## Alternate and exception flows
- Provider lỗi/timeout hoặc provider không hỗ trợ (ví dụ Pollinations cho draft course dạng JSON) -> thông báo lỗi; retry/đổi provider; không có course nào được lưu.
- Discard -> draft private bị xóa; không tạo entity nào.

## State transitions (evidence)
- `submitted` → `in_review`: provider trả về draft JSON.
- `in_review` → `approved_or_completed`: editor accept, entity course/lesson/quiz được tạo.
- `in_review` → `rejected_or_returned`: provider lỗi hoặc editor discard.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| AI course builder | Manage | /manage/courses/ai-builder |

## Acceptance and test contract
- Provider routing hoạt động đúng; đường lỗi (failure path) được xử lý có kiểm soát; human-in-the-loop trước khi lưu (B05 F-018).
- Draft đã lưu là một hệ thống phân cấp `lms_course.field_chapters -> lms_chapter.field_lessons -> lms_lesson` hợp lệ kèm `lms_quiz`/`lms_question` tùy chọn.
- Round-1 QA: screenshot 001-011 trong thư mục screen của WF-010; course cuối cùng `138`.
