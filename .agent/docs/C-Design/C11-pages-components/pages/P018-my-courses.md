---
page: My courses
drupal_concept: controller list
mode: as-built
owner_module: lms_progress
route_group: app
route_name: lms_progress.my_courses
prototype_type: listing
adequacy: PASS
---

# C11 Page - My courses

## Purpose
Các course đã enroll tại `/my-courses` (listing): progress + resume.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-courses` |
| Route name | `lms_progress.my_courses` |
| Primitive | controller list of own `lms_user_course` |
| Deviation | controller-based, not a View — [ADR-006](../../../G-Governance/G01-adrs/ADR-006-controller-based-listings.md) |
| Access | authenticated (own) |
| Cache | contexts: `user`; tags: `lms_user_course_list:{uid}` |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Enrolled + % | `lms_user_course` (own) | own | per-user | enroll → row |

## Page States
Normal: list · Empty: none → catalog CTA · Access denied: anon → login · Validation error: n/a · Loading: lazy.

## Listing behavior

| Sort fields | Default sort | Page size | Filter operators | URL persistence | Empty result |
| --- | --- | --- | --- | --- | --- |
| Không có control sort trên UI; thứ tự ngầm định theo tab: in-progress theo last-accessed, completed theo completed_on, wishlist theo added_on | last_accessed DESC (In progress) / completed_on DESC (Completed) / added_on DESC (Wishlist) | không phân trang trong prototype — hiển thị toàn bộ khoá theo tab | tab equals(status: in_progress\|completed\|wishlist) qua `.tabs[data-tab]` (JS client-side, không phải query arg) | không persist qua URL — tab là JS state (`data-tabs`/`is-active`), mất khi reload | "Saved recommendations" / "You haven't saved any courses from your personalized recommendations yet. Browse the catalog to add more to your wishlist." (tab Wishlist) + CTA "Browse catalog" |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Tiêu đề trang | "My courses" / "Every course you're enrolled in, with progress synced automatically across devices." | module string |
| CTA chính / phụ | "Resume last lesson" / "Browse catalog" | module string |
| Tab labels | "In progress" / "Completed" / "Wishlist" | module string |
| CTA khoá đã hoàn thành | "Get certificate" | module string |
| Empty (Wishlist rỗng) | "You haven't saved any courses from your personalized recommendations yet. Browse the catalog to add more to your wishlist." | View empty-text config (wishlist block) |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile | Ở ≤575.98px `.tabs` (In progress/Completed/Wishlist) cuộn ngang khi không đủ chỗ; `.contcard` (danh sách in-progress/completed) chuyển 2 cột (ảnh + nội dung, nút "Resume"/"Get certificate" xuống hàng) ở ≤639.98px; `.grid--auto` (Wishlist) về 1 cột khi viewport hẹp hơn ~280px/thẻ. |
| tablet | 768–991px: `.grid--auto` (Wishlist) hiển thị ~2 cột (auto-fill, minmax 280px); `.contcard` vẫn giữ layout 3 cột (ảnh, nội dung, nút) vì trên ngưỡng 639.98px. |
| desktop | 992–1199px: `.container` = container-lg; layout đầy đủ, `.grid--auto` có thể lên 3 cột tuỳ độ rộng. |
| wide | ≥1200px: `.container` đạt max-width đầy đủ; `.grid--auto` (Wishlist) mở rộng thêm cột khi còn chỗ. |

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| My courses | done | resume link |
