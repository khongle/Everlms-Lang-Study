# WF-010 - AI-assisted content generation

## Business flow
Instructor/editor mở AI assistant (F-018) và cung cấp một topic/prompt. `CourseContentGenerator` gọi `@AiProvider` đã cấu hình (OpenAI / Gemini / Pollinations qua `AiProviderManager`) và trả về một **draft** có cấu trúc — course outline, nội dung lesson, và/hoặc câu hỏi quiz. Editor review, chỉnh sửa, rồi lưu draft thành các entity thực (course/lesson/quiz). Nếu lời gọi tới provider thất bại, luồng xử lý sẽ giảm cấp một cách an toàn (graceful degrade — hiển thị lỗi, không gây hỏng dữ liệu một phần).

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md), [lms_course](../../C-Design/C05-modules/lms_course.md), [lms_quiz](../../C-Design/C05-modules/lms_quiz.md)

## Related features (B03)
[F-018](../B03-features/F-018-ai-authoring-assistant.md), [F-002](../B03-features/F-002-course-authoring-and-structure.md), [F-006](../B03-features/F-006-quiz-and-assessment-engine.md)

## Trigger
Editor yêu cầu AI generate nội dung từ một prompt/topic.

## Outcome
Một draft nội dung có thể review mà editor có thể chấp nhận vào các entity course/lesson/quiz; con người luôn nằm trong vòng kiểm soát (human-in-the-loop).

## Design link
[C12 WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md)
