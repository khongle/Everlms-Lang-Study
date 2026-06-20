---
page: Homepage
drupal_concept: controller + Twig + editor blocks
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.homepage
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Homepage

## Purpose
The marketing landing page at `/` (`/homepage`). This C11 spec is the contract; the live site is the picture. Composed of editor-managed blocks (hero slideshow, featured courses, categories, testimonials, CTA).

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

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Homepage + 15 skins | done | editor-managed; theme settings drive skin |
