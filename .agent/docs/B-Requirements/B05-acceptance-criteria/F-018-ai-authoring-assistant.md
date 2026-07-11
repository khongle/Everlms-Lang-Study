# F-018 Acceptance Criteria - AI authoring assistant

## Core criteria
1. **Given** một provider đã được cấu hình, **when** editor submit một topic/prompt, **then** `CourseContentGenerator` trả về một draft có cấu trúc (outline/lesson/quiz).
2. **Given** nhiều provider đã được cấu hình, **when** admin chọn một provider, **then** `AiProviderManager` route call đến `@AiProvider` đó (OpenAI/Gemini/Pollinations).
3. **Given** một draft được trả về, **when** editor lưu nó, **then** các entity course/lesson/quiz thật được tạo từ draft (human-in-the-loop).
4. **Given** provider bị lỗi/timeout, **when** generation thất bại, **then** lỗi được surface một cách graceful, không gây partial corruption.

## Evidence expected
- Module: [lms_ai](../../C-Design/C05-modules/lms_ai.md).
- Test: provider-routing unit test với `@AiProvider` được stub; failure-path test (mock error); đã ghi nhận secret nằm trong config (R-001). <!-- credential-sweep:allow: risk mention R-001 -->
