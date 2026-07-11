---
page: Course detail
drupal_concept: entity view controller
mode: as-built
owner_module: lms_course
route_group: public
route_name: lms_course.course_detail
prototype_type: detail
adequacy: PASS
---

# C11 Page - Course detail

## Purpose
Trang landing/detail của course tại `/courses/{lms_course}`: overview, curriculum, instructor, reviews, và CTA enroll/buy. Trang chuyển đổi (conversion page).

## Source Evidence
- **Canonical URL:** `/courses/{lms_course}` · **Owner module:** lms_course
- **Editable source:** `lms_course` entity fields (description, what_learn, prerequisites, who_for, price), chapters/lessons, ratings
- **Editor management UI:** course entity form
- **Access evidence:** anonymous (content gated for enrollment)

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/courses/{lms_course}` |
| Route name | `lms_course.course_detail` |
| Drupal page primitive | entity view controller + placed blocks |
| Owner module | lms_course |
| Theme/layout | `lms_theme` detail layout |
| Access | anonymous (view); enroll requires auth |
| Cache | tags: `lms_course:{id}`, rating tag; contexts: `user` (CTA state) |

## Placed Blocks
| Block | Label | Region | Primitive | Why |
| ----- | ----- | ------ | --------- | --- |
| [course_curriculum](../blocks/course_curriculum.md) | Curriculum | content | block plugin | chapters/lessons outline |
| [enroll_cta](../blocks/enroll_cta.md) | Enroll/Buy | sidebar_second | block plugin | enroll/checkout entry |
| [rating_widget](../blocks/rating_widget.md) | Reviews | content | block plugin | ratings + submit |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Sections | overview, what you'll learn, curriculum, instructor, reviews, FAQ |
| CTA logic | not-enrolled → enroll/buy; enrolled → continue; membership → included |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Title/description/marketing | `lms_course` fields | content_editor/instructor | `lms_course:{id}` | edit course → updates |
| Curriculum | `field_chapters`→`field_lessons` | instructor | course tag | add lesson → outline updates |
| Instructor | `field_instructor` (user) | — | user tag | — |
| Reviews | `lms_rating` | learner submit | rating tag | approve review → shows |
| Price/CTA | `field_price`/entitlement | — | user context | enroll → CTA changes |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | all course copy in entity fields | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | full detail + CTA |
| Empty | course with no lessons → curriculum hidden |
| Access denied | unpublished → 403/404 for non-staff |
| Validation error | n/a |
| Loading | media lazy-loads |

## Message copy inventory
| State/Action | Copy (verbatim) | Editable source |
| --- | --- | --- |
| Primary CTA (not enrolled) | "Enroll now" | enroll block config |
| Secondary CTA | "Add to wishlist" | course entity form |
| Price/promo (enroll card) | "$89.99" (list price "$199.99" struck through) · "Save 55%" | course price fields |
| Purchase reassurance | "30-day money-back guarantee" | enroll block config |
| Membership upsell (alt to buy) | "Or included free with EverLMS Pro" | enroll block config, links to Pricing (P006) |
| Curriculum free-preview tag | "Free preview" (shown on the first unlocked lesson) | curriculum block config |

## Responsive behavior
| Breakpoint | Behavior |
| --- | --- |
| mobile (≤575.98px) | `.container` chạy full width; `.detail` đã là 1 cột (kế thừa từ tablet); `.learn-grid` (What you'll learn) và `.insthead` (Instructor) rút về 1 cột, `.insthead` căn giữa; `.review-summary` (score + review-bars) rút về 1 cột |
| tablet (768–991.98px) | `.detail{grid-template-columns:1fr}` — nội dung (overview/curriculum/instructor/reviews) và thẻ `.enroll` xếp thành 1 cột dọc; `.enroll{position:static}` (mất sticky), thẻ enroll/buy hiện sau cùng, bên dưới toàn bộ nội dung vì là item thứ 2 trong grid |
| desktop (992–1199.98px) | `.detail{grid-template-columns:minmax(0,1fr) 380px}` — nội dung bên trái co giãn, `.enroll` bên phải rộng cố định 380px và `position:sticky;top:90px` theo khi cuộn |
| wide (≥1200px) | `.container` mở tới `--container-xl` (1140px)/`--container-xxl` (1280px) tuỳ viewport; layout 2 cột giữ nguyên, `gap` giữa nội dung và `.enroll` giãn theo `clamp(1.8rem,4vw,3rem)` |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Detail + CTA + reviews | done | gating in F-004 |
