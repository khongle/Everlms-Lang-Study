# D03 — Unit Spec: AI Provider Plugin Architecture

**Unit ID:** U-047
**Group:** G-INTEGRATION
**Title:** AI provider plugin architecture
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned
**Feature Flag:** FF_FEAT_AI_PROVIDER

---

## Scope

Fix known bugs and complete the AI provider plugin system. AiProviderInterface must be complete, provider discovery must use config (not hardcoded), and all providers must implement the full interface. This unit remediates the issues found in TASK-012 audit.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Plugin/AiProvider/AiProviderInterface.php` | Modify | lms_ai |
| `src/Plugin/AiProvider/OpenAiProvider.php` | Modify | lms_ai |
| `src/Plugin/AiProvider/GeminiProvider.php` | Modify | lms_ai |
| `src/Plugin/AiProvider/PollinationsProvider.php` | Modify | lms_ai |
| `src/AiProviderManager.php` | Modify | lms_ai |
| `src/Service/CourseBuilderService.php` | Modify | lms_ai |
| `src/Form/AiSettingsForm.php` | Modify | lms_ai |

---

## Interfaces / Services

```php
interface AiProviderInterface {
  public function generateCourseStructure(string $prompt): array;
  public function generateContent(string $prompt): string;
  public function generateQuiz(string $topic, int $count): array;
  public function generateText(string $prompt): string;  // ADD to interface
  public function generateImage(string $prompt): string; // already in some providers
}
```

- `AiProviderManager::getActiveProvider(): AiProviderInterface` — reads from config `lms_ai.settings:default_provider` (NOT hardcoded `openai`)
- Gemini: move API key to Authorization header (NOT URL query param)
- All providers: add retry logic (3 attempts, exponential backoff)
- Rate limiting: `lms_ai.settings:requests_per_minute` per provider

---

## Known Bugs to Fix (from TASK-012)

1. `generateText()` not in interface → add to interface, all providers implement
2. `createInstance('openai')` hardcoded in `CourseBuilderService` → use `getActiveProvider()`
3. Gemini API key in URL param → move to Authorization header
4. No rate limiting or retry → implement

---

## Acceptance Criteria

- [ ] `AiProviderInterface` declares all methods used by callers
- [ ] `CourseBuilderService` uses `getActiveProvider()` — switching provider in admin config changes behavior
- [ ] Gemini API key sent in `Authorization: Bearer {key}` header
- [ ] All providers retry 3× with exponential backoff on 429/503
- [ ] Rate limit enforced per provider (configurable, default 60 RPM)
- [ ] Feature flag `FF_FEAT_AI_PROVIDER` gates all AI routes

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Provider swap test: switch OpenAI → Gemini |
| G4 Adversarial | PENDING | API key exposure audit |

---

## Dependencies

- None (independent)

**Feature Flag:** FF_FEAT_AI_PROVIDER
**Risk Ref:** RISK-003

*End of U-047-ai-provider-plugin.md*
