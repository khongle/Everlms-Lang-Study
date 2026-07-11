---
page: Analytics reports
drupal_concept: controller + export
mode: as-built
owner_module: lms_analytics
route_group: admin
route_name: lms_analytics.courses
prototype_type: report
adequacy: PASS
---

# C11 Page - Analytics reports

## Purpose
Báo cáo dành cho manager/admin tại `/admin/lms/reports/{courses,quizzes,users}` kèm xuất CSV: enrollments, tỷ lệ hoàn thành (completion), tỷ lệ đạt (pass rates), doanh thu (F-020).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/admin/lms/reports/courses` (quizzes, users; `+/export`) |
| Route name | `lms_analytics.courses` |
| Drupal page primitive | controller (aggregate queries) + CSV export |
| Owner module | lms_analytics |
| Access | manager / administrator (scoped) |
| Cache | short-TTL or uncacheable (live metrics) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [dashboard_kpis](../blocks/dashboard_kpis.md) | KPI strip | content_top | headline metrics |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Reports | courses, quizzes, users; export CSV |
| Scope | manager = org; instructor = own (verify) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Metrics | aggregate over progress/orders/quiz results | manager/admin | live | enroll → count rises |
| Export | CSV controller | same | — | export → file |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | metrics computed from data | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | charts/tables + export |
| Empty | no data → zeros |
| Access denied | non-manager → 403 |
| Validation error | bad date range → ignored |
| Loading | query spinner |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA export dữ liệu | "Export CSV" | prototype static text (nút header, pagehead__actions) |
| Bộ lọc khoảng thời gian báo cáo | "7 days" / "30 days" / "90 days" / "This year" | prototype static text (`.segmented` control, toolbar) |
| Mốc cập nhật số liệu (live) | "Updated 8 minutes ago · Jun 11 – Jul 11, 2026" | prototype static text (toolbar hint, tính theo thời điểm truy vấn) |
| CTA xem đầy đủ danh sách khóa học | "View all courses" | prototype static text (tablecard "Top courses (30 days)") |
| Trạng thái empty (không có dữ liệu trong khoảng lọc) | Không có copy text riêng trong prototype — theo Page States, KPI/biểu đồ hiển thị giá trị 0 thay vì thông báo dạng chữ | the module controller (cần bổ sung copy empty-state nếu muốn UX rõ hơn — gap) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.kpis` (4 KPI đầu trang) về 1 cột ở ≤479px; `.grid.grid--2` (Signups over time + Revenue by month; Top categories by revenue + Learner funnel) về 1 cột ở ≤639px; `.barchart` co lại theo chiều rộng cột cha, nhãn % vẫn hiển thị trên đầu cột; `.wtable` (Top courses) cuộn ngang qua `.wtable__wrap`; toolbar (`.segmented` khoảng thời gian + "Updated…") xếp dọc theo `flex-wrap`. |
| tablet (768–991px) | `.kpis` còn 2 cột (ngưỡng gập ≤991.98px); `.grid.grid--2` vẫn giữ 2 cột (ngưỡng gập ≤639px chưa chạm) cho cả 2 hàng chart. |
| desktop (992–1199px) | `.kpis` 4 cột đầy đủ; `.grid.grid--2` giữ 2 cột; bảng "Top courses" hiển thị đủ cột (Course, Enrollments, Completion, Revenue, Rating) không cần cuộn. |
| wide (≥1200px) | Bố cục như desktop, `.container` mở rộng theo `--container-xl`; các `.chartcard` có thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Reports + export | partial | ⚠️ some KPIs basic (F-020); adequacy NOT_RUN |
