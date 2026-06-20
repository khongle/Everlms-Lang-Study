# C10 — Prototypes

> Technical spike documentation and UX/UI clickable prototypes for EverLMS.
> Each spike is a separate file named `PROTO-{NNN}-{slug}.md`.
> UX/UI clickable prototype (HTML mid-to-high fidelity) is in [`html/`](html/index.html).

## Related canonical documents

| Doc | Purpose |
|-----|---------|
| [../C04-entities/01-content-entities.md](../C04-entities/01-content-entities.md) | Course, Lesson entity schemas (SCORM field) |
| [../C04-entities/03-progress-entities.md](../C04-entities/03-progress-entities.md) | UserCourse / UserLesson progress entities |
| [../C04-entities/04-commerce-entities.md](../C04-entities/04-commerce-entities.md) | Order, payment-method entities |
| [../C04-entities/05-certificate-entities.md](../C04-entities/05-certificate-entities.md) | CertificateTemplate, UserCertificate entities |
| [../C05-modules/lms_progress.md](../C05-modules/lms_progress.md) | Progress cascade / markComplete (PROTO-001) |
| [../C05-modules/lms_payment.md](../C05-modules/lms_payment.md) | Payment gateway plugin host (PROTO-002) |
| [../C05-modules/lms_stripe.md](../C05-modules/lms_stripe.md) | Stripe gateway — webhook stub (PROTO-002) |
| [../C07-sitemap.md](../C07-sitemap.md) | Route map — confirms no webhook routes exist |
| [../C08-wireframes/README.md](../C08-wireframes/README.md) | Wireframe index |

---

## Current State (as of 2026-06-20)

**5 technical spikes defined** (PROTO-001 → PROTO-005), all still `PENDING` — none have been executed. See per-file "Implementation status" sections for what is currently in production code vs. what the spike is meant to validate. Các quyết định kiến trúc quan trọng đã được ghi trong C01 và G01 (ADRs); các prototype này nhằm validate những giả định kỹ thuật còn lại trước khi commit code sản phẩm.

**UX/UI clickable prototype** đã được materialized trong [`html/`](html/index.html) — 35 trang static HTML phủ 5 nhóm flow bắt buộc theo `docs-draft/09-prototype/README.md`: public conversion, enrollment + checkout, learning + quiz + certificate, account/portal, management operation, mobile key journeys. Foundation: shared `assets/theme.css` (mirror của `C09-skins.md`). 660/660 internal href resolved (zero broken link).

---

## Cấu trúc File Prototype

Khi cần tạo prototype mới, tạo file theo template sau:

```
C10-prototypes/
  PROTO-001-{slug}.md
  PROTO-002-{slug}.md
  ...
```

### Template file prototype:

```markdown
# PROTO-{NNN} — {Tên spike}

**Spike area:** {Câu hỏi kỹ thuật cần validate}
**Hypothesis:** {Kỳ vọng kết quả}
**Timebox:** {Thời gian tối đa: e.g., "4h", "1 ngày"}
**Priority:** High / Medium
**Status:** PENDING | VALIDATED | INVALIDATED | INCONCLUSIVE

## Approach
1. {Bước 1}
2. {Bước 2}

## Success Criteria
- {Tiêu chí đo lường}

## Dependencies
- {Điều kiện tiên quyết}

## Outcome
**Result:** {Ghi sau khi thực hiện}
**Decision:** {Hành động dựa trên kết quả}

## Impact on Architecture
- {Nếu INVALIDATED: thay đổi C01? Document ADR.}
- {Nếu VALIDATED: xác nhận approach.}
```

---

## Prototype Candidates (Đề xuất)

Dưới đây là các vấn đề kỹ thuật chưa được validate — nên được spike trước khi implement:

| ID | Spike Area | Priority | Liên quan | Status | Code reality (2026-06-20) |
|----|-----------|----------|-----------|--------|--------------------------|
| [PROTO-001](PROTO-001-progress-race-condition.md) | Progress cascade race condition: SELECT FOR UPDATE vs queue worker vs optimistic lock | High | DEC-003, RISK-002, ADR-010 | PENDING | Race condition still exists in `ProgressApiController::markComplete()` — no lock implemented |
| [PROTO-002](PROTO-002-payment-webhook-signature.md) | Stripe + PayPal webhook signature verification, idempotency, atomic fulfillment | High | RISK-004, FIND-001/002/005/009, ADR-007 | PENDING | No webhook routes exist anywhere — webhook endpoints are completely absent |
| [PROTO-003](PROTO-003-ai-provider-rate-limit-rollback.md) | AI provider rate limit, cost cap, saga rollback, header-based credentials | Medium | RISK-003, FIND-003, FIND-008, ADR-006 | PENDING | No rollback/compensation in `CourseBuilderService`; Gemini key still in URL query string |
| [PROTO-004](PROTO-004-scorm-xapi-tracking-performance.md) | SCORM 1.2 / xAPI tracking endpoint performance + extraction security | Medium | lms_lesson SCORM type, [../C05-modules/lms_progress.md](../C05-modules/lms_progress.md) | PENDING | SCORM package extraction exists in `lms_lesson.module`; no SCORM runtime commit endpoint |
| [PROTO-005](PROTO-005-mpdf-certificate-generation.md) | mPDF certificate generation: Vietnamese fidelity, QR verify, sync vs async | Low | [../C04-entities/05-certificate-entities.md](../C04-entities/05-certificate-entities.md) | PENDING (mPDF **already live**) | `CertificateGenerator` using mPDF is fully implemented and in production |

---

## Hướng dẫn

- Mỗi spike **phải có timebox** — không được kéo dài vô thời hạn.
- **High priority** = blocks architecture decision → phải spike trước khi code.
- Kết quả spike phải update vào `Outcome` và tạo ADR nếu thay đổi architecture.
- Sau khi spike xong, update bảng trên với trạng thái thực tế.

---

## Prototype Summary

| ID | Spike Area | Timebox | Outcome | Code reality (2026-06-20) |
|----|-----------|---------|---------|--------------------------|
| PROTO-001 | Progress cascade race condition | 1 ngày | _(chưa chạy)_ | Risk still open — no lock in markComplete |
| PROTO-002 | Payment webhook signature + idempotency | 1.5 ngày | _(chưa chạy)_ | Not started — no webhook endpoints exist |
| PROTO-003 | AI rate limit + cost cap + rollback | 1 ngày | _(chưa chạy)_ | Not started — no rollback, Gemini key in URL |
| PROTO-004 | SCORM/xAPI endpoint + extraction | 1 ngày | _(chưa chạy)_ | SCORM extraction exists; no tracking endpoint |
| PROTO-005 | mPDF certificate render | 0.5 ngày | _(chưa chạy)_ | mPDF already live — spike validates quality/async choice |

**Total timebox estimate:** ~5 person-days. Recommended execution order: (PROTO-001, PROTO-002) in parallel → PROTO-003 → PROTO-004 → PROTO-005.

---

## UX/UI Clickable Prototype (`html/`)

Static HTML prototype, mở trực tiếp trong browser từ [`html/index.html`](html/index.html) (navigation hub).

| Nhóm | Folder | Trang |
|------|--------|-------|
| Foundation | `html/assets/` | `theme.css` (design tokens + components, mirror C09-skins.md), `components.html` (reusable navbar/footer snippets) |
| 1. Public conversion | `html/public/` | `homepage.html`, `courses.html` (có skeleton loading), `course-detail.html` (sticky enroll sidebar + tabs), `categories.html`, `pricing.html` (toggle monthly/annual), `about.html`, `blog.html`, `faq.html`, `instructors.html`, `learning-paths.html`, `certificate-verify.html` (gắn với PROTO-005), `login.html` (default + validation error states), `register.html` (password strength meter) |
| 2. Enrollment + checkout | `html/account/` | `checkout.html` (4 payment methods: Stripe / PayPal / ZaloPay / BankTransfer), `checkout-success.html` (idempotent confirmation, gợi ý PROTO-002), `orders.html` (table + skeleton + empty state) |
| 3. Learning + quiz + cert | `html/learn/` | `lesson-video.html`, `lesson-text.html`, `lesson-scorm.html` (gợi ý PROTO-004 tracking widget), `quiz.html` (sticky timer, navigator), `quiz-result.html` (SVG score ring), `certificate.html` (paper preview + QR verify, gợi ý PROTO-005) |
| 4. Account / portal | `html/account/` | `my-learning.html` (dashboard), `my-courses.html` (tabs + empty state), `profile.html` (Profile/Security/Notifications/Membership tabs) |
| 5. Management | `html/manage/` | `dashboard.html` (KPIs + revenue SVG chart + 403 demo), `courses.html`, `course-edit.html` (curriculum + AI Builder panel, gợi ý PROTO-003 cost cap & saga rollback log), `quizzes.html`, `orders.html`, `users.html` |
| 6. Mobile key journeys | `html/mobile/` | `home.html` (Home/Explore), `lesson.html` (player + quick quiz), `checkout.html` (Apple Pay / Google Pay style) |

**State coverage** (theo `docs-draft/09-prototype/README.md` §4): default, hover/focus/active, loading/skeleton, empty, validation error, permission denied — tất cả đều có ví dụ cụ thể (xem bảng "State coverage summary" trong `html/index.html`).

**Tech:** Static HTML5 + 1 CSS file. No build step, no JS framework, no external CDN. Mở bằng `file://` hoặc bất cứ static server nào.

---

*Last updated: 2026-06-20 — added implementation status notes per prototype; corrected cross-links to C04-entities/, C05-modules/, C07-sitemap.md.*
