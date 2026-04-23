# Task: AI Provider Plugin Architecture

**ID:** TASK-012
**Unit:** U-047
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** DONE (75%)
**Completed:** 2026-04-15

## Audit Notes
- AiProviderInterface + AiProviderManager (DefaultPluginManager, Annotation discovery) ✅
- 3 providers: OpenAI (generateJson/Text/Image), Gemini (same), Pollinations (image only) ✅
- AiSettingsForm: provider selection, API keys, model config ✅
- **Known bugs**: `generateText()` NOT in interface but called by all providers + queue worker (duck typing violation). Hardcoded `createInstance('openai')` in CourseBuilderService ignores `default_provider` config. No rate limiting/retry/circuit breaker. Gemini API key in URL query param.

## Description
Xây dựng plugin architecture cho AI providers. Cho phép thêm/chuyển provider (OpenAI, Google AI) mà không sửa business controllers.

## Acceptance Criteria
- [ ] AiProviderInterface với methods: generateCourseStructure(), generateContent(), generateQuiz()
- [ ] Plugin discovery qua Drupal plugin system (Attributes)
- [ ] OpenAI provider implementation
- [ ] Admin config: chọn active provider, API key
- [ ] Provider abstraction: chuyển provider không cần sửa business code
- [ ] Error handling + retry logic
- [ ] Rate limiting per provider

## Technical Notes
- Module: `lms_ai` (mới) hoặc extend `lms_base`
- Plugin: `AiProviderManager` + `@AiProvider` attribute
- Config: `/manage/settings/ai`
- Feature flag: `FF_FEAT_AI_PROVIDER`

## Dependencies
- None (independent)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Provider swap test (switch OpenAI → Google)
- [ ] Error handling tests
