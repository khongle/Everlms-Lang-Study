---
page: Workspace home
drupal_concept: controller dashboard
mode: as-built
owner_module: lms_course
route_group: manage
route_name: lms_course.workspace
view_mode: default
prototype_type: dashboard
adequacy: PASS
standalone: vỏ landing chung của workspace, không phải một bước riêng của một workflow cụ thể
---

# C11 Page - Workspace home

## Purpose
Dashboard dành cho nhân sự tại `/workspace` (dashboard): điểm vào để quản lý courses/quizzes/users/orders + các chỉ số KPI.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace` |
| Route name | `lms_course.workspace` |
| Primitive | controller (links + KPIs) |
| Access | instructor/content_editor/manager |
| Cache | contexts: `user.roles` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| KPI tiles + nav | aggregate + menu | staff | short-TTL | role change → nav changes |

## Page States
Normal: dashboard · Empty: new staff → empty KPIs · Access denied: non-staff → 403 · Validation error: n/a · Loading: lazy.

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| CTA chính (header) — tạo khoá học mới | "New course" | the module — Twig template dashboard `workspace` (chuỗi tĩnh, dịch qua UI string translation) |
| CTA phụ (header) — xem báo cáo | "View reports" | the module — Twig template dashboard `workspace` |
| CTA trong panel "To‑do" — sang danh sách khoá học | "Go to Manage courses" | the module — Twig template dashboard `workspace` |
| Copy nhắc nhở trong panel "Learner questions" | "Fastest replies keep your course rating high — aim to answer within 24 hours." | the module — Twig template dashboard `workspace` |
| CTA panel "Learner questions" | "Reply now" | the module — Twig template dashboard `workspace` |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (<576px) | `.container` co về 100% chiều rộng; `.kpis` (4 tile KPI) rút xuống 1 cột dọc ở ≤479.98px; lưới "Quick actions" (`.grid--4`, 4 thẻ) rút xuống 1 cột ở ≤639.98px; `.detail` (nội dung chính + aside "To‑do"/"Learner questions") đã gộp 1 cột từ mốc ≤991.98px nên aside luôn nằm dưới cùng, sau "Recent activity"; biểu đồ cột `.barchart` (Enrollments trend) giữ chiều cao cố định 200px, các cột chỉ hẹp lại theo flex. |
| tablet (768–991px) | `.container` giới hạn 720px; `.kpis` hiển thị 2 cột (rule ≤991.98px); `.grid--4` (Quick actions) cũng còn 2 cột; `.detail` vẫn 1 cột (ngưỡng gộp 991.98px) nên aside vẫn xếp bên dưới nội dung chính, chưa quay lại layout 2 cột. |
| desktop (992–1199px) | `.container` giới hạn 960px; `.kpis` quay lại đủ 4 cột; `.grid--4` quay lại đủ 4 thẻ/hàng; `.detail` chuyển sang layout 2 cột (nội dung chính co giãn + aside cố định 380px, `position:sticky;top:90px`). |
| wide (≥1200px) | `.container` giới hạn 1140px (đến 1399.98px) rồi 1280px ở khổ rất rộng; layout giữ nguyên như desktop (KPI 4 cột, Quick actions 4 cột, `.detail` 2 cột với aside sticky 380px), chỉ nới thêm khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Workspace home | done | role-scoped nav |
