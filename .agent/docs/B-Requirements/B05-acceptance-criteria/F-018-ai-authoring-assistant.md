# F-018 Acceptance Criteria - AI authoring assistant

## Core criteria
1. **Given** a configured provider, **when** an editor submits a topic/prompt, **then** `CourseContentGenerator` returns a structured draft (outline/lessons/quiz).
2. **Given** multiple providers configured, **when** the admin selects one, **then** `AiProviderManager` routes the call to that `@AiProvider` (OpenAI/Gemini/Pollinations).
3. **Given** a returned draft, **when** the editor saves it, **then** real course/lesson/quiz entities are created from the draft (human-in-the-loop).
4. **Given** a provider error/timeout, **when** generation fails, **then** the failure is surfaced gracefully with no partial corruption.

## Evidence expected
- Module: [lms_ai](../../C-Design/C05-modules/lms_ai.md).
- Test: provider-routing unit test with a stubbed `@AiProvider`; failure-path test (mock error); secrets via config noted (R-001).
