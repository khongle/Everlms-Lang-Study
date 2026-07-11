---
page: Learner dashboard
drupal_concept: controller (KPIs + lists)
mode: as-built
owner_module: lms_progress
route_group: app
route_name: lms_progress.dashboard
prototype_type: dashboard
adequacy: PASS
---

# C11 Page - Learner dashboard

## Purpose
Trang chủ của learner đã đăng nhập tại `/dashboard`: continue-learning, progress, certificate gần đây, notification.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/dashboard` |
| Route name | `lms_progress.dashboard` |
| Drupal page primitive | controller render array |
| Owner module | lms_progress |
| Access | authenticated |
| Cache | contexts: `user`; tags: `lms_user_course_list:{uid}` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [progress_bar](../blocks/progress_bar.md) | Continue learning | content_top | resume courses |
| [dashboard_kpis](../blocks/dashboard_kpis.md) | My stats | content_top | counts |
| [notification_bell](../blocks/notification_bell.md) | Notifications | header | unread badge |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Sections | in-progress courses, completed, certificates, recommendations |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| My courses + progress | `lms_user_course` (own) | authenticated (own) | `lms_user_course_list:{uid}` | complete lesson → % updates |
| Certificates | `lms_user_certificate` (own) | own | cert tag | pass quiz → cert appears |
| Notifications | `lms_notification` (own) | own | notif tag | event → badge |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Section headings | UI chrome (i18n strings) | translate via interface i18n |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | populated dashboard |
| Empty | no enrollments → browse-catalog CTA |
| Access denied | anonymous → redirect to login |
| Validation error | n/a |
| Loading | per-widget load |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Hero chào mừng (động, ghép tên + streak) | "Hi Alex, keep the streak going" / "You are on a 7-day streak. 2 lessons will finish your current course." | the module dashboard controller (render array message) |
| CTA chính | "Continue learning" | module string (Twig template the module) |
| CTA phụ / Empty (no enrollments → browse-catalog CTA) | "Browse courses" | module string (Twig template the module) |
| Weekly goal — khích lệ (động theo % giờ học) | "3.6 of 5 hours learned. Nice pace!" | the module dashboard widget string |
| Upcoming — nhắc lịch (động) | "Live Q&A: React patterns" / "Quiz due: Hooks in Depth" | the module upcoming widget (dữ liệu động, không phải chuỗi UI tĩnh) |
| CTA rail quiz | "View quiz results" | module string |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.container` co về 100% width; `.kpis` (4 chỉ số) xuống 1 cột ở ≤479.98px; `.split` (Continue learning + rail) đã gộp 1 cột từ ≤991.98px nên "This week's goal"/"Upcoming" luôn nằm dưới danh sách continue-learning; `.contcard` chuyển layout 2 cột (ảnh + nội dung, nút "Resume" xuống hàng) ở ≤639.98px; `.grid--4` (Recommended) về 1 cột ở ≤639.98px. |
| tablet | 768–991px: `.kpis` hiển thị 2 cột; `.split` vẫn 1 cột (ngưỡng gộp là 991.98px) nên rail nằm dưới; `.grid--4` Recommended hiển thị 2 cột. |
| desktop | 992–1199px: `.split` khôi phục 2 cột (continue-learning trái, ring + upcoming phải); `.kpis` đủ 4 cột; `.grid--4` Recommended đủ 4 cột. |
| wide | ≥1200px: `.container` đạt max-width đầy đủ; bố cục không đổi thêm, chỉ nới khoảng trắng hai bên. |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Dashboard widgets | done | own-data scoped |
