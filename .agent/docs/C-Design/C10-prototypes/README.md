# C10 — Prototypes

> Spike areas và prototypes để validate kỹ thuật trước khi implement chính thức.
> Mỗi prototype là một file riêng trong folder này, đặt tên `PROTO-{NNN}-{slug}.md`.

---

## Trạng thái Hiện tại

**Không có spike nào được ghi nhận.** Hệ thống đã được build trực tiếp vào Phase 4 (Development) mà không có phase prototype chính thức. Các quyết định kiến trúc quan trọng được ghi trong C01 và G01 (ADRs).

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
| PROTO-001 | Progress race condition: optimistic lock vs queue worker | High | DEC-003, RISK-002 | PENDING |
| PROTO-002 | Stripe/PayPal webhook signature verification trong Drupal | High | RISK-004 | PENDING |
| PROTO-003 | AI provider abstraction: Drupal Plugin vs Service+Config | Medium | DEC-002, RISK-003 | PENDING |
| PROTO-004 | SCORM/xAPI tracking endpoint performance | Medium | lms_lesson SCORM type | PENDING |
| PROTO-005 | dompdf certificate generation với custom font/layout | Low | lms_certificate | PENDING |

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
| _(chưa có)_ | — | — | — | — |

---

*Cập nhật lần cuối: 2026-04-16*
