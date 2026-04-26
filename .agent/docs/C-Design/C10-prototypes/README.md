# C10 — Prototypes

> Spike areas và prototypes để validate kỹ thuật trước khi implement chính thức.
> Mỗi spike là một file riêng trong folder này, đặt tên `PROTO-{NNN}-{slug}.md`.
> UX/UI clickable prototype (HTML mid-to-high fidelity) đặt trong [`html/`](html/index.html).

---

## Trạng thái Hiện tại

**5 technical spike đã được định nghĩa** (PROTO-001 → PROTO-005), tất cả ở trạng thái `PENDING`. Phải execute các spike High-priority (PROTO-001, PROTO-002) trước khi mở khoá các unit downstream tương ứng (U-059 cho race condition, U-028/U-029/U-060/U-061 cho payment webhook). Các quyết định kiến trúc quan trọng đã được ghi trong C01 và G01 (ADRs); các prototype này nhằm validate những giả định kỹ thuật còn lại trước khi commit code sản phẩm.

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

| ID | Spike Area | Priority | Liên quan | Status |
|----|-----------|----------|-----------|--------|
| [PROTO-001](PROTO-001-progress-race-condition.md) | Progress cascade race condition: SELECT FOR UPDATE vs queue worker vs optimistic lock | High | DEC-003, RISK-002, ADR-010, U-059 | PENDING |
| [PROTO-002](PROTO-002-payment-webhook-signature.md) | Stripe + PayPal webhook signature verification, idempotency, atomic fulfillment | High | RISK-004, FIND-001/002/005/009, ADR-007, U-028/029/060/061 | PENDING |
| [PROTO-003](PROTO-003-ai-provider-rate-limit-rollback.md) | AI provider rate limit, cost cap, saga rollback, header-based credentials | Medium | RISK-003, FIND-003, FIND-008, ADR-006, U-047 | PENDING |
| [PROTO-004](PROTO-004-scorm-xapi-tracking-performance.md) | SCORM 1.2 / xAPI tracking endpoint performance + extraction security | Medium | lms_lesson SCORM type, lms_progress | PENDING |
| [PROTO-005](PROTO-005-mpdf-certificate-generation.md) | mPDF certificate generation: Vietnamese fidelity, QR verify, sync vs async | Low | lms_certificate, C01 (mPDF vs dompdf) | PENDING |

---

## Hướng dẫn

- Mỗi spike **phải có timebox** — không được kéo dài vô thời hạn.
- **High priority** = blocks architecture decision → phải spike trước khi code.
- Kết quả spike phải update vào `Outcome` và tạo ADR nếu thay đổi architecture.
- Sau khi spike xong, update bảng trên với trạng thái thực tế.

---

## Prototype Summary

| ID | Spike Area | Timebox | Outcome | Impact |
|----|-----------|---------|---------|--------|
| PROTO-001 | Progress cascade race condition | 1 ngày | _(chưa chạy)_ | _(chốt DEC-003)_ |
| PROTO-002 | Payment webhook signature + idempotency | 1.5 ngày | _(chưa chạy)_ | _(unblock RISK-004)_ |
| PROTO-003 | AI rate limit + cost cap + rollback | 1 ngày | _(chưa chạy)_ | _(resolve FIND-003/008, RISK-003)_ |
| PROTO-004 | SCORM/xAPI endpoint + extraction | 1 ngày | _(chưa chạy)_ | _(promote SCORM lesson type)_ |
| PROTO-005 | mPDF certificate render | 0.5 ngày | _(chưa chạy)_ | _(align C01 PDF tech choice)_ |

**Tổng timebox:** ~5 ngày người. Khuyến nghị thực thi theo thứ tự priority: (PROTO-001, PROTO-002) song song → PROTO-003 → PROTO-004 → PROTO-005.

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

*Cập nhật lần cuối: 2026-04-26 — bổ sung PROTO-001 → PROTO-005 + 35 trang HTML clickable prototype, 0 broken link.*
