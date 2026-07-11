---
page: Workspace reports
drupal_concept: controller report
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace_reports
view_mode: default
prototype_type: report
adequacy: PASS
---

# C11 Page - Workspace reports

## Purpose
Báo cáo dành cho manager tại `/workspace/reports` (report): tổng hợp theo course/learner (trùng lặp một phần với lms_analytics).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/reports` |
| Route name | `lms_course.workspace_reports` |
| Primitive | controller (aggregate) |
| Access | manager |
| Cache | short-TTL |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Report metrics | aggregate over progress/orders | manager | live | enroll → count |

## Page States
Normal: report · Empty: zeros · Access denied: non-manager → 403 · Validation error: n/a · Loading: query.

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Empty (khoá học chưa có dữ liệu) — cảnh báo per-course | "UI/UX Design Masterclass shows no data yet — it's still in review and has not been published to learners." | the module — Twig template `workspace-reports` (`.alert`), tên khoá học nội suy theo entity chưa publish |
| Empty (toàn trang, 0 dữ liệu trong kỳ) | "No report data yet for the selected period." | Chưa có chuỗi cụ thể trong prototype tĩnh (demo luôn có dữ liệu mẫu) — viết hợp lý theo Page States "Empty: zeros" / the module empty-text config |
| CTA chính — xuất báo cáo | "Export CSV" | the module — Twig template `workspace-reports` |
| Bộ lọc khoảng thời gian (segmented, mặc định "30 days") | "7 days" / "30 days" / "90 days" / "Year to date" | the module — Twig template `workspace-reports`, lưu lựa chọn qua `url.query args |
| Nhãn KPI | "Enrollments" / "Revenue" / "Completion rate" / "Refunds" | the module — Twig template `workspace-reports`, số liệu tổng hợp từ `progress`/`orders` |
| Tiêu đề biểu đồ | "Enrollments over time" / "Revenue by course" | the module — Twig template `workspace-reports` (`.chartcard`) |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.container` co về 100% chiều rộng; `.kpis` (4 tile: Enrollments/Revenue/Completion rate/Refunds) rút xuống 1 cột dọc ở ≤479.98px; `.grid--2` (2 chart card "Enrollments over time"/"Revenue by course") đã rút về 1 cột từ ngưỡng ≤639.98px nên luôn xếp dọc ở mobile; bảng "Per-course breakdown" (`.wtable`) cuộn ngang trong `.wtable__wrap` (`overflow-x:auto`) thay vì chuyển sang dạng thẻ; nhóm bộ lọc khoảng thời gian (`.segmented`) tự xuống dòng theo flex-wrap. |
| tablet (768–991px) | `.container` giới hạn 720px; `.kpis` hiển thị 2 cột (ngưỡng ≤991.98px); `.grid--2` đã quay lại 2 cột cạnh nhau vì ngưỡng rút gọn chỉ áp dụng ở ≤639.98px; bảng breakdown vẫn cuộn ngang nếu nội dung rộng hơn khung `.tablecard`. |
| desktop (992–1199px) | `.container` giới hạn 960px; `.kpis` quay lại đủ 4 cột; `.grid--2` giữ 2 chart card cạnh nhau; bảng "Per-course breakdown" hiển thị đầy đủ 5 cột (Course, Enrollments, Revenue, Completion, Refunds) không cần cuộn. |
| wide (≥1200px) | `.container` giới hạn 1140px (đến 1399.98px) rồi rộng hơn ở khổ rất lớn; layout giữ nguyên như desktop (KPI 4 cột, 2 chart card cạnh nhau, bảng đầy đủ), chỉ nới thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Workspace reports | partial | overlaps admin analytics (F-020) |
