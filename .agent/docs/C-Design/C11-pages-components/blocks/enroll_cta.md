---
block: Enroll / buy CTA
drupal_concept: block plugin
owner_module: lms_progress
region: sidebar_second
block_type: plugin
adequacy: PASS
---

# C11 Block - Enroll / buy CTA

## Purpose
Call-to-action tuỳ ngữ cảnh trên trang course detail: enroll (miễn phí), buy (trả phí), continue (đã enroll), hoặc included (đã có sẵn trong membership).

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | enroll_cta |
| Drupal primitive | block plugin |
| Owner module | lms_progress + lms_order |
| Placed on | P003, P029 (summary) |
| Cache | contexts: `user`; tags: course, user_course |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| State logic | entitlement check (enrolled/order/membership) |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | button + price markup |
| Access | hide buy for already-entitled |
| Responsive | sticky on mobile |
| Editing | price from `lms_course.field_price` |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | course detail sidebar |

## Tests / Checks
- Trạng thái CTA đúng theo entitlement (free/paid/enrolled/member); điều hướng đúng route enroll/checkout/continue.
