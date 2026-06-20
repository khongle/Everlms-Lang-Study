# WF-010 - AI-assisted content generation

## Business flow
An instructor/editor opens the AI assistant (F-018) and provides a topic/prompt. `CourseContentGenerator` calls the configured `@AiProvider` (OpenAI / Gemini / Pollinations via `AiProviderManager`) and returns a structured **draft** — course outline, lesson bodies, and/or quiz questions. The editor reviews, edits, and saves the draft into real entities (course/lesson/quiz). If the provider call fails, the flow degrades gracefully (error surfaced, no partial corruption).

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md), [lms_course](../../C-Design/C05-modules/lms_course.md), [lms_quiz](../../C-Design/C05-modules/lms_quiz.md)

## Related features (B03)
[F-018](../B03-features/F-018-ai-authoring-assistant.md), [F-002](../B03-features/F-002-course-authoring-and-structure.md), [F-006](../B03-features/F-006-quiz-and-assessment-engine.md)

## Trigger
Editor requests AI generation from a prompt/topic.

## Outcome
A reviewable content draft the editor can accept into course/lesson/quiz entities; human stays in the loop.

## Design link
[C12 WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md)
