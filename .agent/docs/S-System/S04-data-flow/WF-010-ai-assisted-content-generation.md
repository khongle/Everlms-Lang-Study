# S04 Data Flow - WF-010 AI-assisted content generation

```text
Editor (AI builder, P044)
   │ submit topic/audience/tone/provider
   ▼
app-drupal: CourseBuilderService::generateDraft() ──▶ AiProviderManager
   │ HTTPS call to configured @AiProvider (OpenAI / Gemini / Pollinations)
   ▼
ext-ai-provider: returns a structured JSON draft
   │ store draft in private tempstore, no entities yet
   ▼
editor accepts → createCourseFromDraft() writes lms_course/chapter/lesson/quiz ──▶ db-mariadb
   │ lesson-body generation queued
   ▼
queue worker fills lesson body ──▶ db update
```
Source: [C12 WF-010](../../C-Design/C12-workflows/WF-010-ai-assisted-content-generation.md). External: AI provider API (OpenAI/Gemini/Pollinations); keys currently plaintext in config (R-001, WF-010-001).
