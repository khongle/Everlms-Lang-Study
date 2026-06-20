# PROTO-003 — AI Provider Rate Limit, Cost Cap & Rollback Contract

**Spike Area:** Mở rộng `AiProviderInterface` (đã accepted plugin pattern theo ADR-006) thêm 3 capability đang thiếu: (1) `max_tokens` enforcement để chặn runaway cost (FIND-003), (2) per-user rate limit (FIND-003), (3) compensation/rollback cho partial AI-generated content (RISK-003), đồng thời chuyển Gemini API key từ URL query string sang header (FIND-008).
**Hypothesis:** Có thể bổc các capability này vào `AiProviderInterface` + một `AiGenerationOrchestrator` service (saga-style state machine) **mà không phá pattern plugin hiện tại** — 3 provider hiện tại (`OpenAiProvider`, `GeminiProvider`, `PollinationsProvider`) chỉ cần update method signature, business logic compensation nằm tập trung trong service layer. Token cap + rate limit triggered trong < 5ms overhead.
**Timebox:** 1 ngày (8 giờ)
**Priority:** Medium — không block release ngay (AI features là supportive), nhưng FIND-003 + FIND-008 là High security/cost finds nên phải fix trước CP6.
**Status:** PENDING

---

## Approach

1. **Inventory caller surface** — `git grep -rn "AiProviderManager\|generateJson\|generateImage" web/modules/custom/` để liệt kê tất cả call site. Mong đợi: ≤ 10 call site (course generator, quiz generator, lesson image, SCORM stub).
2. **Định nghĩa interface mới** (proposal):
   ```php
   interface AiProviderInterface extends PluginInspectionInterface {
     public function generateJson(string $prompt, ?string $schema, AiCallOptions $options): AiResult;
     public function generateImage(string $prompt, AiCallOptions $options): AiResult;
   }

   final class AiCallOptions {
     public function __construct(
       public readonly int $maxTokens,         // bắt buộc, enforce ở caller
       public readonly int $timeoutMs,         // default 30s, override per-call
       public readonly string $userId,         // for rate limit bucket
       public readonly string $callerContext,  // 'quiz_gen', 'course_desc', ...
     ) {}
   }

   final class AiResult {
     public function __construct(
       public readonly bool $success,
       public readonly mixed $data,
       public readonly ?string $errorCode,    // 'rate_limited', 'token_cap', 'provider_5xx', ...
       public readonly int $tokensUsed,
       public readonly int $latencyMs,
     ) {}
   }
   ```
3. **Migrate 3 provider plugins** — branch `spike/proto-003-ai-contract`:
   - `OpenAiProvider`: pass `max_tokens` vào request body; bắt response `usage.total_tokens` để return trong `AiResult`.
   - `GeminiProvider`: di chuyển API key từ `?key=` query string sang header `x-goog-api-key` (resolve FIND-008); rotate key sau khi merge.
   - `PollinationsProvider`: nếu provider không support max_tokens server-side → enforce client-side bằng cách truncate response sau token count estimate.
4. **Rate limit layer** — implement `AiRateLimiter` service backed by Redis (đã có trong stack):
   - Key: `lms_ai:rl:{userId}:{providerWindow}` — fixed 60s window, 30 calls/user.
   - Key: `lms_ai:rl:global:{provider}` — provider-wide circuit breaker (1000 calls/min).
   - Test: spam 100 calls/min từ 1 user → kỳ vọng 30 succeed, 70 trả `errorCode = 'rate_limited'`.
5. **Cost cap** — global daily cap $X (config) per provider; counter Redis `lms_ai:cost:{provider}:{YYYYMMDD}`; estimate cost = `tokens × $/1k` per provider model. Triggered → ngắt và log warning.
6. **Compensation orchestrator** (RISK-003) — `AiGenerationOrchestrator::generate(GenerationPlan $plan)` triển khai saga steps:
   ```
   plan = [createCourseDraft, generateChapters, generateLessons, generateQuiz]
   ```
   Mỗi step lưu `compensation_hook` (ví dụ: `deleteCourseDraft($id)`); fail tại step N → run compensation từ N-1 ngược về 0. State persisted trong table `lms_ai_generation_state` (status: `pending|partial|completed|rolled_back`).
7. **Test rollback** — kill provider mid-flow (mock throw exception ở step 3/4); verify (a) course draft xoá, (b) chapter rollback, (c) state = `rolled_back`, (d) không có orphan entity trong DB sau test.
8. **Benchmark** — đo overhead của: (a) `AiCallOptions` serialization, (b) Redis rate limit check, (c) compensation state write. Kỳ vọng tổng overhead < 10ms per call.

---

## Success Criteria

- **Cost protection (FIND-003):**
  - 100% calls đi qua interface mới có `max_tokens` set; OpenAI request payload chứa `max_tokens` (verify in HTTP capture).
  - Rate limit enforcement: ≥ 99% các spam call thứ 31+ trong 60s bị reject.
  - Daily cost counter chính xác trong ±5% so với cost report của provider sau 24h shadow run.
- **Security (FIND-008):**
  - `git grep -n "?key=" web/modules/custom/lms_ai/` không còn match Gemini URL.
  - Captured HTTP request tới Gemini có header `x-goog-api-key`, URL không chứa `key=`.
- **Reliability (RISK-003):**
  - Inject failure tại từng step trong saga (4 steps × 1 = 4 test runs) → mọi run kết thúc state = `rolled_back`, 0 orphan rows.
  - Crash mid-compensation → state = `partial`, có thể resume từ `lms_ai_generation_state` qua cron worker.
- **Performance:** Per-call overhead (rate limit + state write) ≤ 10ms ở P95.
- **Backward compat:** Tất cả 3 plugin compile + test pass; call site update đồng bộ trong 1 commit (no half-migrated state in main).

---

## Dependencies

- Redis available (đã có trong stack — confirm trong infra config).
- OpenAI API key + Gemini API key sandbox (rotated after spike).
- Mock HTTP server (e.g., `wiremock`) để inject failure deterministic.
- ADR-006 ACCEPTED (plugin pattern không thay đổi).
- DB schema mới `lms_ai_generation_state` (Phase 1 spike: tạo trong hook_update_N tạm).
- Quyết định global daily cost cap $X — cần SA confirm trước spike.

---

## Outcome

**Status:** PENDING
**Result:** _(điền sau: rate limit accept/reject counts, latency P95, compensation success rate, FIND-008 grep verification)_
**Decision:** _(điền sau: confirm `AiCallOptions` shape; chốt rate limit window và quota số; rollback strategy = saga compensation hay queue retry)_

### Artifacts

- _(điền sau)_ Branch: `spike/proto-003-ai-contract`
- _(điền sau)_ Saga state diagram: `.agent/generated/proto-003/saga-state.svg`
- _(điền sau)_ Rate limit benchmark: `.agent/generated/proto-003/rate-limit.csv`
- _(điền sau)_ Cost calibration log: `.agent/generated/proto-003/cost-shadow-24h.csv`
- _(điền sau)_ HTTP capture (Gemini header migration): `.agent/generated/proto-003/gemini-headers.txt`

### Impact on Architecture

- **Nếu VALIDATED:** Update ADR-006 §Consequences ghi nhận `AiCallOptions` + `AiResult` value objects + rate limit + cost cap; mở ADR-011 (mới) cho saga compensation pattern; resolve FIND-003, FIND-008; downgrade RISK-003 (Medium → Low/Mitigated). U-047 mở rộng scope thêm rate limit + saga.
- **Nếu INVALIDATED — saga complexity:** Fallback đơn giản hơn — chỉ wrap mỗi top-level AI call trong DB transaction (không multi-step), accept rằng generation chỉ gồm 1 entity creation (split flow ra many independent calls); RISK-003 phải re-classify.
- **Nếu INVALIDATED — Pollinations không support truncate:** Drop Pollinations plugin (low priority provider); document trong ADR-006 §Negative.
- **Nếu rate limit overhead > 10ms:** Move sang in-process LRU cache với background flush; chấp nhận eventual consistency cho global counter.
- **Schema:** `lms_ai_generation_state` cần đưa vào C04-entities/ chính thức (config entity hay content entity?) — quyết định trong ADR-011.

---

## Related

- **Risks:** RISK-003 (G02), FIND-003, FIND-008
- **ADRs:** ADR-006 (AI provider plugin — ACCEPTED, sẽ append consequences); proposed ADR-011 (saga compensation)
- **Implementation reference:**
  - `web/modules/custom/lms_ai/src/Plugin/AiProviderInterface.php` (current)
  - `web/modules/custom/lms_ai/src/Plugin/AiProvider/OpenAiProvider.php`
  - `web/modules/custom/lms_ai/src/Plugin/AiProvider/GeminiProvider.php`
  - `web/modules/custom/lms_ai/src/Plugin/AiProvider/PollinationsProvider.php`
  - `web/modules/custom/lms_ai/src/QueueWorker/` (existing queue worker — reuse for resume-after-crash)

---

## Implementation status (as of 2026-06-20)

**NOT IMPLEMENTED — no rollback, no rate limit, Gemini key still in URL query string.**

Current production state of the AI module:
- **No rollback/compensation:** `CourseBuilderService::generateCourseStructure()` has no saga or compensation mechanism. If creation fails after partial entity creation, orphaned entities remain in the database. `AiEngineUserForm` AJAX handlers also have no rollback — they log errors and count successes/failures but do not delete partially-created entities.
- **No rate limit:** `AiProviderInterface` has no `AiCallOptions` value object, no per-user rate limiter, no cost cap. Any caller can trigger unlimited OpenAI/Gemini API calls.
- **Gemini key in URL (FIND-008):** `GeminiProvider` passes the API key as a URL query parameter (`?key=...`). This is a security issue as it may appear in server logs and proxy access logs. The fix (move to `x-goog-api-key` header) has not been applied.
- **No `max_tokens` enforcement:** OpenAI and Gemini calls do not pass `max_tokens` to the provider API, allowing runaway cost.
- `CourseContentGenerator` queue worker (`lms_ai_content_generator`) is real and retries on exception — this is the only existing partial-failure recovery mechanism.

This spike has **not been executed**. RISK-003 and FIND-003/FIND-008 remain open.
