---
page: Course detail
drupal_concept: entity view controller
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.course_detail
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Course detail

## Purpose
The course landing/detail page at `/courses/{lms_course}`: overview, curriculum, instructor, reviews, and an enroll/buy CTA. The conversion page.

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

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Detail + CTA + reviews | done | gating in F-004 |
