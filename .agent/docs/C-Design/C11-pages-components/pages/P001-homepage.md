---
page: Homepage
drupal_concept: controller + Twig + editor blocks
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.homepage
prototype_type: marketing
adequacy: PASS
standalone: public marketing landing page, không phải một bước của 10 workflow giao dịch (B04)
---

# C11 Page - Homepage

## Purpose
Trang đích marketing (landing page) tại `/` (`/homepage`). Spec C11 này là hợp đồng (contract); trang live là hình ảnh minh hoạ. Gồm các block do editor quản lý (hero slideshow, featured courses, categories, testimonials, CTA).

## Source Evidence
- **Canonical URL:** `/` (alias of `/homepage`) · **Category:** Public/Marketing · **Owner module:** lms_course + lms_theme
- **Editable source:** homepage blocks/regions (slideshow content block, featured-courses View), theme settings (15 skins)
- **Editor management UI:** Block layout + content blocks; theme settings (`/admin/appearance/settings/lms_theme`)
- **Access evidence:** anonymous-reachable

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/` (`/homepage`) |
| Route name | `lms_course.homepage` |
| Drupal page primitive | Controller returning render array of placed blocks |
| Builder target | `lms_theme` regions (hero, content_top, content) |
| Owner module | lms_course |
| Theme/layout | `lms_theme`, selected homepage skin |
| Access | anonymous |
| Cache | tags: `lms_course_list`, block content tags; contexts: `url`, `user.roles` |

## Placed Blocks
| Block | Label | Region | Drupal primitive | Why |
| ----- | ----- | ------ | ---------------- | --- |
| [homepage_slideshow](../blocks/homepage_slideshow.md) | Hero slideshow | hero | content block | editable hero banners |
| [course_card](../blocks/course_card.md) | Featured courses | content | Views block | promote courses |
| [dashboard_kpis](../blocks/dashboard_kpis.md) | Stats strip | content_top | block plugin | social proof counts |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Composition | Hero + featured courses + categories + CTA, all editor-managed blocks |
| Personalisation | If authenticated, show "continue learning" strip (progress) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Hero slides | slideshow content block (`block_content` `slideshow`) | content_editor | block cache tag | edit slide → appears |
| Featured courses | Views (course list) | n/a (published) | `lms_course_list` | publish course → appears |
| Category tiles | taxonomy `course_category` | content_editor | term cache | add category → tile |
| Skin/branding | theme settings | administrator | theme settings cache | switch skin → re-render |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | all homepage copy is block/View/theme-settings managed | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | Full homepage with blocks |
| Empty | No featured courses → hide section gracefully |
| Access denied | n/a (public) |
| Validation error | n/a |
| Loading | progressive render; lazy images |

## Message copy inventory

| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Hero — CTA chính | "Explore courses" | homepage slideshow content block (region hero, xem [Placed Blocks](#placed-blocks)) |
| Hero — CTA phụ | "See pricing" | homepage slideshow content block (region hero) |
| Featured courses — CTA xem tất cả | "View all courses" | course card Views block — cấu hình footer link của View (region content) |
| Featured courses — Empty | (không có copy hiển thị — toàn bộ section `.section--soft` "Featured courses this month" bị ẩn) | course card Views block — "No results behavior" = ẩn section, xem `## Page States` → Empty |
| CTA band cuối trang — heading + lede | "Start learning today" / "Join 480,000+ learners. Your first course is free — no credit card required." | homepage block/theme-managed copy (xem `## Hardcoded content exceptions` — không có chuỗi hardcode trên trang này) |
| CTA band cuối trang — CTA chính | "Create free account" | homepage block/theme-managed copy, liên kết tới P016-login-register |

## Responsive behavior

| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container` rộng 100%; `.hero__grid` đã xếp chồng 1 cột (từ ngưỡng ≤991.98px); các lưới thẻ `.grid--4`/`.grid--3` (categories, featured courses, why-EverLMS, instructors, testimonials) về 1 cột (ngưỡng ≤639.98px); `.steps` (how-it-works, 4 bước) về 1 cột (ngưỡng ≤479.98px, tức đã 1 cột ở mobile nhỏ, 2 cột ở 480–575px); `.stats` (stats strip) giữ 2 cột (ngưỡng ≤767.98px, không có rule 1 cột riêng) |
| tablet (768–991px) | `.container` max-width = `var(--container-md)`; `.hero__grid` vẫn 1 cột (ngưỡng gập là ≤991.98px nên áp dụng suốt dải tablet); `.grid--4`/`.grid--3` hiển thị 2 cột (categories 8 ô, featured courses 4 thẻ, why-EverLMS 3 thẻ, instructors 4 thẻ đều co về 2 cột); `.steps` hiển thị 2×2; `.stats` hiển thị 2 cột |
| desktop (992–1199px) | `.container` max-width = `var(--container-lg)`; `.hero__grid` trở lại 2 cột (`1.05fr .95fr`, media bên phải) vì đã vượt ngưỡng 991.98px; `.grid--4` hiển thị đủ 4 cột (categories, featured courses, instructors), `.grid--3` hiển thị đủ 3 cột (why-EverLMS, testimonials); `.steps` và `.stats` hiển thị đủ 4 cột |
| wide (≥1200px) | `.container` max-width = `var(--container-xl)` (≤1399.98px) rồi full width container ở ≥1400px, giới hạn bề rộng nội dung; layout cột giữ nguyên như desktop; `.ctaband`/`.section` padding co giãn theo `clamp()` thay vì nhảy bậc thêm |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Homepage + 15 skins | done | editor-managed; theme settings drive skin |
