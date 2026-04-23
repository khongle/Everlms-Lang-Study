# AC-10 — External Integrations

**Feature ID:** F-010
**Epic:** EPIC-H — AI & Integrations
**Epic Owner:** System / Admin
**Related Workflow:** [WF-004 — Order Management](../B04-business-workflows/WF-004-order-management.md), [WF-006 — Course Creation](../B04-business-workflows/WF-006-course-creation.md)
**Feature Doc:** [B03 F-010 Integrations](../B03-features/10-integrations.md)

---

## Acceptance Criteria

### AC-H01 — AI provider abstraction

**Priority:** Must
**Given** System dùng plugin-based AI provider architecture
**When** Admin switch từ OpenAI sang Google Gemini trong config
**Then** Course generation flow hoạt động với provider mới, không cần sửa business code
**And** Không có hardcoded provider references trong controllers/services

### AC-H02 — AI generation error handling & retry

**Priority:** Must
**Given** AI generation flow đang chạy (generate 20 lessons) và provider timeout ở lesson 10
**When** Timeout/error xảy ra
**Then** System retry với exponential backoff (max 3 lần)
**And** Nếu vẫn fail: rollback all partial content tạo ra; instructor nhận error notification
**And** State machine ghi nhận failed state; không để partial course

### AC-H03 — Zoom integration

**Priority:** Must
**Given** Lesson type = Zoom được cấu hình với Zoom API credentials
**When** Instructor tạo Zoom lesson
**Then** Zoom meeting tạo qua API và meeting link stored với lesson
**And** Enrolled students nhận join link; link expiry match meeting time

### AC-H04 — Payment gateway failure handling

**Priority:** Must
**Given** Stripe/PayPal API không available (network error hoặc 5xx)
**When** Student submit payment
**Then** Hiển thị friendly error: "Payment service unavailable, please try again"
**And** Order vẫn ở pending; student có thể retry
**And** Alert gửi đến admin nếu outage kéo dài >5 phút

### AC-H05 — Search API indexing

**Priority:** Should
**Given** Course mới published hoặc updated
**When** Drupal Search API cron chạy (hoặc real-time index)
**Then** Course xuất hiện trong search kết quả với updated content
**And** Unpublished courses bị remove khỏi index

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| AI API rate limit hit | Backoff + retry; nếu vẫn blocked: pause generation, notify instructor |
| Zoom meeting deleted on Zoom side | Lesson hiển thị "Meeting cancelled" với admin alert |
| Stripe webhook endpoint down | Stripe retries; idempotency ensures no duplicate processing |
| Search index out of sync | Manual reindex option cho admin; stale results acceptable ≤24h |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Reliability | AI generation retry 3x với exponential backoff |
| Security | Payment webhook signature verified bắt buộc |
| Observability | Integration errors logged + alerted |
| Cost control | AI API calls have token budget per generation job |
